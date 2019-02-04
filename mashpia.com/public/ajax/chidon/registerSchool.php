<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

$school_id = mysql_real_escape_string( $_POST['school_id'] );
$bus = mysql_real_escape_string( $_POST['bus'] );
$customer_id = mysql_real_escape_string( $_POST['customer_id'] );
$payment_id = mysql_real_escape_string( $_POST['payment_id'] );

//***************** REGISTER SCHOOL **********************/
$school_exists = mysql_query(
     " SELECT th_chidon_schools_id FROM th_chidon_schools "
    ." WHERE school_id = " . $school_id . " "
    ." AND year = " . $year . " "
    ." AND registered = 1 "
);
if (mysql_num_rows($school_exists) == 0) {
    $res = mysql_query(
         " INSERT INTO th_chidon_schools "
        ." SET school_id = " . $school_id . ", "
        ." year = " . $year . ", "
        ." bus = " . $bus . ", "
        ." customer_profile_id = " . $customer_id . ", "
        ." payment_profile_id = " . $payment_id . ", "
        ." registered = 1"
    );
    if ( !$res ) echo "Error registering school for Chidon Shabbaton " . $year;
    else echo 0;
}