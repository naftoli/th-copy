<?php
require '../db.php';
$id = mysql_real_escape_string($_POST['id']);

$sql = "update th_chidon set deleted = 1 where th_chidon_id = " . $id;
if (mysql_query($sql)) {
    echo 0;
} else {
    echo $sql;
}