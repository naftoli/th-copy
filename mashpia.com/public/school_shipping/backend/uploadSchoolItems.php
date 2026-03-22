<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

if (isset($_POST['submit'])) {
    $file_info = $_FILES['file'];
    $file = fopen($file_info['tmp_name'], 'r');
    $items = ['CHI601', 'CHI602', 'CHI603', 'CHI604', 'CHI605', 'CHI606', 'CHI607', 'CHI611', 'CHI612'];
    $items_info = [];
    while (($row = fgetcsv($file)) !== FALSE) {
        $school_id = $row[0];
        for ($i = 1; $i < count($row); $i++) {
            $qty = $row[$i];
            if ($qty) {
                $items_info[$school_id][$items[$i-1]] = $qty;
            }
        }
    }

    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO school_chidon_items (school_id, item_id, qty)
        VALUES (:school_id, :item_id, :qty)
    ");

    $MASHPIA_DB->beginTransaction();
    foreach ($items_info as $school_id => $items) {
        foreach ($items as $item_id => $qty) {
            $res = $stmt->execute(['school_id' => $school_id, 'item_id' => $item_id, 'qty' => $qty]);
            if (!$res) {
                $MASHPIA_DB->rollBack();
                echo "Error: " . $stmt->errorInfo()[2];
                exit;
            }
        }
    }
    $MASHPIA_DB->commit();
    echo "done.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload School Items</title>
</head>
<body>
    <form action="uploadSchoolItems.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload" name="submit">
    </form>
</body>
</html>