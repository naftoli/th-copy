<?
$admin_auth = array('school');
require('../../header.php');

// $sweater_picture = isset($_POST['sweater_picture']) ? mysql_real_escape_string($_POST['sweater_picture']) : "null";
$sweater_name = isset($_POST['sweater_name']) ? mysql_real_escape_string($_POST['sweater_name']) : "";
$quantity = isset($_POST['quantity']) ? mysql_real_escape_string($_POST['quantity']) : "0";
$size = isset($_POST['size']) ? mysql_real_escape_string($_POST['size']) : "null";
$gender = isset($_POST['gender']) ? mysql_real_escape_string($_POST['gender']) : "null";
$price = isset($_POST['price']) ? mysql_real_escape_string($_POST['price']) : "null";
$our_price = isset($_POST['our_price']) ? mysql_real_escape_string($_POST['our_price']) : "null";

$sql = "INSERT INTO chidon_sweaters ( 
          sweater_name,   quantity,   size,    gender,   price,  our_price
    ) VALUES (
        '$sweater_name', $quantity, '$size', '$gender', '$price', '$our_price'
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