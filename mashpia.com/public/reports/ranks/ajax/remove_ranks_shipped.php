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
    $stmt = $MASHPIA_DB->prepare("DELETE FROM rank_medals_shipped where user_id = :user and rank_ord = :rank");

    $file = $_FILES['file'];

    // Validate file upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "Error uploading file: " . getUploadErrorMessage($file['error']);
        exit;
    }

    $file_path = $file['tmp_name'];
    if (!file_exists($file_path)) {
        echo "Error: Uploaded file not found.";
        exit;
    }

    $fileHandle = fopen($file_path, 'r');
    if ($fileHandle === false) {
        echo "Error: Unable to open uploaded file.";
        exit;
    }

    $total = 0;
    $info = [];
    while (($line = fgetcsv($fileHandle)) !== FALSE) {
        if (count($line) < 2) {
            echo "Error: Invalid CSV format. Each row must have at least 2 columns.";
            fclose($fileHandle);
            exit;
        }
        $info[$line[0]][] = $line[1];
        $total++;
    }
    fclose($fileHandle);

    // update ranks
    try {
        $MASHPIA_DB->beginTransaction();
        $updated = 0;

        foreach ($info as $user_id => $ranks) {
            foreach ($ranks as $rank) {
                if (!$stmt->execute([
                    'user'  => $user_id,
                    'rank'  => $rank
                ])) {
                    throw new Exception('Failed to delete rank #' . htmlspecialchars($rank) . ' for user ' . $user_id);
                }
                $updated++;
            }
        }

        $MASHPIA_DB->commit();
        echo $updated . ' ranks were deleted.';

    } catch (Exception $e) {
        $MASHPIA_DB->rollBack();
        echo "Error deleting ranks: " . htmlspecialchars($e->getMessage());
    }
}

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension";
        default:
            return "Unknown upload error";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Remove Ranks Shipped</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        padding: 20px;
      }
      form {
        margin: 20px 0;
      }
      tr, th, td {
        padding: 10px;
        font-size: 14px;
        border: 1px solid #f0f0f0;
      }
      .error {
        color: red;
        margin: 10px 0;
      }
    </style>
</head>
<body>
<h1>Remove Ranks Shipped</h1>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" id="file" accept=".csv" required>
    <input type="submit" value="Upload">
</form>
</body>
</html>