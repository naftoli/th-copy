<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "no permission.";
    exit;
}
function getOldestChild(array $users) {
    $oldest = 0;
    foreach ($users as $user) {
        if ($oldest == 0) $oldest = $user;
        else {
            $first = new DateTime($user['dob']);
            $second = new DateTime($oldest['dob']);
            if ($first > $second) $oldest = $user;
        }
    }
    return $oldest['user_id'];
}

$admins = [];
$sql = "SELECT 
            admin_id
        FROM
            admin_auths aa
                JOIN
            users u ON u.user_id = aa.id
        WHERE
            admin_id NOT IN (SELECT 
                    admin_id
                FROM
                    admin_auths aa
                        JOIN
                    users u ON u.user_id = aa.id
                WHERE
                    u.hachayol = 1
                GROUP BY admin_id)
                AND u.user_registered > 0
        GROUP BY admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[] = $row['admin_id'];
}

$children = [];
foreach ($admins as $admin_id) {
    $sql = "SELECT 
                user_id, dob, hachayol
            FROM
                users u
                    JOIN
                admin_auths aa ON aa.id = u.user_id
            WHERE
                admin_id = $admin_id
                    AND u.user_registered > 0";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $children[$admin_id][] = $row;
    }
}

$qrys = [];
foreach ($children as $admin_id => $users) {
    $oldest = getOldestChild($users);
    $sql = "update users set hachayol = 1 where user_id = " . $oldest;
    $qrys[] = $sql;
}

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');
foreach ($qrys as $sql) {
//    echo $sql . "<br />";
    if (! mysql_query($sql)) {
        echo mysql_error();
        $success = false;
        break;
    }
}
if ($success) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');
echo "done.";