/**
 * Print Server for FacturaFacil - Node.js Edition
 * 
 * Features:
 * - ESC/POS encoding with CP850 code page command
 * - Local RAW printing via PowerShell (raw-print.ps1) on Windows
 * - Local RAW printing via lp/lpr on Linux/Mac
 * - Network printing via raw TCP socket (port 9100)
 * - REST API: /status, /printers, /print, /print-raw, /print-escpos-text
 * - Log rotation (auto-renames when > 5MB)
 * - CORS enabled for browser clients
 * 
 * Requirements:
 *   npm install express cors iconv-lite
 * 
 * Run:
 *   node server.js
 * 
 * For Windows local printers, place raw-print.ps1 in the same folder.
 */

const express = require('express');
const net = require('net');
const path = require('path');
const fs = require('fs');
const os = require('os');
const { exec, execFile } = require('child_process');
const { promisify } = require('util');
const iconv = require('iconv-lite');

const execAsync = promisify(exec);

// ── Configuration ──
const PORT = process.env.PORT || 9100;
const HOST = process.env.HOST || '0.0.0.0';
const LOG_FILE = path.join(__dirname, 'print-server.log');
const LOG_MAX_SIZE = 5 * 1024 * 1024; // 5 MB
const RAW_PRINT_PS1 = path.join(__dirname, 'raw-print.ps1');
const IS_WIN = os.platform() === 'win32';

// ── ESC/POS Command Constants ──
const ESC = 0x1B;
const GS = 0x1D;
const ESC_POS = {
  INIT: Buffer.from([ESC, 0x40]),           // Initialize printer
  CP_850: Buffer.from([ESC, 0x74, 0x02]),  // Select code page PC850 (Multilingual)
  CP_1252: Buffer.from([ESC, 0x74, 0x10]), // Select code page WPC1252 (Windows Latin)
  CP_858: Buffer.from([ESC, 0x74, 0x26]),  // Select code page PC858 (Euro)
  BOLD_ON: Buffer.from([ESC, 0x45, 0x01]),  // Bold on
  BOLD_OFF: Buffer.from([ESC, 0x45, 0x00]), // Bold off
  ALIGN_CENTER: Buffer.from([ESC, 0x61, 0x01]),
  ALIGN_LEFT: Buffer.from([ESC, 0x61, 0x00]),
  ALIGN_RIGHT: Buffer.from([ESC, 0x61, 0x02]),
  CUT_PARTIAL: Buffer.from([GS, 0x56, 0x01]), // Partial cut
  CUT_FULL: Buffer.from([GS, 0x56, 0x00]),   // Full cut
  FEED_LINES: (n) => Buffer.from([ESC, 0x64, n]), // Feed n lines
};

// ── Logging ──
function rotateLogIfNeeded() {
  try {
    if (fs.existsSync(LOG_FILE)) {
      const stats = fs.statSync(LOG_FILE);
      if (stats.size > LOG_MAX_SIZE) {
        const rotated = LOG_FILE + '.' + Date.now();
        fs.renameSync(LOG_FILE, rotated);
      }
    }
  } catch (e) {
    // ignore rotation errors
  }
}

function log(msg) {
  const line = `[${new Date().toISOString()}] ${msg}`;
  console.log(line);
  try {
    rotateLogIfNeeded();
    fs.appendFileSync(LOG_FILE, line + '\n');
  } catch (e) {
    console.error('Log write failed:', e.message);
  }
}

// ── Helper: prepare ESC/POS data with proper encoding ──
function prepareEscpos(rawBuffer, codePage = 'cp850', codePageId = 0x02) {
  // Laravel already sends ISO-8859-1 encoded data with ESC/POS commands.
  // Just prepend the code page command to ensure correct character mapping.
  const hasCodePageCmd = rawBuffer.length >= 3 && 
    rawBuffer[0] === ESC && rawBuffer[1] === 0x74;

  if (!hasCodePageCmd) {
    return Buffer.concat([ESC_POS.INIT, ESC_POS.CP_850, rawBuffer]);
  }
  return rawBuffer;
}

