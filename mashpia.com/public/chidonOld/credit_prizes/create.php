<?
$admin_auth = array('school');
require('../../header.php');
require_once('../../class.globalSettings.php');
require('./shared.php');

$prize_name = isset($_POST['prize_name']) ? mysql_real_escape_string($_POST['prize_name']) : "";
$quantity = isset($_POST['quantity']) ? mysql_real_escape_string($_POST['quantity']) : "0";
$credits_needed = isset($_POST['credits_needed']) ? mysql_real_escape_string($_POST['credits_needed']) : "null";
$year = GlobalSettings::getChidonYear();

$prize_picture = "";
switch($_FILES['prize_picture']) {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
        break;
    default: // if an image was uploaded succesfully save it
        $prize_picture = save_image($_FILES['prize_picture'], "/chidonOld/credit_prizes/img");
        break;
}

$sql = "INSERT INTO chidon_credit_prizes ( 
          prize,    img,    quantity,    credits,   year
    ) VALUES (
        '$prize_name', '$prize_picture', '$quantity', '$credits_needed', $year
    )
";

mysql_query($sql);

if (mysql_affected_rows() > 0) {
    http_response_code(302);
    header('Location: ./index.php');
} else {
    http_response_code(302);
    header('Location: ./new.php');
}