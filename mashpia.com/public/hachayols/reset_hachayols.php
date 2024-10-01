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
            aa.admin_id, u.user_id, c.class_grade, u.hachayol, ur.reg_date  
        FROM
            users u
                JOIN
            admin_auths aa ON aa.id = u.user_id
                JOIN
            classes c ON c.class_id = u.class_id 
                LEFT JOIN 
            user_registration ur ON ur.user_id = u.user_id 
        WHERE
            u.user_registered IS NOT NULL
                AND u.school_id NOT IN (66 , 112) 
                AND ur.year = 5785 
        ORDER BY aa.admin_id , c.class_grade DESC";
$result = $mysqli->query($sql);
$info = $result->fetch_all(MYSQLI_ASSOC);

// organize data
$admins = [];
$non_registered = [];
foreach ($info as $row) {
    if ($row['reg_date']) $admins[$row['admin_id']][$row['user_id']] = $row['hachayol'];
    else $non_registered[$row['admin_id']][$row['user_id']] = $row['hachayol'];
}

// give hachayol to the oldest child that is in grade 5 or lower
// if there's no child in grade 5 or lower, give to the last child
$success = true;
$mysqli->begin_transaction();
$updated = 0;
foreach ($admins as $admin_id => $children) {
    echo 'Admin ID: ' . $admin_id . '<br>';
    echo 'Children: ' . print_r($children) . '<br>';
    $found = false;
    foreach ($children as $child_id => $hachayol) {
//        if ($grade <= 5 || $grade == 'Pre1a') {
//            $hachayol = $child_id;
//            break;
//        }
//        $hachayol = $child_id;
        // check if any child has hachayol
        if ($hachayol) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $child_id = reset($children);
        $sql = "UPDATE users SET hachayol = 1 WHERE user_id = $child_id";
//        echo $sql . '<br />';
        if ($mysqli->query($sql)) {
            $updated++;
        } else {
            $success = false;
            break 2;
        }
        // remove hachayol from other children
        foreach ($non_registered[$admin_id] as $child_id => $hachayol) {
            $sql = "UPDATE users SET hachayol = 0 WHERE user_id = $child_id";
            echo $sql . '<br />';
            if (!$mysqli->query($sql)) {
                $success = false;
                break 3;
            }
        }
    }
}
if ($success) {
    $mysqli->commit();
    echo 'Updated ' . $updated . ' records';
} else {
    $mysqli->rollback();
    echo 'Failed to update';
}
?>