<?
$users = array();
$class = $_POST['id'];

require_once '../db.php';
$sql = "select user_id, last, first from users where class_id = " . $class . " order by last, first";
//echo $sql;
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row['first'] . ' ' . $row['last'];
}

echo json_encode($users);
?>