<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('No Permission');
}

$stmt = $MASHPIA_DB->prepare("
    UPDATE admins 
    SET admin_address1 = :address1,
        admin_address2 = :address2,
        admin_city = :city,
        admin_state = :state,
        admin_postal = :zip, 
        admin_country = :country
    WHERE admin_id = :id
");

if (isset($_POST['submit'])) {
    $success = true;
    $MASHPIA_DB->beginTransaction();
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, 'r');
    $data = fgetcsv($handle, 1000, ',');
    $updated = 0;
    $i = 0;
    $fields = ['admin_id', 'address1', 'address2', 'city', 'state', 'zip', 'country'];
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        for ($i = 0; $i < count($fields); $i++) {
            $val = $data[$i];
            if ($val == 'nan') $val = '';
            $field = $fields[$i];
            $$field = $val;
        }
        if (!$stmt->execute([
            ':address1' => $address1,
            ':address2' => $address2,
            ':city' => $city,
            ':state' => $state,
            ':zip' => $zip,
            ':country' => $country,
            ':id' => $admin_id
        ])) {
            $success = false;
            break;
        } else {
            $updated++;
        }
        $stmt->debugDumpParams();
        echo "<br /><br />";
    }
    fclose($handle);
    if ($success) {
        $MASHPIA_DB->commit();
        echo "<h1>Updated $updated addresses</h1>";
    } else {
        $MASHPIA_DB->rollBack();
        echo "<h1>Error updating addresses</h1>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Fix Admin Addresses</title>
</head>
<!-- add ability to upload file -->
<body>
<h1>Fix Admin Addresses</h1>
<form action='updateAdminInfo.php' method='post' enctype='multipart/form-data'>
    <input type='file' name='file' id='file'>
    <input type='submit' name="submit" value='Upload'>
</form>
</body>
