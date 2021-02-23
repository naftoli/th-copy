<?

$admin_auth = array('school');
require('../../header.php');


$id = isset($_POST['id']) ? mysql_real_escape_string($_POST['id']) : false;
if (!$id){
    
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}
$sql = "SELECT * FROM chidon_sweaters WHERE sweater_id = '$id'";
$query = mysql_query($sql);
$sweater = mysql_fetch_assoc($query);

if (!$sweater){
    
    http_response_code(302);
    header('Location: ./edit.php?id='.$_POST['id']);
    exit;
}

// $sweater_picture = isset($_POST['sweater_picture']) ? mysql_real_escape_string($_POST['sweater_picture']) : "null";
$sweater_name = isset($_POST['sweater_name']) ? mysql_real_escape_string($_POST['sweater_name']) : "";
$quantity = isset($_POST['quantity']) ? mysql_real_escape_string($_POST['quantity']) : "0";
$size = isset($_POST['size']) ? mysql_real_escape_string($_POST['size']) : "null";
$gender = isset($_POST['gender']) ? mysql_real_escape_string($_POST['gender']) : "null";
$price = isset($_POST['price']) ? mysql_real_escape_string($_POST['price']) : "null";
$our_price = isset($_POST['our_price']) ? mysql_real_escape_string($_POST['our_price']) : "null";



$sql = "UPDATE chidon_sweaters 
        SET sweater_name = '$sweater_name',
            quantity = '$quantity',
            size = '$size',
            gender = '$gender',
            price = '$price',
            our_price = '$our_price'
        WHERE sweater_id = '{$sweater['sweater_id']}'
    ";
mysql_query($sql);

if (mysql_affected_rows() > 0) {
    
    http_response_code(302);
    header('Location: ./index.php');
} else {
    
    http_response_code(302);
    header('Location: ./edit.php?id='.$_POST['id']);
}