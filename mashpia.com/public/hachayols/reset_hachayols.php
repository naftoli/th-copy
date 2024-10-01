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
            aa.admin_id, u.user_id, c.class_grade 
        FROM
            users u
                JOIN
            admin_auths aa ON aa.id = u.user_id
                JOIN
            classes c ON c.class_id = u.class_id 
        WHERE
            u.user_registered IS NOT NULL
                AND u.school_id NOT IN (66 , 112) 
        ORDER BY aa.admin_id , c.class_grade DESC";
$result = $mysqli->query($sql);
$info = $result->fetch_all(MYSQLI_ASSOC);

// organize data
$admins = [];
foreach ($info as $row) {
    $admins[$row['admin_id']][$row['user_id']] = $row['grade'];
}

// give hachayol to the oldest child that is in grade 5 or lower
// if there's no child in grade 5 or lower, give to the last child
foreach ($admins as $admin_id => $children) {
    echo 'Admin ID: ' . $admin_id . '<br>';
    echo 'Children: ' . print_r($children) . '<br>';
    $found = false;
    foreach ($children as $child_id => $grade) {
        if ($grade <= 5 || $grade == 'Pre1a') {
            $hachayol = $child_id;
            break;
        }
        $hachayol = $child_id;
    }
    $mysqli->query("UPDATE users SET hachayol = 1 WHERE user_id = $hachayol");
}