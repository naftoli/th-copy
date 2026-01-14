<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function normalize_delimiter($d) {
    $d = (string)$d;
    if ($d === 'tab' || $d === '\\t' || $d === '\t') return "\t";
    if ($d === 'comma' || $d === ',') return ",";
    if ($d === 'pipe' || $d === '|') return "|";
    if ($d === 'semicolon' || $d === ';') return ";";
    return $d !== '' ? $d[0] : "\t";
}

$error = null;
$success = true;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $has_header = isset($_POST['has_header']) && $_POST['has_header'] === '1';
    $delimiter = normalize_delimiter($_POST['delimiter'] ?? "\t");
    $max_lines = 20000;
    $preview_lines = 200;

    if (!isset($_FILES['tsv']) || !is_array($_FILES['tsv'])) {
        $error = 'No file uploaded.';
    } else if (($_FILES['tsv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload error code: ' . h($_FILES['tsv']['error']);
    } else if (!is_uploaded_file($_FILES['tsv']['tmp_name'])) {
        $error = 'Upload failed.';
    } else if (($_FILES['tsv']['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'File too large (max 5MB).';
    } else {
        $tmp = $_FILES['tsv']['tmp_name'];
        $name = $_FILES['tsv']['name'] ?? 'uploaded.tsv';

        $handle = fopen($tmp, 'r');
        if (!$handle) {
            $error = 'Could not open uploaded file.';
        } else {
            $rows = [];
            $headers = null;
            $line_num = 0;
            $col_count = null;
            $warnings = [];

            $success = true;
            $MASHPIA_DB->beginTransaction();
            $stmtAll = $MASHPIA_DB->prepare("UPDATE date_tasks SET 
                        streak_id = :streak_id,
                        streak_show = :streak_show, 
                        streak_duch_cat = :streak_cat,
                        streak_duch_name = :streak_name
                        WHERE grid_id = :grid_id");
            $stmtSingle = $MASHPIA_DB->prepare("UPDATE date_tasks SET 
                        streak_id = :streak_id,
                        streak_show = :streak_show
                        WHERE grid_id = :grid_id");

            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                if ($has_header) {
                    $has_header = false;
                    continue;
                }

                $line_num++;
                $grid_id = intval($data[0]);
                $streak_id = intval($data[1]);
                $streak_show = intval($data[2]);
                $streak_cat = $data[3];
                $streak_name = $data[4];

                if ($streak_cat && $streak_name) {
                    $res = $stmtAll->execute([
                        'streak_id' => $streak_id,
                        'streak_show' => $streak_show,
                        'streak_cat' => $streak_cat,
                        'streak_name' => $streak_name,
                        'grid_id' => $grid_id
                    ]);
                } else {
                    $res = $stmtSingle->execute([
                        'streak_id' => $streak_id,
                        'streak_show' => $streak_show,
                        'grid_id' => $grid_id
                    ]);
                }
                if (!$res) {
                    $success = false;
                    $stmtAll->debugDumpParams();
                    $stmtSingle->debugDumpParams();
                    break;
                }
                echo "Updating grid ID: $grid_id<br />";
            }

            if ($success) {
                $MASHPIA_DB->commit();
                $msg = 'Streaks updated successfully.';
            } else {
                $MASHPIA_DB->rollBack();
                $msg = 'Error updating streaks: ' . $stmt->errorInfo()[2];
            }
        }
    }
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Upload TSV - Parse Lines</title>
    <style>
      body { font-family: Arial, sans-serif; padding: 16px; }
      .box { border: 1px solid #ddd; padding: 12px; border-radius: 6px; margin-bottom: 12px; }
      .error { background: #ffecec; border-color: #f5c2c7; }
      .warn { background: #fff8e1; border-color: #ffe08a; }
      table { border-collapse: collapse; width: 100%; }
      th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; font-size: 12px; }
      th { background: #f6f6f6; text-align: left; }
      code { background: #f6f6f6; padding: 2px 4px; border-radius: 4px; }
    </style>
  </head>
  <body>
    <h2>TSV Uploader (Parse each line)</h2>

    <?php if ($error) { ?>
      <div class="box error"><b>Error:</b> <?= h($error) ?></div>
    <?php } else if (isset($msg)) { ?>
      <div class="box success"><b>Success:</b> <?= h($msg) ?></div>
    <?php } ?>
    <div class="box">
      <form method="post" enctype="multipart/form-data">
        <div style="margin-bottom: 10px;">
          <label><b>TSV file</b></label><br/>
          <input type="file" name="tsv" accept=".tsv,text/tab-separated-values,text/plain" required />
        </div>

        <div style="margin-bottom: 10px;">
          <label><b>Delimiter</b></label><br/>
          <input type="text" name="delimiter" value="<?= h($_POST['delimiter'] ?? 'tab') ?>" style="width: 220px;" />
          <div style="font-size: 12px; color: #666; margin-top: 4px;">
            Use <code>tab</code> (default), <code>,</code>, <code>|</code>, <code>;</code>, or a single character.
          </div>
        </div>

        <div style="margin-bottom: 10px;">
          <label>
            <input type="checkbox" name="has_header" value="1" <?= (isset($_POST['has_header']) && $_POST['has_header'] === '1') ? 'checked' : '' ?> />
            <b>First row is header</b>
          </label>
        </div>

        <button type="submit">Upload + Parse</button>
      </form>
    </div>
  </body>
</html>
