/*
 * Screenshots for the editor guide in the CP (resources/redaktion/bilder).
 *
 * Needs the demo pages (php scripts/docs/demo-content.php) and a local CP user:
 *
 *     DOCS_EMAIL=… DOCS_PASSWORD=… node scripts/docs/screenshots.mjs [name …]
 *
 * Without arguments all screenshots are taken, otherwise only the named ones.
 */

import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const base = process.env.DOCS_URL ?? 'https://baustoffkreislauf.ch.test';
const out = join(dirname(fileURLToPath(import.meta.url)), '../../resources/redaktion/bilder');
const only = process.argv.slice(2);

const page = (slug) => `/cp/collections/pages/entries/doku-${slug}`;
const demo = (slug) => `/doku-inhaltselemente/${slug}`;

// cp: edit form, sets expanded, cropped to the «Inhalte» field.
// web: the page's <main>, cut off after maxHeight pixels.
const shots = [
  // Overview
  { name: 'cp-seite', cp: page('texteditor'), crop: 'page', sidebar: true, height: 1000 },
  { name: 'cp-set-auswahl', cp: page('texteditor'), action: 'set-picker', crop: 'page', height: 1050 },
  { name: 'cp-bard-sets', cp: page('newsletter-formular'), action: 'bard-picker', crop: 'page', height: 1100 },
  { name: 'cp-bard-toolbar', cp: page('texteditor'), action: 'bard-toolbar' },
  { name: 'cp-tab-medien', cp: page('texteditor'), tab: 'Medien', crop: 'page', height: 800 },
  { name: 'cp-tab-seo', cp: page('texteditor'), tab: 'SEO', crop: 'page', height: 800 },
  { name: 'cp-tab-einstellungen', cp: page('texteditor'), tab: 'Einstellungen', crop: 'page', height: 800 },

  // Page elements
  ...[
    'texteditor', 'texteditor-einzug', 'teaser', 'kacheln', 'beitraege', 'bilder', 'bild-text',
    'video', 'modul', 'team', 'sponsoren', 'stoerer', 'zitat', 'interaktives-svg',
    // Elements inside the text editor
    'download', 'datei-geschuetzt', 'bilder-download', 'button', 'newsletter-formular', 'embed-code',
  ].flatMap((slug) => [
    // Only the result of «Texteinzug» is shown; the module on the real pages below.
    ...(slug === 'texteditor-einzug' ? [] : [{ name: `cp-${slug}`, cp: page(slug) }]),
    ...(slug === 'modul' ? [] : [{ name: `web-${slug}`, web: demo(slug) }]),
  ]),

  // Module: the real pages
  { name: 'web-modul-anlaesse', web: '/aktuell/anlaesse', maxHeight: 1100 },
  { name: 'web-modul-publikationen', web: '/fachbereiche/publikationen', maxHeight: 1100 },
  

  // Posts
  { name: 'cp-beitrag', cp: '/cp/collections/posts/entries/dafbbf5d-94bc-4c10-9353-7df596c8fe77', crop: 'page', height: 1600 },
  { name: 'cp-beitrag-vorschau', cp: '/cp/collections/posts/entries/dafbbf5d-94bc-4c10-9353-7df596c8fe77', tab: 'Vorschau', crop: 'page', height: 900 },
  { name: 'web-beitrag', web: '/fachthemen/am-anfang-war-der-gletscher', maxHeight: 1700 },
];

const browser = await chromium.launch();
const context = await browser.newContext({
  ignoreHTTPSErrors: true,
  viewport: { width: 1440, height: 1100 },
  deviceScaleFactor: 2,
});
const tab = await context.newPage();
mkdirSync(out, { recursive: true });

await login();

for (const shot of shots) {
  if (only.length && !only.includes(shot.name)) continue;
  try {
    shot.cp ? await cpShot(shot) : await webShot(shot);
    console.log('✓', shot.name);
  } catch (error) {
    console.log('✗', shot.name, error.message.split('\n')[0]);
  }
}

await browser.close();

async function login() {
  await tab.goto(base + '/cp/auth/login');
  await tab.fill('input[name=email]', process.env.DOCS_EMAIL);
  await tab.fill('input[name=password]', process.env.DOCS_PASSWORD);
  await tab.keyboard.press('Enter');
  await tab.waitForURL(/\/cp(?!\/auth)/);
}

// The local install runs Statamic Pro in trial mode, which nags on every page.
async function dismissLicenseAlert() {
  const alert = tab.getByRole('dialog').filter({ hasText: 'Lizenz' });
  if (await alert.isVisible().catch(() => false)) {
    await alert.getByRole('button').first().click(); // «Snooze»
    await alert.waitFor({ state: 'hidden' });
  }
}

