<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

// make sure only super auth can view
if ( $admin_user['auth'] != 'super' ) {
    echo 'You are not authorized to view this page.';
    exit;
}

// get uploaded file as csv and parse
if ( isset( $_POST['submit'] ) ) {
    $shipped_date = '2024-10-06';

    $success = true;
    $MASHPIA_DB->beginTransaction();
    $stmt = $MASHPIA_DB->prepare("
        UPDATE medal_marks 
        SET date_shipped = ?, date_received = ? 
        WHERE medal_ord = ?
        AND subject_id = ?
        AND user_id = ?
      ");

    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    $first = true;
    $idx = 1;
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($first) {
            $first = false;
            continue;
        }
        $user_id = intval($row[1]);
        $subject_id = intval($row[2]);
        $medal_ord = intval($row[3]);
        $res = $stmt->execute([$shipped_date, $shipped_date, $medal_ord, $subject_id, $user_id]);
        if (!$res) {
            $success = false;
            break;
        }
    }
    if ($success) {
        $MASHPIA_DB->commit();
        echo "Successfully updated shipped date.";
    } else {
        $MASHPIA_DB->rollBack();
        echo "Failed to update shipped date.";
    }
    fclose($handle);
}

?>
<DOCTYPE html>
<html>
<head>
    <title>Fix Shipped Issue</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <!-- add ability to upload csv file -->
    <form action="fix_shipped_issue.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload" name="submit">
</body>
</html>