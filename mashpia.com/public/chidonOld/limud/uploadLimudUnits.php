<?php
// uploadLimudUnits.php
// Upload a CSV with columns: day, book 1 units, book 2 units, book 3 units, book 4 units, book 5 units
// Generate SQL INSERT statements for table limud_book_units(day, book, unit)

// Optional: enforce admin auth consistent with site conventions
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

function getJulianDate($heDate) {
    global $year;
    $params = explode(',', $heDate);
    $dd = intval($params[0]);
    $mm = intval($params[1]);
    $yy = intval($year);
    if ($mm == 12) {
        $yy--;
        $mm++;
    }
    $jd = jewishtojd($mm, $dd, $yy);
    return getGregorianFromJd($jd);
}

function getGregorianFromJd($jd) {
    $gregorian = jdtogregorian($jd);
    // return mm/dd/yyyy
    $gregorian = explode('/', $gregorian);
    return $gregorian[2] . '-' . $gregorian[0] . '-' . $gregorian[1];
}

// Helper: expand a string of units into an array of integers
function expand_units($str) {
    $units = [];
    if ($str === null) return $units;
    // Normalize whitespace and dash varieties
    $s = trim($str);
    if ($s === '') return $units;
    $s = str_replace(["\xE2\x80\x93", "\xE2\x80\x94", "–", "—"], '-', $s); // en/em dashes
    // Split by comma or semicolon
    $parts = preg_split('/\s*[;,]\s*/', $s);
    foreach ($parts as $p) {
        if ($p === '') continue;
        // Remove extra spaces around dash
        $p = preg_replace('/\s*-\s*/', '-', trim($p));
        if (preg_match('/^(\d+)-(\d+)$/', $p, $m)) {
            $start = intval($m[1]);
            $end = intval($m[2]);
            if ($start <= $end) {
                for ($i = $start; $i <= $end; $i++) $units[] = $i;
            } else {
                // If reversed range, still expand inclusively descending
                for ($i = $start; $i >= $end; $i--) $units[] = $i;
            }
        } elseif (preg_match('/^\d+$/', $p)) {
            $units[] = intval($p);
        } else {
            // Try to catch formats like "6 -9" or stray text; extract all numbers
            if (preg_match_all('/\d+/', $p, $nums) && count($nums[0]) > 0) {
                $nums = array_map('intval', $nums[0]);
                if (count($nums) === 2 && strpos($p, '-') !== false) {
                    $start = $nums[0];
                    $end = $nums[1];
                    if ($start <= $end) {
                        for ($i = $start; $i <= $end; $i++) $units[] = $i;
                    } else {
                        for ($i = $start; $i >= $end; $i--) $units[] = $i;
                    }
                } else {
                    foreach ($nums as $n) $units[] = $n;
                }
            }
        }
    }
    // De-duplicate and sort ascending for cleanliness
    $units = array_values(array_unique($units));
    sort($units, SORT_NUMERIC);
    return $units;
}

// Process upload if submitted
$queries = [];
$errors = [];
$processed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv'])) {
    if (!is_uploaded_file($_FILES['csv']['tmp_name'])) {
        $errors[] = 'File upload failed.';
    } else {
        $hasHeader = isset($_POST['has_header']);
        $fh = fopen($_FILES['csv']['tmp_name'], 'r');
        if ($fh === false) {
            $errors[] = 'Unable to open uploaded file.';
        } else {
            $day = 1;
            $rowIndex = 0;
            while (($row = fgetcsv($fh)) !== false) {
                // Skip empty rows
                if ($row === null || count($row) === 0) continue;
                // Optional header
                if ($rowIndex === 0 && $hasHeader) {
                    $rowIndex++;
                    continue;
                }
                // Expect at least 6 columns: date, book1..book5
                // If there are more, ignore extras; if fewer, pad with empties
                for ($i = count($row); $i < 6; $i++) $row[$i] = '';

                $dateRaw = trim((string)$row[0]);
                if ($dateRaw === '') {
                    $rowIndex++;
                    continue; // skip rows without a date
                }
                $date = getJulianDate($dateRaw);

                for ($book = 1; $book <= 5; $book++) {
                    $cell = isset($row[$book]) ? trim((string)$row[$book]) : '';
                    if ($cell === '') continue;
                    $unitList = expand_units($cell);
                    foreach ($unitList as $unit) {
                        // Compose SQL; assume integers for book and unit
                        $queries[] = sprintf('INSERT INTO limud_book_units (day, date, book, unit, year) 
                                VALUES (%d, %s, %d, %d, %d)', $day, $date, $book, intval($unit), $year);
                    }
                }
                $day++;
                $rowIndex++;
            }
            fclose($fh);
            $processed = true;
        }
    }
}

