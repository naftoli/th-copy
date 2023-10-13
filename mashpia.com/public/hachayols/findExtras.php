<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require '../header.php';
require '../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo 'No Permission';
    exit;
}

$sql = "SELECT 
            admin_id, user_id, hachayol
        FROM
            admin_auths aa
                JOIN
            users u ON u.user_id = aa.id
        WHERE
            u.user_registered > 0
        ORDER BY admin_id , hachayol DESC";
$result = $mysqli->query($sql);
$info = $result->fetch_all(MYSQLI_ASSOC);

// organize data
$admins = [];
foreach ($info as $row) {
    $admins[$row['admin_id']][] = $row;
}

// get all children that paid for extra hachayols
$sql = "select user_id from registration_charges where type like '%HACH%'";
$result = $mysqli->query($sql);
$paid = $result->fetch_all(MYSQLI_ASSOC);

// find all extras that didn't pay
$extras = [];
foreach ($admins as $admin_id => $children) {
    $num = count($children);
    if ($num > 1) {
        for ($i = 1; $i < $num; $i++) {
            $child = $children[$i];
            if ($child['hachayol'] == 1 && !in_array($children[$i]['user_id'], $paid)) {
                $extras[] = $children[$i]['user_id'];
            }
        }
    }
}

// update extras to have their hachayol flag set to 0
foreach ($extras as $user_id) {
    $sql = "UPDATE users 
            SET 
                hachayol = 0
            WHERE
                user_id = ?";
    $result = $mysqli->prepare($sql);
    $result->bind_param("i", $user_id);
    $result->execute();
}

echo "done";
