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
$year--;

$raised = [];
$sql = "select * from family_raised where year = $year order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $raised[$row['admin_id']] = $row['amount'];
}

$sweaters = [];
$sql = "select * from family_sweaters where year = $year order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $sweaters[$row['admin_id']][] = $row;
}

$admin_ids = array_unique(array_merge(array_keys($raised), array_keys($sweaters)), SORT_NUMERIC);

$children = [];
$sql = "select * from users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        join admin_auths aa on aa.id = u.user_id 
        where aa.admin_id in (" . implode(',', $admin_ids) . ") 
        and aa.auth = 'user' 
        and c.class_grade != '8' 
        and u.school_id not in (61, 269, 612)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['admin_id']][$row['user_id']] = $row;
}

$admins = [];
$sql = "select * from admins where admin_id in (" . implode(',', array_keys($children)) . ") order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[$row['admin_id']] = $row;
}

echo json_encode([
    'success'   => true,
    'raised'    => $raised,
    'sweaters'  => $sweaters,
    'admins'    => $admins,
    'admin_ids' => array_keys($admins),
    'children'  => $children
]);
?>