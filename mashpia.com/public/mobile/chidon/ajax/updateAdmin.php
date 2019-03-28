<?
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['admin'] );
require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$fcell = mysql_real_escape_string($_POST['fcell']);
$mcell = mysql_real_escape_string($_POST['mcell']);
$hphone = mysql_real_escape_string($_POST['phone']);
$whatsapp = isset($_POST['whatsapp']) ? mysql_real_escape_string($_POST['whatsapp']) : 0;
$email = mysql_real_escape_string($_POST['email']);
$city = mysql_real_escape_string($_POST['city']);
$state = mysql_real_escape_string($_POST['state']);
$country = mysql_real_escape_string($_POST['country']);

$sql = "update admins
        set admin_phone_mobile = '" . $fcell . "',
        admin_phone_mobile2 = '" . $mcell . "',
        admin_phone_home = '" . $hphone . "',
        chidon_whatsapp = " . $whatsapp . ",
        admin_email = '" . $email . "',
        admin_city = '" . $city . "',
        admin_state = '" . $state . "',
        admin_country = '" . $country . "' 
        where admin_id = " . $admin;
mysql_query($sql);
?>