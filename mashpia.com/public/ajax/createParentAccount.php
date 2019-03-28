<?php
require '../db.php';

foreach ($_POST as $k => $v) {
    $_POST[$k] = mysql_real_escape_string($v);
}

$pos = strpos($_POST['address'], ' ');
$addressNum = substr($_POST['address'], 0, $pos);
$username = strtolower($_POST['lname']) . $addressNum;
    
$data = array(
    'first'             => $_POST['fname'], 
    'last'              => $_POST['lname'], 
    'admin_address1'    => $_POST['address'], 
    'admin_city'        => $_POST['city'], 
    'admin_state'       => $_POST['state'], 
    'admin_postal'      => $_POST['zip'], 
    'admin_country'     => 'USA', 
    'admin_phone_home'  => $_POST['hphone'], 
    'admin_phone_mobile'=> $_POST['cphone'], 
    'admin_email'       => $_POST['email'], 
    'username'          => $username, 
    'password'          => 'p1234'
);

$childID = $_POST['childID'];

require '../newClasses/newParent.php';
$p = new NewParent();

for ($i=0; $i < 100; $i++){
    if ($p->action($data)) {
        //add child to account
        $admin_id = $p->getAdminID();
        $sql = "INSERT INTO admin_auths
                SET admin_id = " . $admin_id . ",
                id = " . $childID . ",
                auth = 'user',
                role_id = 1";
        mysql_query($sql);
        $p->sendConfEmail();
        echo $admin_id;
        exit(); // kill the script
    } else {
        $data['username'] = $username.$i;
    }
}
echo 0;

?>
