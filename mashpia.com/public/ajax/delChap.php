<?php
require '../db.php';
$id = mysql_real_escape_string($_POST['id']);

if ($id > 0) {
    $sql = "delete from th_chidon_chaps where th_chidon_chap_id = " . $id;
    if (mysql_query($sql)) {
        echo 1;
        exit;
    } else {
        echo mysql_error();
        exit;
    }
}
echo 0;
exit;