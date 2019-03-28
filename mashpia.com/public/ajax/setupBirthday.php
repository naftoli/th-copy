<?php
require '../db.php';
require '../class.birthday.php';
require '../class.birthdayYi.php';
require '../class.heDob.php';

$myDomain       = $_SERVER['SCRIPT_URI'];
$requestsSource = $_SERVER['HTTP_REFERER'];
if ( parse_url( $myDomain, PHP_URL_HOST ) !== parse_url( $requestsSource, PHP_URL_HOST ) ){
    die();
}

$users = is_array( $_POST['id'] ) ? $_POST['id'] : array( $_POST['id'] );
foreach ($users as $user) {
    $user_id = mysql_real_escape_string($user);
    // create birthday missions
    $b = new Birthday( $user_id );
    $b->setBirthday();
    $bi = new BirthdayYi( $user_id );
    $bi->setBirthday();
    
    //set dob for syncing with wp
    $hdob = new HeDob( $user_id );
    $hdob->setHeDob();
}