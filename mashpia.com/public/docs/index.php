<?php
// List of available doc files
$docs = [
  'overview'         => 'Overview & Getting Started',
  'base-management'  => 'Base Management',
  'missions'         => 'Missions',
  'rewards'          => 'Rewards Program',
  'chidon'           => 'Chidon HaSefer',
  'reports'          => 'Reports',
  'campaigns'        => 'Campaigns',
  'rebbes-gift'      => "Rebbe's Gift",
  'promotions'       => 'Promotions',
  'other'            => 'Other Features',
];

$page = isset($_GET['page']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['page']) : 'overview';
if (!array_key_exists($page, $docs)) $page = 'overview';

$file = __DIR__ . '/' . $page . '.md';
$content = file_exists($file) ? htmlspecialchars(file_get_contents($file)) : '# Not Found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tzivos Hashem Docs — <?= htmlspecialchars($docs[$page]) ?></title>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; display: flex; min-height: 100vh; background: #f5f6fa; color: #222; }

    /* Sidebar */
    #sidebar {
      width: 240px; min-width: 240px; background: #1a2340; color: #c8d0e0;
      padding: 0; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; overflow-y: auto;
    }
    #sidebar .logo {
      padding: 20px 18px 14px; font-size: 15px; font-weight: 700; color: #fff;
      border-bottom: 1px solid rgba(255,255,255,0.08); line-height: 1.4;
    }
    #sidebar .logo span { display: block; font-size: 11px; font-weight: 400; color: #7a8db0; margin-top: 3px; }
    #sidebar nav { padding: 10px 0; }
    #sidebar a {
      display: block; padding: 9px 18px; color: #c8d0e0; text-decoration: none;
      font-size: 13.5px; border-left: 3px solid transparent; transition: all 0.15s;
    }
    #sidebar a:hover { background: rgba(255,255,255,0.06); color: #fff; }
    #sidebar a.active { background: rgba(255,255,255,0.1); color: #fff; border-left-color: #4a9eff; }

    /* Content */
    #content { flex: 1; padding: 36px 48px; max-width: 900px; }

    /* Markdown styles */
    #content h1 { font-size: 26px; margin-bottom: 8px; color: #1a2340; border-bottom: 2px solid #e0e4ef; padding-bottom: 10px; }
    #content h2 { font-size: 20px; margin: 28px 0 10px; color: #1a2340; }
    #content h3 { font-size: 16px; margin: 20px 0 8px; color: #2c3e6e; }
    #content h4 { font-size: 14px; margin: 16px 0 6px; color: #2c3e6e; font-weight: 600; }
    #content p { line-height: 1.7; margin-bottom: 12px; }
    #content ul, #content ol { margin: 8px 0 12px 22px; line-height: 1.7; }
    #content li { margin-bottom: 4px; }
    #content strong { color: #1a2340; }
    #content code { background: #eef0f6; padding: 2px 6px; border-radius: 3px; font-size: 13px; font-family: 'SF Mono', Monaco, monospace; }
    #content blockquote { border-left: 4px solid #4a9eff; background: #f0f5ff; padding: 10px 16px; margin: 12px 0; border-radius: 0 6px 6px 0; color: #2c3e6e; }
    #content blockquote p { margin: 0; }
    #content hr { border: none; border-top: 1px solid #e0e4ef; margin: 24px 0; }

    /* Tables */
    #content table { border-collapse: collapse; width: 100%; margin: 14px 0; font-size: 14px; }
    #content th { background: #1a2340; color: #fff; padding: 9px 14px; text-align: left; font-weight: 600; }
    #content td { padding: 8px 14px; border-bottom: 1px solid #e0e4ef; }
    #content tr:nth-child(even) td { background: #f8f9fc; }
    #content tr:hover td { background: #eef2ff; }

    @media (max-width: 700px) {
      #sidebar { display: none; }
      #content { padding: 20px; }
    }
  </style>
</head>
<body>

<div id="sidebar">
  <div class="logo">
    Tzivos Hashem
    <span>Platform Documentation</span>
  </div>
  <nav>
    <?php foreach ($docs as $key => $label): ?>
      <a href="?page=<?= $key ?>" class="<?= $key === $page ? 'active' : '' ?>"><?= htmlspecialchars($label) ?></a>
    <?php endforeach; ?>
  </nav>
</div>

<div id="content">
  <div id="md-output"></div>
</div>

<script>
  const raw = <?= json_encode(file_get_contents($file)) ?>;
  document.getElementById('md-output').innerHTML = marked.parse(raw);
</script>

</body>
</html>