async function cpShot({ name, cp, tab: tabName, action, crop = 'field', height = 1100, sidebar = false, maxHeight = 3200 }) {
  await tab.goto(base + cp);
  await settle();
  await dismissLicenseAlert();

  if (tabName) {
    await tab.getByRole('tab', { name: tabName }).click();
  } else if (action !== 'set-picker' && action !== 'bard-picker') {
    await expandAll();
  }

  if (action === 'set-picker') {
    await tab.getByRole('button', { name: 'Set hinzufügen' }).last().click();
    await tab.getByText('Hauptelemente', { exact: true }).last().click({ timeout: 3000 }).catch(() => {});
  } else if (action === 'bard-picker') {
    await expandAll();
    // Put the caret at the end of the paragraph and open an empty line below it.
    const paragraph = tab.locator('.ProseMirror > p').filter({ visible: true }).last();
    await paragraph.click();
    await paragraph.evaluate((el) => {
      const range = document.createRange();
      range.selectNodeContents(el);
      range.collapse(false);
      getSelection().removeAllRanges();
      getSelection().addRange(range);
    });
    await tab.keyboard.press('Enter');
    await tab.waitForTimeout(500);
    await tab.locator('.bard-fieldtype').getByRole('button', { name: 'Set hinzufügen' }).click();
    await tab.getByText('Downloads', { exact: true }).last().click({ timeout: 3000 }).catch(() => {});
  } else if (action === 'bard-toolbar') {
    await tab.locator('.ProseMirror p').nth(1).dblclick();
  }
  await tab.waitForTimeout(500);
  if (action !== 'bard-toolbar') await tab.mouse.move(0, 0);

  // Whole window, without the CP navigation unless asked for.
  if (crop === 'page') {
    const x = sidebar ? 0 : 236;
    await tab.screenshot({ path: join(out, `${name}.png`), clip: { x, y: 56, width: 1440 - x, height: height - 56 } });
    return;
  }

  // Element screenshots are cut off at the viewport, so grow it to fit.
  const target = tab.locator('.replicator-fieldtype, .bard-fieldtype').first();
  const box = await target.boundingBox();
  await tab.setViewportSize({ width: 1440, height: Math.max(1100, Math.ceil(box.y + box.height) + 100) });
  await tab.waitForTimeout(300);
  // Very long forms are cut off; below this height the rest only repeats.
  const field = await target.boundingBox();
  await tab.screenshot({ path: join(out, `${name}.png`), clip: { ...field, height: Math.min(field.height, maxHeight) } });
  await tab.setViewportSize({ width: 1440, height: 1100 });
}

// Pages with embeds (YouTube, maps, analytics) never go fully idle.
async function settle() {
  await tab.waitForLoadState('load');
  await tab.waitForLoadState('networkidle', { timeout: 8000 }).catch(() => {});
  await tab.waitForTimeout(500);
}

// Collapsed sets stay collapsed until each replicator's «expand all» is clicked,
// nested ones only appear after their parent opened.
async function expandAll() {
  await tab.getByRole('button', { name: 'Alle Sets ausklappen' }).first().waitFor();
  await tab.waitForTimeout(500);
  for (let round = 0; round < 3; round++) {
    const buttons = tab.getByRole('button', { name: 'Alle Sets ausklappen' });
    const count = await buttons.count();
    for (let i = 0; i < count; i++) {
      await buttons.nth(i).click({ timeout: 1500 }).catch(() => {});
    }
    await tab.waitForTimeout(300);
  }
}

// Saved as JPEG: the full-width photos make PNGs of several MB.
async function webShot({ name, web, maxHeight = 1300 }) {
  await tab.goto(base + web);
  await settle();
  await tab.evaluate(() => document.fonts.ready);
  // Lazy images only load once scrolled into view.
  await tab.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 600) {
      window.scrollTo(0, y);
      await new Promise((resolve) => setTimeout(resolve, 120));
    }
    window.scrollTo(0, 0);
  });
  await tab.waitForTimeout(800);

  // <main> has generous padding at the bottom; stop below its last visible child.
  // Left and right, crop to the content: full-width bands don't count.
  const { top, bottom, left, right } = await tab.evaluate(() => {
    const main = document.querySelector('main');
    const children = [...main.children].map((el) => el.getBoundingClientRect()).filter((r) => r.height > 0);
    const content = [...main.querySelectorAll('*')]
      .map((el) => el.getBoundingClientRect())
      .filter((r) => r.width > 0 && r.height > 0 && r.width < window.innerWidth * 0.95);
    return {
      top: main.getBoundingClientRect().top + window.scrollY,
      bottom: Math.max(...children.map((r) => r.bottom)) + window.scrollY + 50,
      left: Math.max(0, Math.min(...content.map((r) => r.left)) - 40),
      right: Math.min(window.innerWidth, Math.max(...content.map((r) => r.right)) + 40),
    };
  });

  await tab.screenshot({
    path: join(out, `${name}.jpg`),
    type: 'jpeg',
    quality: 82,
    fullPage: true,
    clip: { x: left, y: top, width: right - left, height: Math.min(bottom - top, maxHeight) },
  });
}
