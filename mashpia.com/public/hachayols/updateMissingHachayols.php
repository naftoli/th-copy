<?php
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

// find first child that's registered for each admin that doesn't have hachayol marked
foreach ($admins as $children) {
    $first = $children[0];
    if ($first['hachayol'] == 0) {
        $user_id = $first['user_id'];
        $sql = "UPDATE users 
                SET 
                    hachayol = 1
                WHERE
                    user_id = :id";
        $result = $mysqli->prepare($sql);
        $result->execute(['id' => $user_id]);
    }
}
echo "done";
