<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] !== 'super') {
    die('You are not authorized to view this page.');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get uploaded csv file, parse file with following columns: admin_id amount
if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    $data = fgetcsv($handle, 1000, ",");
    $updated = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $admin_id = $data[0];
        $amount = floatval($data[1]);
        // update family credit
        // first get the total amount of family credits for the admin
        $stmt = $MASHPIA_DB->prepare("
            SELECT IFNULL(SUM(amount), 0) FROM registration_charges WHERE admin_id = :admin_id AND type = 'RRFAM' AND year = :year
        ");
        $stmt->execute([
            ':admin_id' => $admin_id,
            ':year' => $year
        ]);
        $total_amount = $stmt->fetchColumn();
        $amount -= floatval($total_amount);
        if ($amount <= 0) {
            continue;
        }
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO registration_charges 
            SET admin_id = :admin_id,
                amount = :amount,
                type = 'RRFAM',
                year = :year, 
                trans_id = 0
        ");
        if ($stmt->execute([
            ':admin_id' => $admin_id,
            ':amount' => $amount,
            ':year' => $year
        ])) {
            $updated++;
        }
    }
    echo "Updated $updated family credits.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Update Family Credits</title>
</head>
<body>
    <h1>Update Family Credits</h1>
    <!-- upload file with the following columns: admin_id amount -->
    <form action="updateFamilyCredits.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload">
    </form>
</body>
</html>