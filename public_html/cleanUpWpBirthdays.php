<?php
require 'db.php';
mysql_select_db('wp');

$ids = array();
$sql = "select * from wp_posts wp 
        where wp.post_type = 'birthday'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ids[] = $row['ID'];
}

$missing = array();
foreach ($ids as $id) {
    $sql = "select * from wp_postmeta where meta_key = 'age' and post_id = " . $id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) == 0) {
        $missing[] = $id;
    }
}

$sql = "delete from wp_postmeta where post_id in (" . implode(',', $missing) . ")";
mysql_query($sql);
$sql = "delete from wp_posts where ID in (" . implode(',', $missing) . ")";
mysql_query($sql);