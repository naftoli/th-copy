<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school');
require('../../header.php');
require('./shared.php');
require_once('../../class.globalSettings.php');

$prize_name = isset($_POST['prize_name']) ? mysql_real_escape_string($_POST['prize_name']) : "";
$quantity = isset($_POST['quantity']) ? mysql_real_escape_string($_POST['quantity']) : "0";
$made_possible_by = isset($_POST['made_possible_by']) ? mysql_real_escape_string($_POST['made_possible_by']) : "null";
$personalization = isset($_POST['personalization']) ? mysql_real_escape_string($_POST['personalization']) : "null";
$color = isset($_POST['color']) ? mysql_real_escape_string($_POST['color']) : "null";
$size = isset($_POST['size']) ? mysql_real_escape_string($_POST['size']) : "null";
$note = isset($_POST['note']) ? mysql_real_escape_string($_POST['note']) : "null";
$price = isset($_POST['price']) ? mysql_real_escape_string($_POST['price']) : "null";
$our_price = isset($_POST['our_price']) ? mysql_real_escape_string($_POST['our_price']) : "null";
$year = GlobalSettings::getChidonRegYear();

$prize_picture = "";
switch($_FILES['prize_picture']) {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
        break;
    default: // if an image was uploaded succesfully save it
        $prize_picture = save_image($_FILES['prize_picture'], "/chidonOld/prizes/img");
        break;
}

$sql = "INSERT INTO chidon_prizes ( 
          prize_name,    prize_picture,    quantity,    made_possible_by,    personalization,    color,    size,    note,   price,  our_price,  year
    ) VALUES (
        '$prize_name', '$prize_picture', '$quantity', '$made_possible_by', '$personalization', '$color', '$size', '$note', $price, $our_price, $year
    )
";

mysql_query($sql);

if (mysql_affected_rows() > 0) {
    http_response_code(200);
    header('Location: ./index.php');
} else {
    http_response_code(302);
    header('Location: ./new.php');
}