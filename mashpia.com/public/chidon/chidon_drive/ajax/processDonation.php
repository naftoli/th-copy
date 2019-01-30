<?php
$admin_auth = ['school'];
define( "MASHPIA_AUTH_REQUIRED", true );
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$year = $_POST['year'];
$donation = $_POST['donation'];
$user_donations = $_POST['user_donations'];
$cc_info = $_POST['cc_info'];
$cc_info['desc'] = "Chidon Drive " . $year;

// extract first and last name
$cc_info['first'] = '';
$cc_info['last'] = trim( $cc_info['name'] );
if ( strpos( $cc_info['last'], ' ' ) !== false ) {
  $full_name = explode(' ', $cc_info['last']);
  $lastPos = count( $full_name ) - 1;
  $cc_info['last'] = $last_name[$lastPos];
  $cc_info['first'] = $last_name[0];
  if ( $lastPos > 1 ) {
    for ( $i = 1; $i < $lastPos; $i++ ) {
      $cc_info['first'] += $last_name[$i];
    }
  }
}

// make sure cc info is valid
$msg = '';
$card_num = preg_replace('/\s+/', '', $cc_info['number']);
if ( strlen( $card_num ) != 16 ) {
  $msg .= "Credit Card Number must be 16 digits.\n"; 
} else {
  $cc_info['number'] = $card_num;
}

if ( strpos( $cc_info['exp'], '/' ) === false ) {
  $msg .= "Invalid Expiry Date Format.\n";
} else {
  $expiry = explode('/', $cc_info['exp']);
  // strip spaces 
  foreach ( $expiry as $k => $v ) {
    $expiry[$k] = trim( $v );
  }
  if ( strlen( $expiry[0] ) != 2 || strlen( $expiry[1] != 4 ) ) {
    $msg .= "Invalid Expiry Date Format.\n";
  } else {
    $exp = $expiry[1] . '-' . $expiry[0];
    $cc_info['exp'] = $exp;
  }
}

$cvc = $cc_info['cvc'];
if ( !is_numeric( $cvc ) ) {
  $msg .= "CVC must be a number.\n";
}

if ( !empty( $msg ) ) {
  echo $msg;
  exit;
}

exit;

// process donation amount through authorize
require 'authorize.php';
$response = chargeCreditCard( $donation, $cc_info );
exit;

// if successful add donation and user donations to db
$MASHPIA_DB->beginTransaction();

$stmt = $MASHPIA_DB->prepare("
  INSERT INTO chidon_donations 
  SET admin_id = :admin, 
  chidon_year = :year, 
  donation_amount = :donation,
  transaction_id = :trans_id, 
  transaction_info = :trans_info
");
$res = $stmt->execute([
  ':admin'    =>  $admin_user['admin_id'], 
  ':year'     =>  $year, 
  ':donation' =>  $donation, 
  ':trans_id' =>  111, 
  ':trans_info' => '1:111:succes'
]);
if ( !$res ) {
  $MASHPIA_DB->rollBack();
  echo json_encode([
    'success' => false,
    'message' => 'Error entering user donation into db.'
  ]);
  exit;
}

$stmt = $MASHPIA_DB->prepare("
  INSERT INTO chidon_user_subsidies 
  SET chidon_donation_id = :donation_id, 
  chidon_year = :year,
  user_id = :user,
  subsidy_amount = :amount
");
foreach ( $user_donations as $donation ) {
  $res = $stmt->execute([
    ':donation_id'  =>  $MASHPIA_DB->lastInsertId(), 
    ':year'         =>  $year, 
    ':user'         =>  $donation['user_id'], 
    ':amount'       =>  $donation['amount']
  ]);
  if ( !$res ) {
    $MASHPIA_DB->rollBack();
    echo json_encode([
      'success' => false,
      'message' => 'Error entering user donation into db.'
    ]);
    exit;
  }
}

// if we get here all is good
$MASHPIA_DB->commit();
echo json_encode([
  'success' =>  true, 
  'message' =>  'Your donation has been processed and given out to the users.'
]);