<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// get all ranks
$sql = "SELECT * FROM ranks";
$result = mysql_query($sql);
$ranks = [];
while ($row = mysql_fetch_assoc($result)) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}

echo json_encode([
    'success'   => true,
    'ranks'     => $ranks
]);