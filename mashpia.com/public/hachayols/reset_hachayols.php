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
            aa.admin_id, u.user_id, u.hachayol, c.class_grade
        FROM
            users u
                JOIN
            schools s USING (school_id)
                JOIN
            admin_auths aa ON aa.id = u.user_id
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            u.user_registered IS NOT NULL 
            and u.school_id not in (66, 112) 
        ORDER BY aa.admin_id , c.class_grade DESC 
	";
$result = $mysqli->query($sql);
$info = $result->fetch_all(MYSQLI_ASSOC);

// organize data
$admins = [];
foreach ($info as $row) {
    $admins[$row['admin_id']][] = $row;
}

// find all family accounts with no hachayol set
$missing = [];
foreach ($admins as $admin_id => $children) {
    $found = false;
    foreach ($children as $user) {
        if ($user['hachayol'] == 1) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $missing[] = $admin_id;
    }
}

// show missing hachayols
echo "Number of Admins without any Hachayols: " . count($missing) . "<br>";
echo "<pre>"; print_r($missing); echo "</pre>";
