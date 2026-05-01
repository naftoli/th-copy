const express = require('express');
const fs = require('fs');
const path = require('path');
const { marked } = require('marked');

const app = express();
const PORT = 3456;
const RULES_DIR = path.join(__dirname, '..', 'business-rules');

const MODULE_LABELS = {
  'base-management-auth':       'Base Management & Auth',
  'missions':                   'Missions',
  'rewards':                    'Rewards Program',
  'chidon':                     'Chidon',
  'campaigns':                  'Campaigns',
  'promotions':                 'Promotions',
  'rebbe-gift-marathon-yearly': "Rebbe's Gift, Mission Marathon & Yearly Gift",
  'reports-shipping-accounting':'Reports, Shipping & Accounting',
  'helpdesk-registration-camps':'Helpdesk, Registration & Camps',
  'registration':               'Registration (legacy)',
};

function getModules() {
  return fs.readdirSync(RULES_DIR)
    .filter(f => f.endsWith('.md'))
    .map(f => {
      const slug = f.replace('.md', '');
      return { slug, label: MODULE_LABELS[slug] || slug, file: f };
    })
    .sort((a, b) => {
      const order = Object.keys(MODULE_LABELS);
      const ai = order.indexOf(a.slug);
      const bi = order.indexOf(b.slug);
      if (ai === -1 && bi === -1) return a.label.localeCompare(b.label);
      if (ai === -1) return 1;
      if (bi === -1) return -1;
      return ai - bi;
    });
}

function countRules(markdown) {
  return (markdown.match(/^\d+\./gm) || []).length;
}

