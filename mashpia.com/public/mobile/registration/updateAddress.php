<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/json-functions.php';

if ( !$current_user ) {
  require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/setCurrentUser.php';
  $current_user = setCurrentUser();
}

$admin_id = $current_user->admin_id;
if ( !$admin_id ) json_error("There was an error.");

$info = $_POST['info'];
// make sure country is USA for Unites States
if ($info['country'] == "United States") {
    $info['country'] = "USA";
}
$stmt = $MASHPIA_DB->prepare("
    update admins 
    set admin_address1 = :address1, 
    admin_address2 = :address2, 
    admin_city = :city, 
    admin_state = :state, 
    admin_city = :city, 
    admin_postal = :zip, 
    admin_country = :country,  
    updated_address = 1 
    where admin_id = :admin
");
$res = $stmt->execute([
    ':address1' =>  $info['address1'], 
    ':address2' =>  $info['address2'], 
    ':city'     =>  $info['city'], 
    ':state'    =>  $info['state'], 
    ':zip'      =>  $info['zip'], 
    ':country'  =>  $info['country'], 
    ':admin'    =>  $admin_id
]);
if ( $res ) {
    json_response("Address Updated.");
} else {
    json_error("Error updating address." . $stmt->errorInfo()[2]);
}