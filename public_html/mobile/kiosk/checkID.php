<?php
//ini_set('display_errors',1);
require "../../db.php";

$response = array();
$response['success'] = false;

$card_number = mysql_real_escape_string( $_POST['card'] );
// remove first number
$card_number = substr($card_number, 1);
$sql = "select * from users where user_code = " . $card_number;
$result = mysql_query( $sql );
if (mysql_num_rows($result)) {
    $row = mysql_fetch_assoc($result);
    if ($row['user_registered'] > 0) {
        $user_id = $row['user_id'];
        $response['success'] = true;
        $response['body'] = $user_id;
        
        // encrypt user id
        require '../reg/ajax/encrypt.php';
        $encrypted = encrypt_decrypt('encrypt', $user_id);
        if (!isset($_COOKIE['user'])) setcookie('user', $encrypted, 0, '/');
        if (!isset($_COOKIE['kiosk'])) setcookie('kiosk', 1, 0, '/');
    } else {
        $response['body'] = "You are not registered.";
    }
} else {
    $response['body'] = "No such ID Card in our system.";
}
echo json_encode($response);