// ── Printer Discovery ──
async function getPrintersWindows() {
  try {
    // Try modern Get-Printer first
    const { stdout } = await execAsync(
      'powershell -NoProfile -Command "Get-Printer | Select-Object Name,IsDefault | ConvertTo-Json -Compress"',
      { timeout: 5000 }
    );
    const data = JSON.parse(stdout);
    const list = Array.isArray(data) ? data : [data];
    return list
      .filter(p => p && p.Name)
      .map(p => ({
        name: p.Name,
        isDefault: !!p.IsDefault,
        status: 'unknown'
      }));
  } catch (e) {
    // Fallback to wmic
    try {
      const { stdout } = await execAsync(
        'wmic printer get name,default /format:csv',
        { timeout: 5000 }
      );
      const printers = [];
      const lines = stdout.trim().split('\n');
      for (const line of lines) {
        const parts = line.split(',').map(s => s.trim().replace(/^"|"$/g, ''));
        if (parts.length >= 3 && parts[2] && parts[2] !== 'Name') {
          printers.push({
            name: parts[2],
            isDefault: parts[1].toLowerCase() === 'true',
            status: 'unknown'
          });
        }
      }
      return printers;
    } catch (e2) {
      return [];
    }
  }
}

async function getPrintersUnix() {
  try {
    const { stdout } = await execAsync('lpstat -a', { timeout: 5000 });
    return stdout.trim().split('\n').map(line => {
      const name = line.split(' ')[0];
      return { name, isDefault: false, status: 'idle' };
    }).filter(p => p.name);
  } catch (e) {
    try {
      const { stdout } = await execAsync('lpstat -d', { timeout: 5000 });
      const defaultPrinter = stdout.replace(/^.*: /, '').trim();
      return defaultPrinter ? [{ name: defaultPrinter, isDefault: true, status: 'idle' }] : [];
    } catch (e2) {
      return [];
    }
  }
}

async function getPrinters() {
  if (IS_WIN) {
    return await getPrintersWindows();
  }
  return await getPrintersUnix();
}

// ── Local RAW Printing ──
async function printLocalRaw(printerName, dataBuffer) {
  const tmpFile = path.join(os.tmpdir(), `print_${Date.now()}_${Math.random().toString(36).slice(2)}.bin`);

  try {
    fs.writeFileSync(tmpFile, dataBuffer);

    if (IS_WIN) {
      // Use raw-print.ps1 (must exist in same folder)
      if (!fs.existsSync(RAW_PRINT_PS1)) {
        throw new Error(`raw-print.ps1 not found at ${RAW_PRINT_PS1}. Place it next to server.js.`);
      }
      const cmd = `powershell -NoProfile -ExecutionPolicy Bypass -File "${RAW_PRINT_PS1}" -printerName "${printerName}" -filePath "${tmpFile}"`;
      const { stdout } = await execAsync(cmd, { timeout: 15000 });
      const output = stdout.trim();
      if (output.startsWith('ERROR')) {
        throw new Error(output);
      }
      log(`Printed OK to "${printerName}" via RAW (${dataBuffer.length} bytes)`);
      return { success: true, bytes: dataBuffer.length };
    } else {
      // Unix: use lp
      const cmd = `lp -d "${printerName}" -o raw "${tmpFile}"`;
      await execAsync(cmd, { timeout: 15000 });
      log(`Printed OK to "${printerName}" via lp (${dataBuffer.length} bytes)`);
      return { success: true, bytes: dataBuffer.length };
    }
  } finally {
    try { fs.unlinkSync(tmpFile); } catch (e) { /* ignore */ }
  }
}

