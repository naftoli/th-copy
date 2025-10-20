<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$sql = "SELECT * FROM hachayols_to_give where year = 5786";
$result = $MASHPIA_DB->query($sql);
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

// remove duplicates
$info = [];
foreach ($rows as $row) {
    $info[$row['user_id']][] = $row['hachayol_id'];
}

$duplicates = [];
foreach ($info as $user_id => $more) {
    $num = count($more);
    if ($num > 1) {
        for ($i = 0; $i < ($num - 1); $i++) {
            $duplicates[] = $more[$i];
        }
    }
}

foreach ($duplicates as $hachayol_id) {
    $sql = "DELETE FROM hachayols_to_give WHERE hachayol_id = " . $hachayol_id;
    $result = $MASHPIA_DB->query($sql);
}
echo "done";