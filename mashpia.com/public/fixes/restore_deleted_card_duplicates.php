<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/utils/header.php';

checkAuth();
$db = getDbHandle();

$sql = "SHOW COLUMNS FROM mashpia_backup2.user_points";
$stmt = $db->query($sql);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$columns = array_column($columns, 'Field');

$sql = "
    SELECT 
        *
    FROM
        mashpia_backup2.user_points up1
            LEFT JOIN
        pointsDB.user_points up2 USING (user_point_id)
    WHERE
        up2.user_point_id IS NULL
";
$stmt = $db->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "INSERT INTO pointsDB.user_points (" . implode(',', $columns) . ") VALUES (:" . implode(',:', $columns) . ")";
$stmt = $db->prepare($sql);

$db->beginTransaction();
foreach ($rows as $row) {
    $res = $stmt->execute($row);
    if (!$res) {
        echo "Error inserting row: " . print_r($row, true) . "\n";
        $db->rollBack();
        break;
    }
}
$db->commit();
echo "Done";