// ── Network Printing ──
async function printToNetwork(ip, port, dataBuffer) {
  return new Promise((resolve, reject) => {
    const socket = new net.Socket();
    const timeout = 10000;
    let resolved = false;

    socket.setTimeout(timeout);

    socket.on('connect', () => {
      log(`Connected to ${ip}:${port}`);
      socket.write(dataBuffer, () => {
        socket.end();
      });
    });

    socket.on('close', () => {
      if (!resolved) {
        resolved = true;
        log(`Sent ${dataBuffer.length} bytes to ${ip}:${port}`);
        resolve({ success: true, bytes: dataBuffer.length });
      }
    });

    socket.on('timeout', () => {
      socket.destroy();
      if (!resolved) {
        resolved = true;
        reject(new Error(`Connection timeout to ${ip}:${port}`));
      }
    });

    socket.on('error', (err) => {
      socket.destroy();
      if (!resolved) {
        resolved = true;
        reject(new Error(`Socket error to ${ip}:${port}: ${err.message}`));
      }
    });

    socket.connect(port, ip);
  });
}

// ── Express App ──
const app = express();

// Manual CORS + private network access (no cors npm needed)
app.use((req, res, next) => {
    const origin = req.headers.origin || '*';
    res.setHeader('Access-Control-Allow-Origin', origin);
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept');
    res.setHeader('Access-Control-Allow-Private-Network', 'true');
    res.setHeader('Vary', 'Origin');

    if (req.method === 'OPTIONS') {
        return res.status(204).end();
    }

    next();
});

app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Health check
app.get('/status', (req, res) => {
  res.json({
    status: 'ok',
    hostname: os.hostname(),
    platform: os.platform(),
    node_version: process.version,
    uptime: process.uptime()
  });
});

// List printers
app.get('/printers', async (req, res) => {
  try {
    const printers = await getPrinters();
    log(`Listed ${printers.length} printers`);
    res.json({ success: true, printers });
  } catch (err) {
    log(`Error listing printers: ${err.message}`);
    res.status(500).json({ success: false, message: err.message });
  }
});

// Main print endpoint
app.post('/print', async (req, res) => {
  try {
    const { printer, data: dataBase64, ip, port, mode = 'escpos', encoding = 'cp850' } = req.body;

    if (!dataBase64) {
      return res.status(400).json({ success: false, message: 'No print data provided' });
    }

    let rawBuffer = Buffer.from(dataBase64, 'base64');

    // Auto-fix ESC/POS encoding if needed
    if (mode === 'escpos') {
      const originalLen = rawBuffer.length;
      rawBuffer = prepareEscpos(rawBuffer, encoding);
      if (rawBuffer.length !== originalLen) {
        log(`ESC/POS auto-converted UTF-8->${encoding} (${originalLen} -> ${rawBuffer.length} bytes)`);
      }
    }

    if (ip && port) {
      await printToNetwork(ip, parseInt(port), rawBuffer);
      res.json({ success: true, message: 'Sent to network printer', bytes: rawBuffer.length });
    } else if (printer) {
      await printLocalRaw(printer, rawBuffer);
      res.json({ success: true, message: 'Printed to local printer', bytes: rawBuffer.length });
    } else {
      res.status(400).json({ success: false, message: 'Specify printer name or network (ip+port)' });
    }
  } catch (err) {
    log(`Print failed: ${err.message}`);
    res.status(500).json({ success: false, message: err.message });
  }
});

// Print raw text (converts encoding)
app.post('/print-raw', async (req, res) => {
  try {
    const { printer, text, ip, port, encoding = 'latin1' } = req.body;
    if (!text) {
      return res.status(400).json({ success: false, message: 'No text provided' });
    }

    // Convert text to selected encoding
    let rawBuffer;
    try {
      rawBuffer = iconv.encode(text + '\n\n\n\n', encoding);
    } catch (e) {
      return res.status(400).json({ success: false, message: `Encoding error: ${e.message}` });
    }

    if (ip && port) {
      await printToNetwork(ip, parseInt(port), rawBuffer);
      res.json({ success: true, message: 'Raw text sent to network printer' });
    } else if (printer) {
      await printLocalRaw(printer, rawBuffer);
      res.json({ success: true, message: 'Raw text sent to local printer' });
    } else {
      res.status(400).json({ success: false, message: 'Specify printer or ip+port' });
    }
  } catch (err) {
    log(`Print raw failed: ${err.message}`);
    res.status(500).json({ success: false, message: err.message });
  }
});

