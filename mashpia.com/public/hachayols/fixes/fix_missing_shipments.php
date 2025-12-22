<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

if ($admin_user['auth'] != 'super') {
    echo 'No Permission';
    exit;
}

$stmtUsers = $MASHPIA_DB->prepare("
    SELECT user_id FROM hachayols_to_give h 
    JOIN admin_auths aa ON h.user_id = aa.id 
    WHERE aa.admin_id = :admin_id 
    AND aa.role_id = 1
    AND h.year = :year 
    ORDER BY h.user_id
");

$stmt = $MASHPIA_DB->prepare("
    INSERT IGNORE INTO th_chidon_shipping 
    (year, user_id, item_id, item_num, status, date_shipped, shipment_number) 
    VALUES (:year, :user, :item, :num, :status, :date, :shipment_number)
");

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, 'r');

    $first = true;
    $shipments = [];
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        if ($first) {
            $first = false;
            continue;
        }
        $admin_id = $data[0];
        $row_shipments = [];
        for ($i = 1; $i < 5; $i++) {
            if (intval($data[$i])) {
                $row_shipments[] = $i;
            }
        }
        $shipments[$admin_id][] = $row_shipments;
    }
    fclose($handle);

    // echo "<pre>"; print_r($shipments); echo "</pre>"; exit;

    $dates[1] = new DateTime('2025-09-16');
    $dates[2] = new DateTime('2025-09-16');
    $dates[3] = new DateTime('2025-10-21');
    $dates[4] = new DateTime('2025-10-31');

    $success = true;
    $MASHPIA_DB->beginTransaction();

    foreach ($shipments as $admin_id => $rows) {
        $stmtUsers->execute([
            'admin_id' => $admin_id,
            'year' => $year
        ]);
        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $index => $user) {
            if (!isset($rows[$index])) {
                continue;
            }
            $user_id = $user['user_id'];
            foreach ($rows[$index] as $shipment_number) {
                $res = $stmt->execute([
                    'year' => $year,
                    'user' => $user_id,
                    'item' => ('HACH0' . $shipment_number),
                    'num' => 0,
                    'status' => 1,
                    'date' => $dates[$shipment_number]->format('Y-m-d H:i:s'),
                    'shipment_number' => $shipment_number
                ]);
                if (!$res) {
                    $success = false;
                    break 3;
                }
            }
        }
    }

    if ($success) {
        $MASHPIA_DB->commit();
        echo 'Success';
    } else {
        $MASHPIA_DB->rollBack();
        echo 'Failed';
        $stmt->debugDumpParams();
        $MASHPIA_DB->errorInfo();
    }
}
?>
<!-- create form for uploading a csv file with user_id and shipment_number -->
<!DOCTYPE html>
<html>
    <head>
        <title>Fix Missing Shipments</title>
    </head>
    <body>
        <form action="fix_missing_shipments.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file" id="file">
            <input type="submit" value="Upload">
        </form>
    </body>
</html>