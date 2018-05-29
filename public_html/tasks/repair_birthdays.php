<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once( dirname(__FILE__) . '/../header.php' );
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}
// import the required files
require_once( dirname(__FILE__) . '/../class.heDob.php' );
require_once( dirname(__FILE__) . '/../class.birthday.php' );
require_once( dirname(__FILE__) . '/../class.birthdayYi.php' );
// April 15 2019
$cutoff = "2458589"; // all birthdays after this date will be deleted and remade.

$query = mysql_query(
     "SELECT DISTINCT(user_id), dob, mission_name, start_date, end_date "
    ."FROM date_tasks_missions JOIN birthdays USING (date_tasks_mission_id) "
    ."JOIN users USING (user_id) WHERE start_date > 2458689 "
    ."AND subject_id = 40 AND dob <= '2013-01-01 00:00:00' AND user_registered IS NOT NULL "
    ."GROUP BY user_id "
    ."ORDER BY start_date "
);
/*
" SELECT DISTINCT(user_id), dob "
." FROM birthdays "
." JOIN date_tasks_missions USING (date_tasks_mission_id) "
." JOIN users USING (user_id) "
." WHERE start_date >= $cutoff "
." AND user_registered IS NOT NULL;"
*/

echo mysql_num_rows( $query ) . " Children with birthdays that are insane<br/><br/>";
// go through each user and update them
while( $row = mysql_fetch_assoc( $query ) ){
    $user_id = $row['user_id'];
    // create the Hebrew birthdate
    $h = new HeDob( $user_id, true );
	$h->setHeDob();
	// create birthday missions
    $b = new Birthday( $user_id );
    $b->setBirthday();
    $bi = new BirthdayYi( $user_id );
    $bi->setBirthday();
}