// Print ESC/POS from plain text (server generates the ticket)
app.post('/print-escpos-text', async (req, res) => {
  try {
    const { printer, text, ip, port, bold = false, align = 'left', cut = true, codePage = 'cp850' } = req.body;
    if (!text) {
      return res.status(400).json({ success: false, message: 'No text provided' });
    }

    // Build ESC/POS payload
    let payload = Buffer.concat([]);

    // Code page + init
    payload = Buffer.concat([payload, ESC_POS.CP_850, ESC_POS.INIT]);

    // Alignment
    if (align === 'center') payload = Buffer.concat([payload, ESC_POS.ALIGN_CENTER]);
    else if (align === 'right') payload = Buffer.concat([payload, ESC_POS.ALIGN_RIGHT]);
    else payload = Buffer.concat([payload, ESC_POS.ALIGN_LEFT]);

    // Bold
    if (bold) payload = Buffer.concat([payload, ESC_POS.BOLD_ON]);

    // Text content
    const textBytes = iconv.encode(text, codePage);
    payload = Buffer.concat([payload, textBytes]);

    // Bold off
    if (bold) payload = Buffer.concat([payload, ESC_POS.BOLD_OFF]);

    // Feed lines
    payload = Buffer.concat([payload, ESC_POS.FEED_LINES(3)]);

    // Cut
    if (cut) payload = Buffer.concat([payload, ESC_POS.CUT_PARTIAL]);

    log(`ESC/POS ticket generated: ${payload.length} bytes`);

    if (ip && port) {
      await printToNetwork(ip, parseInt(port), payload);
      res.json({ success: true, message: 'ESC/POS ticket sent to network printer', bytes: payload.length });
    } else if (printer) {
      await printLocalRaw(printer, payload);
      res.json({ success: true, message: 'ESC/POS ticket printed locally', bytes: payload.length });
    } else {
      res.status(400).json({ success: false, message: 'Specify printer or ip+port' });
    }
  } catch (err) {
    log(`Print ESC/POS text failed: ${err.message}`);
    res.status(500).json({ success: false, message: err.message });
  }
});

// Open cash drawer via GET (no CORS preflight needed)
app.get('/open-drawer', async (req, res) => {
  try {
    const printer = req.query.printer;
    const ip = req.query.ip;
    const port = req.query.port ? parseInt(req.query.port) : null;

    const drawerCmd = Buffer.concat([
      Buffer.from([0x1B, 0x40]),           // INIT
      Buffer.from([0x1B, 0x70, 0x00, 0x32, 0xFF])  // Open drawer pin 2
    ]);

    if (ip && port) {
      await printToNetwork(ip, port, drawerCmd);
      res.json({ success: true, message: 'Drawer opened via network' });
    } else if (printer) {
      await printLocalRaw(printer, drawerCmd);
      res.json({ success: true, message: 'Drawer opened via local printer' });
    } else {
      res.status(400).json({ success: false, message: 'Specify ?printer=NAME or ?ip=IP&port=PORT' });
    }
  } catch (err) {
    log(`Open drawer failed: ${err.message}`);
    res.status(500).json({ success: false, message: err.message });
  }
});

// 404
app.use((req, res) => {
  res.status(404).json({ success: false, message: 'Not found' });
});

// Error handler
app.use((err, req, res, next) => {
  log(`Unhandled error: ${err.message}`);
  res.status(500).json({ success: false, message: err.message });
});

// ── Start ──
app.listen(PORT, HOST, async () => {
  log(`=== Print Server running on http://${HOST}:${PORT} ===`);
  log(`Platform: ${os.platform()} | Node: ${process.version}`);

  if (IS_WIN && !fs.existsSync(RAW_PRINT_PS1)) {
    log(`WARNING: raw-print.ps1 not found at ${RAW_PRINT_PS1}. Local Windows printing will fail.`);
  }

  try {
    const printers = await getPrinters();
    if (printers.length > 0) {
      log(`Available printers: ${printers.map(p => p.name).join(', ')}`);
    } else {
      log('No printers detected');
    }
  } catch (e) {
    log(`Could not list printers: ${e.message}`);
  }
});
