<?php
/**
 * Business Rules Viewer
 * Renders Markdown files from /business-rules/ in the browser.
 * Access: /business-rules.php  or  /business-rules.php?file=BR-chidon.md
 */

$rulesDir = dirname(__DIR__, 3) . '/business-rules';

// Security: only allow .md files from the rules directory
function safeFile(string $dir, string $name): ?string {
    if (!preg_match('/^[\w\-]+\.md$/i', $name)) return null;
    $path = $dir . '/' . $name;
    return (file_exists($path) && is_file($path)) ? $path : null;
}

// Minimal Markdown → HTML renderer (no dependencies)
function renderMarkdown(string $md): string {
    $lines = explode("\n", $md);
    $html  = '';
    $inCode  = false;
    $inTable = false;
    $inList  = false;
    $buf     = [];

    $flushList = function() use (&$html, &$buf, &$inList) {
        if ($inList) { $html .= '<ul>' . implode('', $buf) . '</ul>'; $buf = []; $inList = false; }
    };
    $flushTable = function() use (&$html, &$buf, &$inTable) {
        if ($inTable) { $html .= '<table class="br-table"><tbody>' . implode('', $buf) . '</tbody></table>'; $buf = []; $inTable = false; }
    };

    foreach ($lines as $line) {
        // Fenced code blocks
        if (preg_match('/^```/', $line)) {
            $flushList(); $flushTable();
            if (!$inCode) { $html .= '<pre><code>'; $inCode = true; }
            else          { $html .= '</code></pre>'; $inCode = false; }
            continue;
        }
        if ($inCode) { $html .= htmlspecialchars($line) . "\n"; continue; }

        // Headings
        if (preg_match('/^(#{1,4})\s+(.+)/', $line, $m)) {
            $flushList(); $flushTable();
            $lvl = strlen($m[1]);
            $id  = preg_replace('/[^a-z0-9]+/', '-', strtolower($m[2]));
            $html .= "<h{$lvl} id=\"{$id}\">{$m[2]}</h{$lvl}>\n";
            continue;
        }

        // Horizontal rule
        if (preg_match('/^---+$/', trim($line))) {
            $flushList(); $flushTable();
            $html .= "<hr>\n";
            continue;
        }

        // Table rows
        if (preg_match('/^\|/', trim($line))) {
            $flushList();
            $inTable = true;
            if (preg_match('/^\|[\s\-\|:]+\|$/', trim($line))) continue; // separator row
            $cells = array_map('trim', explode('|', trim($line, '| ')));
            $tag   = (count($buf) === 0) ? 'th' : 'td';
            $row   = '<tr>';
            foreach ($cells as $c) $row .= "<{$tag}>" . inlineMarkdown($c) . "</{$tag}>";
            $row  .= '</tr>';
            $buf[] = $row;
            continue;
        } else {
            $flushTable();
        }

        // List items
        if (preg_match('/^[-*]\s+(.+)/', $line, $m)) {
            $inList = true;
            $buf[]  = '<li>' . inlineMarkdown($m[1]) . '</li>';
            continue;
        } else {
            $flushList();
        }

        // Blank line
        if (trim($line) === '') { $html .= "<br>\n"; continue; }

        // Paragraph / inline
        $html .= '<p>' . inlineMarkdown($line) . "</p>\n";
    }

    $flushList(); $flushTable();
    if ($inCode) $html .= '</code></pre>';
    return $html;
}

function inlineMarkdown(string $text): string {
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    // Inline code
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Links [text](url)
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="?file=$2">$1</a>', $text);
    return $text;
}

// --- Router ---
$files = array_values(array_filter(
    array_map('basename', glob($rulesDir . '/*.md') ?: []),
    fn($f) => $f !== ''
));
sort($files);

$requested = $_GET['file'] ?? 'INDEX.md';
$filePath  = safeFile($rulesDir, $requested);

$content = $filePath ? renderMarkdown(file_get_contents($filePath)) : '<p style="color:red">File not found.</p>';
$title   = htmlspecialchars(str_replace(['.md', '-', '_'], ['',' ',' '], $requested));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Business Rules — <?= $title ?></title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 15px; color: #1a1a1a; background: #f6f8fa; display: flex; min-height: 100vh; }

  /* Sidebar */
  nav { width: 240px; min-width: 240px; background: #1e2a38; color: #c9d3de; padding: 0; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
  nav .logo { padding: 20px 16px 12px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: .08em; color: #7eb3e0; border-bottom: 1px solid #2d3e52; }
  nav a { display: block; padding: 9px 16px; color: #c9d3de; text-decoration: none; font-size: 13px; border-left: 3px solid transparent; transition: background .15s; }
  nav a:hover { background: #2d3e52; color: #fff; }
  nav a.active { background: #2d3e52; border-left-color: #7eb3e0; color: #fff; font-weight: 600; }

  /* Content */
  main { flex: 1; padding: 40px 48px; max-width: 960px; }
  main h1 { font-size: 26px; margin-bottom: 24px; color: #0d1117; border-bottom: 2px solid #e1e4e8; padding-bottom: 10px; }
  main h2 { font-size: 20px; margin: 32px 0 12px; color: #0d1117; border-bottom: 1px solid #e1e4e8; padding-bottom: 6px; }
  main h3 { font-size: 16px; margin: 24px 0 8px; color: #333; }
  main h4 { font-size: 14px; margin: 16px 0 6px; color: #555; }
  main p  { line-height: 1.7; margin-bottom: 10px; color: #333; }
  main hr { border: none; border-top: 1px solid #e1e4e8; margin: 24px 0; }
  main br { display: block; margin: 4px 0; }
  main ul { padding-left: 22px; margin-bottom: 14px; }
  main li { line-height: 1.7; margin-bottom: 4px; }
  main a  { color: #0969da; text-decoration: none; }
  main a:hover { text-decoration: underline; }

  pre { background: #f0f4f8; border: 1px solid #d0d7de; border-radius: 6px; padding: 16px; overflow-x: auto; margin: 16px 0; font-size: 13px; line-height: 1.6; }
  code { background: #f0f4f8; border-radius: 4px; padding: 2px 5px; font-size: 13px; font-family: 'SFMono-Regular', Consolas, monospace; }
  pre code { background: none; padding: 0; }

  .br-table { border-collapse: collapse; width: 100%; margin: 16px 0; font-size: 13px; }
  .br-table th, .br-table td { border: 1px solid #d0d7de; padding: 8px 12px; text-align: left; }
  .br-table th { background: #f0f4f8; font-weight: 600; }
  .br-table tr:nth-child(even) td { background: #f8fafc; }

  strong { font-weight: 600; }
  em     { font-style: italic; }
</style>
</head>
<body>
<nav>
  <div class="logo">Business Rules</div>
  <?php foreach ($files as $f):
    $label  = str_replace(['.md', 'BR-', '-', '_'], ['', '', ' ', ' '], $f);
    $active = ($f === $requested) ? ' active' : '';
  ?>
  <a href="?file=<?= urlencode($f) ?>"class="<?= $active ?>"><?= htmlspecialchars($label) ?></a>
  <?php endforeach; ?>
</nav>
<main>
  <?= $content ?>
</main>
</body>
</html>
