const puppeteer  = require('puppeteer');
const { PDFDocument } = require('pdf-lib');
const Redis      = require('ioredis');
const nodemailer = require('nodemailer');

const redis = new Redis({
  host: '127.0.0.1',
  port: 6379,
  password: 'Naftoli@5783!'
});
const QUEUE_KEY = 'pdf_jobs';

// ── Configure your SMTP here ──────────────────────────────────────────────────
const mailer = nodemailer.createTransport({
  host:   'localhost',
  port:   25,
  secure: false,
  tls: {
    rejectUnauthorized: false  // needed on some cPanel setups
  }
});
// ─────────────────────────────────────────────────────────────────────────────

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

async function setStatus(jobId, status, extra) {
  const data = Object.assign(
    { jobId: jobId, status: status, updatedAt: Date.now() },
    extra || {}
  );
  // Keep status in Redis for 2 hours
  await redis.set('pdf_status:' + jobId, JSON.stringify(data), 'EX', 7200);
}

async function processJob(job) {
  const jobId   = job.jobId;
  const pages   = job.pages;
  const email   = job.email;
  const name    = job.name;
  const options = job.options || {};
  const openPages = [];

  try {
    await setStatus(jobId, 'processing', { progress: 'Rendering pages...' });

    const browser = await getBrowser();

    const pdfBuffers = await Promise.all(pages.map(function(item) {
      return browser.newPage().then(function(page) {
        openPages.push(page);
        return page.setContent(item.html, { waitUntil: 'networkidle0' })
          .then(function() {
            return page.pdf({
              format: options.format || 'A4',
              printBackground: true,
              margin: options.margin || { top: '20mm', bottom: '20mm', left: '15mm', right: '15mm' }
            });
          });
      });
    }));

    await setStatus(jobId, 'processing', { progress: 'Merging PDF...' });

    const merged = await PDFDocument.create();
    for (var i = 0; i < pdfBuffers.length; i++) {
      var doc = await PDFDocument.load(pdfBuffers[i]);
      var copiedPages = await merged.copyPages(doc, doc.getPageIndices());
      copiedPages.forEach(function(p) { merged.addPage(p); });
    }

    const mergedPdf = Buffer.from(await merged.save());

    await setStatus(jobId, 'processing', { progress: 'Sending email...' });

    const recipients = Array.isArray(email)
      ? email.filter(Boolean).join(', ')
      : String(email || '').trim();

    if (!recipients) {
      throw new Error('No valid recipient email(s) found in job payload');
    }

    await mailer.sendMail({
      from:        '"Tzivos Hashem" <dev@tzivoshashem.org>',
      to:          recipients,
      subject:     'Your PDF Duch is Ready',
      text:        'Please find your PDF Duch attached.',
      attachments: [{ filename: 'pdf.pdf', content: mergedPdf }]
    });

    await setStatus(jobId, 'complete', {
      progress: 'Done',
      message:  'Your PDF has been emailed to ' + recipients
    });

    console.log('Job complete:', jobId);

  } catch (err) {
    console.error('Job failed:', jobId, err.message);
    await setStatus(jobId, 'failed', { error: err.message });
  } finally {
    await Promise.all(openPages.map(function(p) {
      return p.isClosed() ? null : p.close();
    }));
  }
}

async function run() {
  console.log('PDF worker started, waiting for jobs...');

  while (true) {
    try {
      // BLPOP blocks until a job arrives — uses no CPU while idle
      const result = await redis.blpop(QUEUE_KEY, 5);

      if (!result) continue;

      const job = JSON.parse(result[1]);
      console.log('Picked up job:', job.jobId);
      await processJob(job);

    } catch (err) {
      console.error('Worker loop error:', err.message);
      // Wait 2 seconds before retrying to avoid hammering on persistent errors
      await new Promise(function(r) { setTimeout(r, 2000); });
    }
  }
}

run();