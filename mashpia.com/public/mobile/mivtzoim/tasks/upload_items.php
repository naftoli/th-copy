<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to view this page.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$stmt = $MASHPIA_DB->prepare("
    INSERT INTO mivtzoim_items
    SET
        item = :item,
        yom_tov = 'Hei Teves',
        msrp = :msrp,
        sale = :sale,
        stock = :stock,
        thumb = :thumb
");

if (isset($_FILES['items'])) {
    $MASHPIA_DB->beginTransaction();
    echo "starting...";
    $file = fopen($_FILES['items']['tmp_name'], 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        $item = $line[0];
        $msrp = $line[1];
        $sale = $line[2];
        $stock = $line[3];
        $image = $line[4];
        if (! $stmt->execute([
            ':item' => $item,
            ':type' => 'Hei Teves',
            ':msrp' => $msrp,
            ':sale' => $sale,
            ':stock' => $stock,
            ':thumb' => $image
        ])) {
            $MASHPIA_DB->rollback();
            echo "Error: " . $stmt->errorInfo()[2];
            exit;
        }
    }
    $MASHPIA_DB->commit();
    echo "done.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Items</title>
</head>
<body>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="items" />
        <input type="submit" value="Upload" />
    </form>
</body>
</html>
