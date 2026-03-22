<?php
// upload file that contains user id and rank ord and set as shipped
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$stmt = $MASHPIA_DB->prepare("INSERT IGNORE INTO rank_medals_shipped (user_id, rank_ord) VALUES (?, ?)");

if (isset($_FILES['file']['tmp_name'])) {
    $MASHPIA_DB->beginTransaction();
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $res = $stmt->execute([$data[0], $data[1]]);
        if (!$res) {
            $MASHPIA_DB->rollBack();
            $stmt->debugDumpParams();
            die('Failed to insert all records');
        }
    }
    fclose($handle);
    $MASHPIA_DB->commit();
    echo 'Done';
}
?>
<!DOCTYPE html>
<html>
<body>
    <form method="post" action="setRanksShipped.php" enctype="multipart/form-data">
        <input type="file" name="file" />
        <input type="submit" value="Upload" />
    </form>
</body>