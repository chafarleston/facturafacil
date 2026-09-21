const fs = require('fs');
const path = require('path');

const docsDir = path.join(__dirname, '..', 'docs');
const mdPath = path.join(docsDir, 'MANUAL_DE_USUARIO.md');
const outPath = path.join(docsDir, 'manual', 'manual-usuario.html');

const md = fs.readFileSync(mdPath, 'utf8').replace(/\r\n/g, '\n');

function inlineImage(src) {
  const abs = path.join(docsDir, src);
  if (!fs.existsSync(abs)) return '<em>[imagen no encontrada: ' + src + ']</em>';
  const b64 = fs.readFileSync(abs).toString('base64');
  const mime = src.endsWith('.png') ? 'image/png' : 'image/jpeg';
  return '<div class="img-wrap"><img src="data:' + mime + ';base64,' + b64 + '" alt="' + src + '"><p class="img-caption">' + path.basename(src) + '</p></div>';
}

const body = [];
let inUl = false;
let inTable = false;
let tableRows = [];

for (const raw of md.split('\n')) {
  const line = raw.trimEnd();
  if (line.trim() === '---') { body.push('<hr>'); continue; }
  if (line.trim() === '') { if (inUl) { body.push('</ul>'); inUl = false; } body.push(''); continue; }

  const h = line.match(/^(#{1,6})\s+(.+)$/);
  if (h) {
    if (inUl) { body.push('</ul>'); inUl = false; }
    const lvl = Math.min(h[1].length, 4);
    body.push(`<h${lvl}>${h[2]}</h${lvl}>`);
    continue;
  }

  const img = line.match(/^!\[[^\]]*\]\(([^)]+)\)$/);
  if (img) { body.push(inlineImage(img[1])); continue; }

  const quote = line.match(/^>\s?(.*)$/);
  if (quote && !line.startsWith('> |')) { body.push('<blockquote>' + quote[1] + '</blockquote>'); continue; }

  if (line.startsWith('|') && line.endsWith('|')) {
    const cells = line.split('|').slice(1, -1).map(c => c.trim());
    if (!inTable) {
      inTable = true; tableRows = [cells];
    } else {
      tableRows.push(cells);
    }
    continue;
  }
  if (inTable) {
    const rows = tableRows.filter((r, i) => i !== 1 && r.some(c => c)); // salta separator
    const head = tableRows[0];
    body.push('<table><thead><tr>' + head.map(c => '<th>' + c + '</th>').join('') + '</tr></thead><tbody>' +
      rows.map(r => '<tr>' + r.map(c => '<td>' + c + '</td>').join('') + '</tr>').join('') + '</tbody></table>');
    inTable = false;
  }

  const li = line.match(/^[-*]\s+(.+)$/);
  if (li) { if (!inUl) { body.push('<ul>'); inUl = true; } body.push('<li>' + li[1] + '</li>'); continue; }
  const li2 = line.match(/^\d+\.\s+(.+)$/);
  if (li2) { if (!inUl) { body.push('<ol>'); inUl = true; } body.push('<li>' + li2[1] + '</li>'); continue; }

  // párrafo: convierte **negritas** y `código` y markdown interno en el md path simple
  let para = line;
  para = para.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  para = para.replace(/`([^`]+)`/g, '<code>$1</code>');
  para = para.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a>$1</a>');
  body.push('<p>' + para + '</p>');
}
if (inUl) body.push('</ul>');
if (inTable) {
  const rows = tableRows.filter((r, i) => i !== 1 && r.some(c => c));
  body.push('<table><thead><tr>' + tableRows[0].map(c => '<th>' + c + '</th>').join('') + '</tr></thead><tbody>' +
    rows.map(r => '<tr>' + r.map(c => '<td>' + c + '</td>').join('') + '</tr>').join('') + '</tbody></table>');
}

const html = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Manual de Usuario — FacturaFácil</title>
<style>
  @page { size: A4; margin: 16mm 14mm; }
  body { font-family: 'Segoe UI', Arial, sans-serif; color: #1f2937; line-height: 1.55; margin: 0; padding: 24px 18px; }
  h1, h2, h3 { color: #0f4c81; }
  h1 { border-bottom: 3px solid #0f4c81; padding-bottom: 8px; font-size: 24px; }
  h2 { border-bottom: 1px solid #c7d2fe; padding-bottom: 4px; margin-top: 0; page-break-after: avoid; }
  h3 { margin-top: 14px; page-break-after: avoid; }
  img { max-width: 100%; height: auto; border: 1px solid #d1d5db; border-radius: 6px; display: block; }
  .img-wrap { margin: 10px 0; text-align: center; page-break-inside: avoid; }
  .img-caption { font-size: 11px; color: #6b7280; margin: 4px 0 0; }
  table { border-collapse: collapse; width: 100%; margin: 10px 0; font-size: 13px; }
  th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
  th { background: #eef2ff; }
  blockquote { border-left: 4px solid #0f4c81; background: #f0f6ff; padding: 6px 12px; margin: 8px 0; }
  code { background: #f3f4f6; padding: 1px 5px; border-radius: 4px; font-size: 12px; }
  hr { border: none; border-top: 1px solid #d1d5db; margin: 16px 0; }
  ul, ol { padding-left: 22px; }
  ul li, ol li { margin: 3px 0; }
  a { color: #0f4c81; }
  @media print { body { padding: 0; } h2 { page-break-before: auto; } }
</style>
</head>
<body>
${body.join('\n')}
</body>
</html>`;

fs.writeFileSync(outPath, html, 'utf8');
console.log('HTML generado: ' + outPath + ' (' + Math.round(html.length / 1024) + ' KB)');