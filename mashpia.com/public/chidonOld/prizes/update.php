<?

$admin_auth = array('school');
require('../../header.php');


$id = isset($_POST['id']) ? mysql_real_escape_string($_POST['id']) : false;
if (!$id){
    
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}
$sql = "SELECT * FROM chidon_prizes WHERE prize_id = '$id'";
$query = mysql_query($sql);
$prize = mysql_fetch_assoc($query);

if (!$prize){
    
    http_response_code(302);
    header('Location: ./edit.php?id='.$_POST['id']);
    exit;
}

// $prize_picture = isset($_POST['prize_picture']) ? mysql_real_escape_string($_POST['prize_picture']) : "null";
$prize_name = isset($_POST['prize_name']) ? mysql_real_escape_string($_POST['prize_name']) : "";
$quantity = isset($_POST['quantity']) ? mysql_real_escape_string($_POST['quantity']) : "0";
$made_possible_by = isset($_POST['made_possible_by']) ? mysql_real_escape_string($_POST['made_possible_by']) : "null";
$personalization = isset($_POST['personalization']) ? mysql_real_escape_string($_POST['personalization']) : "null";
$color = isset($_POST['color']) ? mysql_real_escape_string($_POST['color']) : "null";
$size = isset($_POST['size']) ? mysql_real_escape_string($_POST['size']) : "null";
$note = isset($_POST['note']) ? mysql_real_escape_string($_POST['note']) : "null";
$price = isset($_POST['price']) ? mysql_real_escape_string($_POST['price']) : "null";
$our_price = isset($_POST['our_price']) ? mysql_real_escape_string($_POST['our_price']) : "null";

$sql = "UPDATE chidon_prizes 
        SET prize_name = '$prize_name',
            quantity = '$quantity',
            made_possible_by = '$made_possible_by',
            personalization = '$personalization',
            color = '$color',
            quantity = '$quantity',
            size = '$size',
            note = '$note',
            price = '$price',
            our_price = '$our_price'
        WHERE prize_id = '{$prize['prize_id']}'
    ";
mysql_query($sql);

if (mysql_affected_rows() > 0) {
    
    http_response_code(302);
    header('Location: ./index.php');
} else {
    
    http_response_code(302);
    header('Location: ./edit.php?id='.$_POST['id']);
}