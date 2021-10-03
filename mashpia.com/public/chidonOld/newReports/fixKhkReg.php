<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$transactions = [];
$sql = "select * from transactions where description like '%khk%' and trans_date > '2021-08-01'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $transactions[] = $row;
}

$khk = [];
foreach ($transactions as $trans) {
    $users = $trans['users_registered'];
    $sql = "select user_id, class_grade from classes c 
            join users u using (class_id) 
            where user_id in ($users) 
            order by class_grade desc";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $user_id = $row['user_id'];
    $khk[] = $user_id;
}

$updated = 0;
foreach ($khk as $user_id) {
    $sql = "update th_chidon set khk_reg = 1 where user_id = $user_id and year = 5782";
    if (mysql_query($sql)) $updated++;
}

echo "updated: " . $updated;