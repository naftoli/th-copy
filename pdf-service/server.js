const express = require('express');
const puppeteer = require('puppeteer');
const { PDFDocument } = require('pdf-lib');

const app = express();
app.use(express.json({ limit: '10mb' }));

let browser;

async function getBrowser() {
  if (!browser || !browser.connected) {
    browser = await puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
  }
  return browser;
}

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok' });
});

// Single PDF
app.post('/generate', async (req, res) => {
  const { html, options = {} } = req.body;
  let page;

  if (!html) {
    return res.status(400).json({ error: 'html is required' });
  }

  try {
    const browser = await getBrowser();
    page = await browser.newPage();
    await page.setContent(html, { waitUntil: 'networkidle0' });
    const pdf = await page.pdf({
      format: options.format || 'A4',
      printBackground: true,
      margin: options.margin || { top: '10mm', bottom: '10mm', left: '10mm', right: '10mm' }
    });

    res.set('Content-Type', 'application/pdf');
    res.send(pdf);

  } catch (err) {
    console.error(err);
    res.status(500).json({ error: err.message });
  } finally {
    if (page && !page.isClosed()) await page.close();
  }
});

// Batch + merge
app.post('/generate-and-merge', async (req, res) => {
  const { pages, options = {} } = req.body;
  const openPages = [];

  if (!Array.isArray(pages) || pages.length === 0) {
    return res.status(400).json({ error: 'pages array is required' });
  }

  try {
    const browser = await getBrowser();

    const pdfBuffers = await Promise.all(pages.map(async ({ html }) => {
      const page = await browser.newPage();
      openPages.push(page);
      await page.setContent(html, { waitUntil: 'networkidle0' });
      const pdf = await page.pdf({
        format: options.format || 'A4',
        printBackground: true,
        margin: options.margin || { top: '10mm', bottom: '10mm', left: '10mm', right: '10mm' }
      });
      return pdf;
    }));

    const merged = await PDFDocument.create();
    for (const buffer of pdfBuffers) {
      const doc = await PDFDocument.load(buffer);
      const copiedPages = await merged.copyPages(doc, doc.getPageIndices());
      copiedPages.forEach(p => merged.addPage(p));
    }

    const mergedPdf = Buffer.from(await merged.save());
    res.json({ pdf: mergedPdf.toString('base64') });

  } catch (err) {
    console.error(err);
    res.status(500).json({ error: err.message });
  } finally {
    await Promise.all(openPages.map(p => p.isClosed() ? null : p.close()));
  }
});

const PORT = process.env.PORT || 3001;
app.listen(PORT, '127.0.0.1', () => {
  console.log('PDF service running on port ' + PORT);
});