<?php
$users = array();
$class = $_GET['id'];
$registeredOnly = isset($_GET['registered']) ? $_GET['registered'] : 0;

require_once '../db.php';
$sql = "select user_id, last, first from users
        where class_id = " . $class;
if ($registeredOnly) $sql .= " and user_registered > 0";
$sql .= " order by last, first";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
    // in order for array not to be sorted by user_id the key must be a string
    $users[' ' . $row['user_id']] = $row['first'] . ' ' . $row['last'];
}

echo json_encode($users);
?>