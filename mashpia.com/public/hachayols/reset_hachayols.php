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
    $admins[$row['admin_id']][$row['user_id']] = $row['class_grade'];
}

// set hachayol to first child in family that is lower than 6th grade unless there is no child in the family that is lower than 6th grade
$i = 1;
foreach ($admins as $admin_id => $users) {
    $hachayol = null;
    foreach ($users as $user_id => $class_grade) {
        if (intval($class_grade) < 6 || !is_numeric($class_grade)) {
            $hachayol = $user_id;
            break;
        }
    }
    if ($hachayol === null) {
        // get first child in family
        $hachayol = first(array_keys($users));
    }
    $sql = "UPDATE users SET hachayol = 1 WHERE user_id = " . $hachayol;
    echo $i++ . ": " . $sql . "<br />";
//    $mysqli->query($sql);
}