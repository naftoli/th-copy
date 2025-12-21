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
    INSERT INTO mashpia_purchases.mivtzoim_items 
    SET
        item = :item,
        size = :size,
        yom_tov = 'Hei Teves',
        msrp = :msrp,
        sale = :sale,
        stock = :stock,
        thumb = :thumb,
        ord = :order
");

$update = $MASHPIA_DB->prepare("
    UPDATE mashpia_purchases.mivtzoim_items
    SET
        size = :size,
        msrp = :msrp,
        sale = :sale,
        stock = :stock,
        ord = :order
    WHERE mivtzoim_item_id = :id OR item = :item
");

if (isset($_FILES['items'])) {
    $MASHPIA_DB->beginTransaction();
    echo "starting...";
    $file = fopen($_FILES['items']['tmp_name'], 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        $i = 0;
        $id = $line[$i++];
        $item = $line[$i++];
        $size = $line[$i++];
        if ($size == 'NULL') $size = null;
        $yom_tov = $line[$i++];
        $msrp = $line[$i++];
        $sale = $line[$i++];
        $stock = $line[$i++];
        $image = $line[$i++];
        $order = $line[$i++];
        // if ($id) {
            if (! $update->execute([
                ':id' => $id,
                ':item' => $item,
                ':msrp' => $msrp,
                ':sale' => $sale,
                ':stock' => $stock,
                ':order' => $order, 
                ':size' => $size
            ])) {
                $MASHPIA_DB->rollback();
                echo "Error: " . $update->errorInfo()[2];
                exit;
            }
        // } else {
        //     if (! $stmt->execute([
        //         ':item' => $item,
        //         ':msrp' => $msrp,
        //         ':sale' => $sale,
        //         ':stock' => $stock,
        //         ':thumb' => $image,
        //         ':order' => $order,
        //         ':size' => $size
        //     ])) {
        //         $MASHPIA_DB->rollback();
        //         echo "Error: " . $stmt->errorInfo()[2];
        //         exit;
        //     }
        // }
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
