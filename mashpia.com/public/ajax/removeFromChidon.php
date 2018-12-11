<?php
require '../db.php';
$id = mysql_real_escape_string($_POST['id']);

$sql = "delete from th_chidon where th_chidon_id = " . $id;
if (mysql_query($sql)) {
    echo 0;
} else {
    echo $sql;
}