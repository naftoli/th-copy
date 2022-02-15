<?
$admin_auth = array('school');
require('../../header.php');
require('./shared.php');
require_once('../../class.globalSettings.php');

$sweater_name = isset($_POST['sweater_name']) ? mysql_real_escape_string($_POST['sweater_name']) : "";
$quantity = isset($_POST['quantity']) ? mysql_real_escape_string($_POST['quantity']) : "0";
$size = isset($_POST['size']) ? mysql_real_escape_string($_POST['size']) : "null";
$gender = isset($_POST['gender']) ? mysql_real_escape_string($_POST['gender']) : "null";
$price = isset($_POST['price']) ? mysql_real_escape_string($_POST['price']) : "null";
$our_price = isset($_POST['our_price']) ? mysql_real_escape_string($_POST['our_price']) : "null";
$year = GlobalSettings::getChidonYear();

$sweater_picture = "";
switch($_FILES['sweater_picture']) {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
        break;
    default: // if an image was uploaded succesfully save it
        $sweater_picture = save_image($_FILES['sweater_picture'], "/chidonOld/sweaters/img");
        break;
}

$sql = "INSERT INTO chidon_sweaters ( 
          sweater_name,    sweater_picture,   quantity,   size,    gender,    price,    our_price,   year
    ) VALUES (
        '$sweater_name', '$sweater_picture', $quantity, '$size', '$gender', '$price', '$our_price', $year
    )
";

mysql_query($sql);

if (mysql_affected_rows() > 0) {
    http_response_code(302);
    header('Location: ./index2.php');
} else {
    http_response_code(302);
    header('Location: ./new.php');
}