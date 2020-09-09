<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$rows = [];
$sql = "select * from transactions where trans_id <= 24470 and description like 'Mivtza Lulav 5781%' order by trans_id desc";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
    $rows[] = $row;
}

foreach ($rows as $row) {
    $usersPos = strpos($row['description'], 'Users:');
    $admin = substr($row['description'], 39, (strpos($row['description'], ';') - 39));
    $users = substr($row['description'], ($usersPos + 6));
    $sql = "insert into lulav_purchases 
            set year = 5781, 
            admin_id = " . $admin . ", 
            users = '" . $users . "', 
            paid = " . $row['amount'] . ",
            authorization = '" . $row['response'] . "'";
    mysql_query( $sql );
}
echo "done";
