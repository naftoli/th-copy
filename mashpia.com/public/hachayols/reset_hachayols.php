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
            aa.admin_id, u.user_id, u.dob, c.class_grade, u.hachayol
        FROM
            users u
                JOIN
            admin_auths aa ON aa.id = u.user_id
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            u.user_registered > 0
        ORDER BY admin_id , class_grade DESC";

$result = $mysqli->query($sql);
$info = $result->fetch_all(MYSQLI_ASSOC);

$data = [];
foreach ($info as $row) {
    $data[$row['admin_id']][] = [
        'user_id'   => $row['user_id'],
        'grade'     => $row['class_grade'],
        'hachayol'  => $row['hachayol']
    ];
}

echo "<pre>"; print_r($data); echo "</pre>";