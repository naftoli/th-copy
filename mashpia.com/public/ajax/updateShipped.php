<?
$id = $_POST['id'];
$checked = $_POST['checked'];
$field = $_POST['field'];
$table = $_POST['table'];
$key = $_POST['key'];

if ($table == 'rank_marks') {
    $info = explode(':', $id);
    $id = $info[0];
    $rank = $info[1];
}

if ($table == 'medal_marks') {
    $info = explode(':', $id);
    $id = $info[0];
    $subject = $info[1];
    $medal = $info[2];
}

require_once '../db.php';

$sql = "update $table 
        set $field = ";
if ($checked == 'true') 
    $sql .= "now() ";
else if ($checked == 'false')
    $sql .= "null "; 
$sql .= "where $key = $id";
if (isset($rank)) 
    $sql .= " and rank_ord = " . $rank;
if (isset($subject) && isset($medal)) {
    $sql .= " and subject_id = " . $subject . " and medal_ord = " . $medal;
}
//echo $sql;

if (mysql_query($sql)) 
    echo 1;
else 
    echo 0;
?>