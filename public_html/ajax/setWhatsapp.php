<?php
require '../db.php';
$class_id = mysql_real_escape_string($_POST['class_id']);
$val = intval(mysql_real_escape_string($_POST['val']));

if ($class_id > 0) {
    $sql = "update classes set whatsapp = " . $val . " where class_id = " . $class_id;
    if (mysql_query($sql)) {
        echo 1;
        exit;
    }
}
echo 0;