// Simple HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Limud Units CSV → SQL Generator</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; color: #222; }
        h1 { font-size: 22px; margin: 0 0 16px; }
        form { border: 1px solid #ddd; padding: 16px; border-radius: 8px; background: #fafafa; }
        .row { margin: 10px 0; }
        .errors { color: #a00; margin: 12px 0; }
        textarea { width: 100%; height: 360px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; }
        .actions { display: flex; gap: 8px; align-items: center; }
        button, .btn { background: #0b5; color: #fff; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
        button:hover, .btn:hover { background: #094; }
        .muted { color: #555; font-size: 13px; }
        .note { font-size: 12px; color: #333; }
        .card { border: 1px solid #eee; border-radius: 8px; padding: 12px; margin-top: 16px; background: #fff; }
        label { display: inline-flex; align-items: center; gap: 6px; }
    </style>
    <script>
        // No download functionality; SQL is displayed on screen below.
    </script>
    <!-- Expected CSV columns: day, book 1 units, book 2 units, book 3 units, book 4 units, book 5 units -->
    <!-- Unit cells can include ranges like "6 -9", commas (1,2,5), or semicolons; ranges are expanded to individual inserts. -->
</head>
<body>
    <h1>Limud Units CSV → SQL</h1>
    <div class="card">
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <label>
                    <strong>CSV file</strong>
                    <input type="file" name="csv" accept=".csv,text/csv" required />
                </label>
            </div>
            <div class="row">
                <label>
                    <input type="checkbox" name="has_header" checked />
                    First row is a header
                </label>
            </div>
            <div class="row">
                <label>
                    <input type="checkbox" name="run_inserts" />
                    Run inserts into DB now (transactional)
                </label>
            </div>
            <div class="row actions">
                <button type="submit">Generate SQL</button>
                <span class="muted">Table: <code>limud_book_units</code></span>
            </div>
            <div class="row note">
                Expected columns: <code>date</code>, <code>book 1 units</code>, <code>book 2 units</code>, <code>book 3 units</code>, <code>book 4 units</code>, <code>book 5 units</code>.
                Unit cells may include ranges like <code>6 -9</code>; these will be expanded into individual inserts.
            </div>
        </form>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($processed): ?>
        <?php
            $ran = false; $runSuccess = null; $runError = '';
            if (isset($_POST['run_inserts'])) {
                $ran = true; $runSuccess = true;
                // Start transaction
                @mysql_query('set autocommit=0');
                @mysql_query('begin');
                foreach ($queries as $q) {
                    if (!@mysql_query($q)) {
                        $runSuccess = false;
                        $runError = mysql_error();
                        break;
                    }
                }
                if ($runSuccess) {
                    @mysql_query('commit');
                    @mysql_query('set autocommit=1');
                } else {
                    @mysql_query('rollback');
                    @mysql_query('set autocommit=1');
                }
            }
        ?>
        <div class="card">
            <p><strong><?php echo number_format(count($queries)); ?></strong> SQL statements generated.</p>
            <?php if ($ran): ?>
                <?php if ($runSuccess): ?>
                    <p style="color:#0a0;"><strong>Success:</strong> Inserts executed and committed.</p>
                <?php else: ?>
                    <p style="color:#a00;"><strong>Error running inserts:</strong> <?php echo htmlspecialchars($runError); ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <pre style="white-space: pre; overflow:auto; max-height: 60vh; background:#f7f7f7; border:1px solid #eee; padding:10px;"><?php echo htmlspecialchars(implode(";\n", $queries) . (count($queries) ? ";\n" : "")); ?></pre>
        </div>
    <?php endif; ?>
</body>
</html>