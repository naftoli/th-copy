<?
require '../../../db.php';

$id = mysql_real_escape_string($_POST['id']);
$size = mysql_real_escape_string($_POST['size']);

$sql = "update th_chidon
        set size = '" . $size . "'
        where th_chidon_id = " . intval($id);
if (mysql_query($sql)) {
    echo 0;
} else {
    echo 1;
}
?>