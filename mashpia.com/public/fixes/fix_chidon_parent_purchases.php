<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$info = [];
$sql = "select * from th_chidon_parent_purchases";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
foreach ($info as $row) {
    $id = $row['th_chidon_parent_purchase_id'];
    $desc = $row['authorize_desc'];
    $details = explode(':', $desc);
    $sql = "update th_chidon_parent_purchases 
            set authorize_id = '" . $details[0] . "' 
            where th_chidon_parent_purchase_id = " . $id;
//    echo $sql . "<br />";
    mysql_query($sql);
}
echo 'done.';