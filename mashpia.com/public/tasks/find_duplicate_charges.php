<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$sql = "select * from registration_charges where year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute(['year' => $year]);

$charges = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $charges[$row['user_id']][$row['type']][] = $row;
}

$extras = [];
$others = [];
foreach ($charges as $user_id => $details) {
    foreach ($details as $type => $more) {
        foreach ($more as $idx => $row) {
            if ($idx > 0) {
                if ($type == 'LDE') $extras[] = $row;
                else $others[] = $row;
            }
        }
    }
}
echo count($extras) . "<br />" . count($others) . "<br />";

$stmt = $MASHPIA_DB->prepare("delete from registration_charges where registration_charge_id = :id");
foreach ($extras as $row) {
    $stmt->execute(['id' => $row['registration_charge_id']]);
}
echo "done";

echo "<pre>"; print_r($others); echo "</pre>";