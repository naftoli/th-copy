<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

$school_id = mysql_real_escape_string( $_POST['school_id'] );
$bus = mysql_real_escape_string( $_POST['bus'] );

//***************** REGISTER SCHOOL **********************/
$school_exists = mysql_query(
     " SELECT th_chidon_schools_id FROM th_chidon_schools "
    ." WHERE school_id = " . $school_id . " "
    ." AND year = " . $year . " "
    ." AND registered = 1 "
);
if (mysql_num_rows($school_exists) == 0) {
    mysql_query(
         " INSERT INTO th_chidon_schools "
        ." SET school_id = " . $school_id . ", "
        ." year = " . $year . ", "
        ." bus = " . $bus . ", "
        ." registered = 1"
    );
}