function countQuestions(markdown) {
  const m = markdown.match(/## Open Questions[\s\S]*?(?=\n## |\n# |$)/g);
  if (!m) return 0;
  return m.reduce((n, block) => n + (block.match(/^- /gm) || []).length, 0);
}

function shell(title, body, activeSlug = null) {
  const nav = getModules().map(m => `
    <li class="${m.slug === activeSlug ? 'active' : ''}">
      <a href="/module/${m.slug}">${m.label}</a>
    </li>`).join('');

  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${title} — Business Rules</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      font-size: 15px;
      color: #1a1a2e;
      background: #f5f7fa;
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    nav {
      width: 260px;
      min-width: 260px;
      background: #1a1a2e;
      color: #c8d0e0;
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }
    .nav-header {
      padding: 20px 18px 14px;
      border-bottom: 1px solid #2e3050;
    }
    .nav-header a {
      text-decoration: none;
      color: #fff;
      font-weight: 700;
      font-size: 14px;
      letter-spacing: .5px;
      text-transform: uppercase;
    }
    .nav-header small {
      display: block;
      color: #7880a0;
      font-size: 11px;
      margin-top: 3px;
    }
    nav ul { list-style: none; padding: 10px 0; flex: 1; }
    nav ul li a {
      display: block;
      padding: 8px 18px;
      color: #c8d0e0;
      text-decoration: none;
      font-size: 13px;
      line-height: 1.4;
      border-left: 3px solid transparent;
      transition: background .15s, color .15s;
    }
    nav ul li a:hover { background: #252846; color: #fff; }
    nav ul li.active a {
      background: #252846;
      color: #7eb8f7;
      border-left-color: #7eb8f7;
      font-weight: 600;
    }

    /* ── Main ── */
    main {
      flex: 1;
      padding: 36px 48px;
      max-width: 960px;
    }

    h1.page-title {
      font-size: 26px;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 6px;
    }
    .meta { color: #6b7280; font-size: 13px; margin-bottom: 32px; }
    .meta span { margin-right: 16px; }
    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }
    .badge-blue  { background: #dbeafe; color: #1d4ed8; }
    .badge-amber { background: #fef3c7; color: #92400e; }

    /* ── Index cards ── */
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 16px;
      margin-top: 8px;
    }
    .card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      padding: 20px 22px;
      text-decoration: none;
      color: inherit;
      transition: box-shadow .15s, border-color .15s;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); border-color: #bfcfe8; }
    .card-title { font-weight: 600; font-size: 15px; color: #1a1a2e; }
    .card-stats { font-size: 12px; color: #6b7280; }

    /* ── Markdown body ── */
    .md-body { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 36px 40px; }
    .md-body h1 { font-size: 22px; margin-bottom: 4px; }
    .md-body h2 { font-size: 17px; margin: 28px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; color: #1d4ed8; }
    .md-body h3 { font-size: 15px; margin: 22px 0 8px; color: #374151; }
    .md-body h4 { font-size: 14px; margin: 16px 0 6px; color: #4b5563; }
    .md-body p  { line-height: 1.7; color: #374151; margin-bottom: 10px; }
    .md-body ol { padding-left: 22px; margin-bottom: 10px; }
    .md-body ul { padding-left: 20px; margin-bottom: 10px; }
    .md-body li { line-height: 1.65; color: #374151; margin-bottom: 4px; }
    .md-body code { background: #f3f4f6; padding: 1px 5px; border-radius: 4px; font-size: 13px; font-family: 'SF Mono', 'Fira Code', monospace; }
    .md-body pre  { background: #f3f4f6; padding: 14px 16px; border-radius: 8px; overflow-x: auto; margin-bottom: 12px; }
    .md-body pre code { background: none; padding: 0; }
    .md-body hr   { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
    .md-body strong { color: #111827; }
    .md-body blockquote { border-left: 3px solid #7eb8f7; padding-left: 14px; color: #4b5563; margin-bottom: 10px; }

    /* rule numbers stand out */
    .md-body ol > li { padding-left: 4px; }

    /* open-questions section gets amber tint */
    .md-body h2:has(+ ul),
    .md-body h2:has(+ p + ul) { color: #92400e; }

    @media (max-width: 700px) {
      body { flex-direction: column; }
      nav { width: 100%; min-width: 0; height: auto; position: static; }
      main { padding: 20px 16px; }
      .md-body { padding: 20px 18px; }
    }
  </style>
</head>
<body>
  <nav>
    <div class="nav-header">
      <a href="/">Business Rules</a>
      <small>TH Platform</small>
    </div>
    <ul>${nav}</ul>
  </nav>
  <main>${body}</main>
</body>
</html>`;
}

// Index
app.get('/', (req, res) => {
  const modules = getModules();
  let totalRules = 0;
  let totalQuestions = 0;

  const cards = modules.map(m => {
    const md = fs.readFileSync(path.join(RULES_DIR, m.file), 'utf8');
    const rules = countRules(md);
    const questions = countQuestions(md);
    totalRules += rules;
    totalQuestions += questions;
    return `<a class="card" href="/module/${m.slug}">
      <div class="card-title">${m.label}</div>
      <div class="card-stats">
        <span class="badge badge-blue">${rules} rules</span>
        ${questions ? `<span class="badge badge-amber">${questions} open</span>` : ''}
      </div>
    </a>`;
  }).join('');

  const body = `
    <h1 class="page-title">Business Rules</h1>
    <div class="meta">
      <span>${modules.length} modules</span>
      <span class="badge badge-blue">${totalRules} total rules</span>
      ${totalQuestions ? `<span class="badge badge-amber">${totalQuestions} open questions</span>` : ''}
    </div>
    <div class="card-grid">${cards}</div>`;

  res.send(shell('Index', body));
});

// Module page
app.get('/module/:slug', (req, res) => {
  const slug = req.params.slug;
  const filePath = path.join(RULES_DIR, `${slug}.md`);

  if (!fs.existsSync(filePath)) {
    return res.status(404).send(shell('Not Found', '<p>Module not found.</p>'));
  }

  const md = fs.readFileSync(filePath, 'utf8');
  const rules = countRules(md);
  const questions = countQuestions(md);
  const label = MODULE_LABELS[slug] || slug;

  const body = `
    <h1 class="page-title">${label}</h1>
    <div class="meta">
      <span class="badge badge-blue">${rules} rules</span>
      ${questions ? `<span class="badge badge-amber">${questions} open questions</span>` : ''}
    </div>
    <div class="md-body">${marked.parse(md)}</div>`;

  res.send(shell(label, body, slug));
});

app.listen(PORT, () => {
  console.log(`Business Rules viewer running at http://localhost:${PORT}`);
});
