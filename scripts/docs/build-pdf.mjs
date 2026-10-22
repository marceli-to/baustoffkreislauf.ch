/*
 * Builds docs/redaktion/Redaktionsanleitung.pdf from anleitung.md.
 *
 *     node scripts/docs/build-pdf.mjs [--html]
 *
 * Markdown conventions:
 *   ![Caption](bilder/cp-….png)   image with caption; «cp-» screenshots are
 *                                  shown narrower, «web-» ones full width
 *   > **Tipp:** …                  highlighted box
 *   <div class="pair">…</div>      two images side by side
 */

import { chromium } from 'playwright';
import { marked } from 'marked';
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const dir = join(dirname(fileURLToPath(import.meta.url)), '../../docs/redaktion');
const markdown = readFileSync(join(dir, 'anleitung.md'), 'utf8');

marked.use({
  renderer: {
    image({ href, text }) {
      const kind = href.includes('/cp-') ? 'cp' : 'web';
      return `<figure class="${kind}"><img src="${href}" alt=""><figcaption>${text}</figcaption></figure>`;
    },
    // A paragraph holding only images would wrap <figure> in <p>.
    paragraph({ tokens }) {
      const html = this.parser.parseInline(tokens);
      return html.trim().startsWith('<figure') ? html : `<p>${html}</p>\n`;
    },
  },
});

const today = new Date().toLocaleDateString('de-CH', { month: 'long', year: 'numeric' });
const body = marked.parse(markdown.replaceAll('{{ stand }}', today));

const html = `<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Redaktionsanleitung – Baustoff Kreislauf Schweiz</title>
<link rel="stylesheet" href="https://use.typekit.net/iaz8rmb.css">
<style>${readFileSync(join(dir, 'style.css'), 'utf8')}</style>
</head>
<body>${body}</body>
</html>`;

const htmlPath = join(dir, 'anleitung.html');
writeFileSync(htmlPath, html);

if (process.argv.includes('--html')) {
  console.log(htmlPath);
  process.exit();
}

const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto(pathToFileURL(htmlPath).href, { waitUntil: 'networkidle' });
await page.evaluate(() => document.fonts.ready);
await page.pdf({
  path: join(dir, 'Redaktionsanleitung.pdf'),
  format: 'A4',
  printBackground: true,
  displayHeaderFooter: true,
  headerTemplate: '<span></span>',
  footerTemplate: `
    <div style="width:100%;font-family:Helvetica,Arial,sans-serif;font-size:7.5pt;color:#888;padding:0 18mm;display:flex;justify-content:space-between">
      <span>Baustoff Kreislauf Schweiz · Redaktionsanleitung Inhaltselemente</span>
      <span><span class="pageNumber"></span> / <span class="totalPages"></span></span>
    </div>`,
  margin: { top: '16mm', bottom: '18mm', left: '18mm', right: '18mm' },
});
await browser.close();

console.log(join(dir, 'Redaktionsanleitung.pdf'));
