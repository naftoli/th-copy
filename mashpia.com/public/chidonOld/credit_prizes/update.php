<?php
$admin_auth = array('school');
require('../../header.php');
require('./shared.php');

$id = isset($_POST['id']) ? mysql_real_escape_string($_POST['id']) : false;
if (!$id){
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}
$sql = "SELECT * FROM chidon_credit_prizes WHERE chidon_credit_prize_id = '$id'";
$query = mysql_query($sql);
$prize = mysql_fetch_assoc($query);

if (!$prize){
    http_response_code(302);
    header('Location: ./edit.php?id='.$_POST['id']);
    exit;
}

$prize_name = isset($_POST['prize_name']) ? mysql_real_escape_string($_POST['prize_name']) : "";
$quantity = isset($_POST['quantity']) ? mysql_real_escape_string($_POST['quantity']) : "0";
$credits_needed = isset($_POST['credits_needed']) ? mysql_real_escape_string($_POST['credits_needed']) : "0";

$prize_picture = null;
if ($_FILES['prize_picture']['size'] > 0) {
    switch($_FILES['prize_picture']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
        case UPLOAD_ERR_PARTIAL:
        case UPLOAD_ERR_NO_FILE:
            break;
        default: // if an image was uploaded succesfully save it
            $prize_picture = save_image($_FILES['prize_picture'], "/chidonOld/credit_prizes/img", $prize['prize_picture']);
            break;
    }
}

if ($prize_picture) $prize_picture = mysql_real_escape_string($prize_picture);

$sql = "UPDATE chidon_credit_prizes 
        SET prize = '$prize_name',
            " . ($prize_picture ? "img = '$prize_picture', " : " ") . "
            quantity = '$quantity',
            credits = '$credits_needed',
        WHERE prize_id = '{$prize['chidon_credit_prize_id']}'
    ";
echo $sql; exit;
mysql_query($sql);

if (mysql_affected_rows() > 0) {

    http_response_code(302);
    header('Location: ./index.php');
} else {

    http_response_code(302);
    header('Location: ./edit.php?id='.$_POST['id']);
}