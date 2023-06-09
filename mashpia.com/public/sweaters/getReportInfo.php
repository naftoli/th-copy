<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        'success' => false,
        'message' => 'No access'
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$raised = [];
$sql = "select * from family_raised where year = $year order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $raised[$row['admin_id']] = $row['amount'];
}

$sweaters = [];
$sql = "select fs.*, u.first, u.user_serial from family_sweaters fs join users u using (user_id) where year = $year order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $sweaters[$row['admin_id']][] = $row;
}

$admins = [];
$sql = "select * from admins where admin_id in (" . implode(',', array_keys($raised)) . ") order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[$row['admin_id']] = $row;
}

echo json_encode([
    'success'   => true,
    'raised'    => $raised,
    'sweaters'  => $sweaters,
    'admins'    => $admins,
    'admin_ids' => array_keys($admins)
]);
?>