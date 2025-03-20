<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$prizes = [];
$sql = "select * from chidon_prizes where prize_id >= 452 and prize_id <= 481";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}

foreach ($prizes as $prize) {
    $sql = "update chidon_prizes set prize_picture = '" . $prize['prize_picture'] . "' where year = 5786 and prize_name = '" . $prize['prize_name'] . "'";
    if (! mysql_query($sql)) {
        echo "Error: " . mysql_error() . "<br />" . $sql . "<br /><br />";
    }
}

echo "Done.";
?>