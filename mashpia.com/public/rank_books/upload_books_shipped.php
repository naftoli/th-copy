<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

if (isset($_FILES['file'])) {
    $stmt = $MASHPIA_DB->prepare("INSERT IGNORE INTO rank_books_shipped (user_id, book) VALUES (?, ?)");
    $stmtUsers = $MASHPIA_DB->prepare("SELECT user_serial, user_id FROM users WHERE user_serial in (?)");

    $file = $_FILES['file'];
    $file_path = $file['tmp_name'];
    $file = fopen($file_path, 'r');
    $total = 0;
    $info = [];
    while (($line = fgetcsv($file)) !== FALSE) {
        $info[$line[0]] = $line[1];
        $total++;
    }
    fclose($file);

    // make sure our total is same as info total
    $bookTotal = count($info);
    if ($bookTotal != $total) {
        echo "Error: corrupted file. Total books count does not match.'";
        exit;
    }

    // get user_ids
    $serials = array_keys($info);
    $resUsers = $stmtUsers->execute([$serials]);
    if (!$resUsers) {
        $success = false;
        echo 'Failed to get user_ids';
        exit;
    }
    $rows = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
    $user_ids = [];
    foreach ($rows as $row) {
        $user_ids[$row['user_serial']] = $row['user_id'];
    }

    $MASHPIA_DB->beginTransaction();
    $success = true;

    $error = '';
    $updated = 0;
    foreach ($info as $serial => $book) {
        $user_id = $user_ids[$serial];
        $res = $stmt->execute([$user_id, $book]);
        if (!$res) {
            $success = false;
            $error = 'Failed to update book #' . $book . ' for user ' . $user_id . '\nNo books were set as shipped.';
            break;
        }
        $updated++;
    }

    if ($success) {
        $MASHPIA_DB->commit();
        echo $updated . ' books were set as shipped.';
    } else {
        $MASHPIA_DB->rollBack();
        echo "Error setting books as shipped: " . $error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Books Shipped</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        tr, th, td {
          padding: 10px;
          font-size: 14px;
          border: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <!-- create a form to upload the file -->
    <form id="upload_form" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload">
    </form>
</body>
</html>