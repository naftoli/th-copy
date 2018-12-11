<?php
require 'db.php';

$info = array();
$extra = array();
$sql = "select * from wp.wp_posts where post_type = 'birthday'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (count($info[$row['post_title']][$row['post_date']])) $extra[] = $row['ID'];
    $info[$row['post_title']][$row['post_date']][] = $row;
}
echo "<pre>";
//print_r($info);
//print_r($extra);
echo "</pre>";

mysql_query('set autocommit = 0');
foreach ($extra as $post_id) {
    $sql1 = "delete from wp.wp_postmeta where post_id = " . $post_id;
    $sql2 = "delete from wp.wp_posts where ID = " . $post_id;
    mysql_query('begin');
    if (mysql_query($sql1) && mysql_query($sql2)) {
        mysql_query('commit');
        echo $post_id . ' has been deleted.<br />';
    } else {
        mysql_query('rollback');
        echo mysql_error() . "<br />";
    }
}

