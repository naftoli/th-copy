<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

if ($admin_user['auth'] != 'super') {
    die('You do not have permission to run this script.');
}

$sql = "SELECT 
            *, COUNT(*) AS total
        FROM
            th_chidon_shipping
        WHERE
            year = :year
        GROUP BY year , user_id , item_id , item_num
        HAVING total > 1";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute(['year' => $year]);
$doubles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtSelect = $MASHPIA_DB->prepare("SELECT * FROM th_chidon_shipping WHERE year = :year AND user_id = :user AND item_id = :item AND item_num = :num");

$qrys = [];
foreach ($doubles as $double) {
    $stmtSelect->execute([
        'year'      => $year,
        'user'      => $double['user_id'],
        'item'      => $double['item_id'],
        'num'       => $double['item_num'],
    ]);
    $rows = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
    $numRows = count($rows);
    foreach ($rows as $index => $row) {
        if ($index == ($numRows - 1)) continue;
        $qrys[] = "DELETE FROM th_chidon_shipping WHERE th_chidon_shipping_id = " . $row['th_chidon_shipping_id'];
    }
}

foreach ($qrys as $qry) {
    $MASHPIA_DB->exec($qry);
}
echo "Done.";