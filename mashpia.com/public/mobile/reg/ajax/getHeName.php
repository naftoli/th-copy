<?php
$admin_auth = ['user'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/db.php";

$sql = "select first_he, last_he from users where user_id = " . mysql_real_escape_string($_POST['user_id']);
$result = mysql_query($sql);
if ($result) {
    $row = mysql_fetch_assoc($result);
    $name = $row['first_he'] . ' ' . $row['last_he'];
    if (!empty(trim($name))) {
        echo json_encode([
            'success' => true,
            'heName' => $name
        ]);
        exit;
    }
}
echo json_encode([
    'success'   => false
]);