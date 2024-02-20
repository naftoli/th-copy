<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] !== 'super') {
    die('You are not authorized to view this page.');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get uploaded csv file, parse file with following columns: id, type, amount
if (isset($_FILES['file'])) {
    // parse cvs
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    $data = fgetcsv($handle, 1000, ",");
    $updated = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $id = $data[0];
        $amount = $data[1];
        // update family balance
        $stmt = $MASHPIA_DB->prepare("
            UPDATE th_chidon 
            SET prepaid_credit = :amount 
            WHERE user_id = :id 
            AND year = :year
        ");
        if ($stmt->execute([
            ':amount'   => $amount,
            ':id'       => $id,
            ':year'     => $year
        ])) {
            $updated++;
        } else {
            $stmt->errorInfo();
            $stmt->debugDumpParams();
            break;
        }
    }
    echo "Updated $updated registration charges.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fix Family Balances</title>
</head>
<!-- add ability to upload file -->
<body>
<form action="fix_personal_credits.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload">
</form>
</body>