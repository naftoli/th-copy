<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO registration_charges 
    SET user_id = :user, 
    school_id = :school, 
    type = :type, 
    amount = :amount, 
    date = :date, 
    year = :year
");

// get school_id
$schoolStmt = $MASHPIA_DB->prepare("
    SELECT school_id 
    FROM users 
    WHERE user_id = :user
");

if (isset($_FILES['myshliach'])) {
    $success = true;
    $MASHPIA_DB->beginTransaction();
    $file = fopen($_FILES['myshliach']['tmp_name'], 'r');
    if ($file === false) {
        die('Failed to open file');
    }
    while (($data = fgetcsv($file)) !== false) {
        $admin_id = $data[0];
        $codes = explode(':', $data[1]);
        $user_id = $data[2];
        $date = $data[3];

        $dateInfo = explode('/', $date);
        $regDate = $dateInfo[2] . '-' . $dateInfo[0] . '-' . $dateInfo[1];

        $schoolStmt->execute(['user' => $user_id]);
        $school_id = $schoolStmt->fetchColumn();

        $res = $stmt->execute([
            'user'      => $user_id,
            'school'    => $school_id,
            'type'      => $codes[0],
            'amount'    => $codes[1],
            'date'      => $regDate,
            'year'      => $year,
        ]);
        if (!$res) {
            $success = false;
            break;
        }
    }
    if ($success) {
        $MASHPIA_DB->commit();
        echo 'Success';
    } else {
        $MASHPIA_DB->rollBack();
        echo 'Failed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add MyShliach Shipping</title>
</head>
<body>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="myshliach">
    <input type="submit" value="Submit">
</form>
</body>
