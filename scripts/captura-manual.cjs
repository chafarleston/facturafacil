const { chromium } = require('playwright-core');
const fs = require('fs');
const path = require('path');

const URL = 'http://facturafacil.test';
const OUT = path.join(__dirname, '..', 'docs', 'manual-img');
const EMAIL = 'manual@demo.local';
const PASS = 'Manual2026!';
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch({ executablePath: CHROME, headless: true, args: ['--no-sandbox'] });
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1.5 });
  const page = await ctx.newPage();

  // aborta polling/SSE para estabilizar capturas (no rompe la carga inicial server-side)
  const polls = [
    '**/restaurant/stream', '**/restaurant/kitchen-stream',
    '**/restaurant/active-orders', '**/restaurant/locks',
    '**/restaurant/print-status', '**/restaurant/kitchen-orders',
    '**/restaurant/kiosk-orders',
  ];
  for (const p of polls) page.route(p, r => r.abort());

  const shot = async (name, opts = {}) => {
    await page.waitForTimeout(opts.wait || 1600);
    await page.screenshot({ path: path.join(OUT, name), fullPage: !!opts.full });
    console.log('OK', name);
  };

  const goto = async (url) => page.goto(URL + url, { waitUntil: 'domcontentloaded', timeout: 25000 });
  const snap = async (name, url, opts = {}) => {
    try { await goto(url); await shot(name, opts); }
    catch (e) { console.log('ERR', name, e.message); }
  };

  // 01 Login (público)
  await snap('01-login.png', '/login');

  // Iniciar sesión
  try {
    await goto('/login');
    await page.fill('input[name=email]', EMAIL);
    await page.fill('input[name=password]', PASS);
    await page.click('button[type=submit]');
    await page.waitForTimeout(2500);
    console.log('LOGIN OK ->', page.url());
  } catch (e) { console.log('LOGIN ERR', e.message); }

  // 02-31 pantallas principales
  const paginas = [
    ['02-dashboard.png', '/dashboard'],
    ['03-pos.png', '/pos'],
    ['04-restaurante.png', '/restaurant'],
    ['05-kds-cocina.png', '/restaurant/kitchen/cocina'],
    ['06-autopedido.png', '/autopedido'],
    ['07-caja.png', '/cashregisters'],
    ['08-caja-movimientos.png', '/cash-movements'],
    ['09-caja-config-reporte.png', '/cash-report-settings'],
    ['10-comprobantes.png', '/invoices'],
    ['11-boletas-nv.png', '/invoices/nv'],
    ['12-comprobante-nuevo.png', '/invoices/create'],
    ['13-resumenes-sunat.png', '/sunat-summaries'],
    ['14-productos.png', '/products'],
    ['15-producto-nuevo.png', '/products/create'],
    ['16-producto-compuesto.png', '/products/composite/create'],
    ['17-inventario.png', '/products/inventory-report'],
    ['18-clientes.png', '/customers'],
    ['19-series.png', '/series'],
    ['20-series-nueva.png', '/series/create'],
    ['21-empresas.png', '/companies'],
    ['22-usuarios.png', '/users'],
    ['23-roles.png', '/roles'],
    ['24-personal.png', '/personal'],
    ['25-horarios.png', '/schedules'],
    ['26-reglas-tardanza.png', '/attendance-rules'],
    ['27-marcaciones.png', '/attendance/logs'],
    ['28-reportes-asistencia.png', '/attendance/reports'],
    ['29-marcador.png', '/marcar'],
    ['30-backup.png', '/backup'],
    ['31-impresoras.png', '/printers'],
  ];
  for (const [name, url] of paginas) await snap(name, url, { full: true });

  // 32-34 Restaurante: pedido abierto + modal añadir producto + precuenta
  try {
    await goto('/restaurant');
    await page.waitForSelector('.table-card', { timeout: 8000 }).catch(() => {});
    const ok = await page.evaluate(() => {
      const t = document.querySelector('.table-card.occupied, .table-card.has-order, .table-card.reserved, .table-card[class*="order"]')
        || document.querySelector('.table-card');
      if (!t) return false;
      t.click();
      return true;
    });
    if (!ok) { console.log('NO TABLE'); }
    await page.waitForTimeout(2200);
    await shot('32-restaurante-pedido.png');

    // modal cantidad de producto
    const abierto = await page.evaluate(() => {
      document.querySelectorAll('.product-card, .item-card, [class*="menu-"]').forEach((el, i) => {
        if (i === 0 && typeof el.click === 'function') el.click();
      });
      return true;
    });
    await page.waitForTimeout(1200);
    await shot('33-restaurante-modal-producto.png');

    // precuenta (overlay, sin imprimir)
    await page.evaluate(() => {
      const f = document.querySelector('#btnPrebill');
      if (f) f.click();
    });
    await page.waitForTimeout(900);
    await shot('34-restaurante-precuenta.png');
  } catch (e) { console.log('ERR restaurant modal', e.message); }

  await browser.close();
  console.log('FIN');
})().catch(e => { console.error('FATAL', e); process.exit(1); });