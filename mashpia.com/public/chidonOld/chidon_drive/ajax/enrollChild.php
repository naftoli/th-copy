<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$childInfo = $_POST['child'];

$stmt = $MASHPIA_DB->prepare("
  UPDATE users 
  SET
      first = :first, 
      last = :last, 
      first_he = :heFirst, 
      last_he = :heLast
  WHERE
      user_id = :user
");
$stmt->execute([
  ':first'    =>  $childInfo['first'], 
  ':last'     =>  $childInfo['last'], 
  ':heFirst'  =>  $childInfo['heFirst'], 
  ':heLast'   =>  $childInfo['heLast'], 
  ':user'     =>  $childInfo['user_id']
]);

if ( intval( $childInfo['school_id'] ) == 269 ) {
  $stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon 
    SET 
        history = :history,
        size = :sweater,
        grade = :grade,
        book = :book,
        host = :acc_family,
        between_streets1 = :acc_cross1,
        between_streets2 = :acc_cross2,
        host_number = :acc_phone,
        allergies = :allergies,
        sandwich = :sandwich,
        shoe_size = :shoe,
        test_lang = :test_lang, 
        notes = :notes, 
        answers = :answer, 
        host_street = :street, 
        host_street_num = :street_num, 
        host_street_num_suffix = :street_num_suffix, 
        host_street_apt = :street_apt, 
        walking_zone = :zone,
        walking = :walking, 
        in_zone = :in_zone, 
        transportation = :transportation, 
        airport = :airport, 
        flight = :flight, 
        snack_way_back = :snack, 
        school_name = :school_name, 
        affiliation = :affiliation, 
        friend_name = :friend_name, 
        home_address = :anash_address, 
        home_city = :anash_city, 
        home_state = :anash_state, 
        home_zip = :anash_zip, 
        medications = :medications, 
        known_family = :known_family, 
        anash_airport = :anash_airport, 
        anash_arrival = :anash_arrival, 
        morning_pickup = :morning_pickup
    WHERE
        year = :year AND user_id = :user_id
  ");
  $res = $stmt->execute([
    ':history'      =>  $childInfo['history'], 
    ':sweater'      =>  $childInfo['sweater'], 
    ':grade'        =>  $childInfo['grade'], 
    ':book'         =>  $childInfo['book'], 
    ':acc_family'   =>  $childInfo['acc_family'], 
    ':acc_cross1'   =>  $childInfo['acc_cross1'], 
    ':acc_cross2'   =>  $childInfo['acc_cross2'], 
    ':acc_phone'    =>  $childInfo['acc_phone'], 
    ':allergies'    =>  $childInfo['allergies'], 
    ':sandwich'     =>  $childInfo['sandwich'], 
    ':shoe'         =>  $childInfo['shoe'],
    ':test_lang'    =>  $childInfo['test_lang'],
    ':year'         =>  $year, 
    ':user_id'      =>  $childInfo['user_id'],
    ':notes'        =>  $childInfo['notes'], 
    ':answer'       =>  $childInfo['answer'], 
    ':street'       =>  $childInfo['acc_street'], 
    ':street_num'   =>  $childInfo['acc_street_num'], 
    ':street_num_suffix'  =>  $childInfo['acc_street_num_suffix'], 
    ':street_apt'   =>  $childInfo['acc_street_apt'], 
    ':zone'         =>  $childInfo['walking_zone'], 
    ':walking'      =>  $childInfo['walking'], 
    ':in_zone'      =>  $childInfo['in_zone'], 
    ':transportation' => $childInfo['transportation'], 
    ':airport'      =>  $childInfo['airport'], 
    ':flight'       =>  $childInfo['flight'], 
    ':snack'        =>  $childInfo['snack_way_back'], 
    ':school_name'  =>  $childInfo['school_name'], 
    ':affiliation'  =>  $childInfo['affiliation'], 
    ':friend_name'  =>  $childInfo['friend_name'], 
    ':anash_address'=>  $childInfo['anash_address'], 
    ':anash_city'   =>  $childinfo['anash_city'], 
    ':anash_state'  =>  $childInfo['anash_state'], 
    ':anash_zip'    =>  $childInfo['anash_zip'], 
    ':medications'  =>  $childInfo['medications'], 
    ':known_family' =>  $childInfo['known_family'], 
    ':anash_airport'=>  $childInfo['anash_airport'], 
    ':anash_arrival'=>  $childInfo['anash_arrival'], 
    ':morning_pickup' => $childInfo['morning_pickup']
  ]);
} else {
  $stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon 
    SET 
        history = :history,
        size = :sweater,
        grade = :grade,
        book = :book,
        host = :acc_family,
        between_streets1 = :acc_cross1,
        between_streets2 = :acc_cross2,
        host_number = :acc_phone,
        allergies = :allergies,
        sandwich = :sandwich,
        shoe_size = :shoe,
        test_lang = :test_lang, 
        notes = :notes, 
        answers = :answer, 
        host_street = :street, 
        host_street_num = :street_num, 
        host_street_num_suffix = :street_num_suffix, 
        host_street_apt = :street_apt, 
        walking_zone = :zone,
        walking = :walking, 
        in_zone = :in_zone, 
        transportation = :transportation, 
        airport = :airport, 
        flight = :flight, 
        snack_way_back = :snack
    WHERE
        year = :year AND user_id = :user_id
  ");

  $res = $stmt->execute([
    ':history'      =>  $childInfo['history'], 
    ':sweater'      =>  $childInfo['sweater'], 
    ':grade'        =>  $childInfo['grade'], 
    ':book'         =>  $childInfo['book'], 
    ':acc_family'   =>  $childInfo['acc_family'], 
    ':acc_cross1'   =>  $childInfo['acc_cross1'], 
    ':acc_cross2'   =>  $childInfo['acc_cross2'], 
    ':acc_phone'    =>  $childInfo['acc_phone'], 
    ':allergies'    =>  $childInfo['allergies'], 
    ':sandwich'     =>  $childInfo['sandwich'], 
    ':shoe'         =>  $childInfo['shoe'],
    ':test_lang'    =>  $childInfo['test_lang'],
    ':year'         =>  $year, 
    ':user_id'      =>  $childInfo['user_id'],
    ':notes'        =>  $childInfo['notes'], 
    ':answer'       =>  $childInfo['answer'], 
    ':street'       =>  $childInfo['acc_street'], 
    ':street_num'   =>  $childInfo['acc_street_num'], 
    ':street_num_suffix'  =>  $childInfo['acc_street_num_suffix'], 
    ':street_apt'   =>  $childInfo['acc_street_apt'], 
    ':zone'         =>  $childInfo['walking_zone'], 
    ':walking'      =>  $childInfo['walking'], 
    ':in_zone'      =>  $childInfo['in_zone'], 
    ':transportation' => $childInfo['transportation'], 
    ':airport'      =>  $childInfo['airport'], 
    ':flight'       =>  $childInfo['flight'], 
    ':snack'        =>  $childInfo['snack_way_back']
  ]);
}
// echo "<pre>"; echo $stmt->debugDumpParams(); echo "</pre>"; exit;
if ( $res ) {
  echo json_encode([
    'success'   =>  true,
    'message'   =>  'Saved.'
  ]);
} else {
  echo json_encode([
    'success'   =>  false,
    'error'     =>  'Error saving info.'
  ]);
}
