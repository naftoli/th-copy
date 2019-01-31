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
  $cc_info['last'] = $full_name[$lastPos];
  $cc_info['first'] = $full_name[0];
  if ( $lastPos > 1 ) {
    for ( $i = 1; $i < $lastPos; $i++ ) {
      $cc_info['first'] += $full_name[$i];
    }
  }
}

// make sure cc info is valid
$msg = '';
$card_num = preg_replace('/\s+/', '', $cc_info['number']);
$length = strlen( $card_num );
if ( $length < 15 || $length > 16 ) {
  $msg .= "Credit Card Number must be 15 or 16 digits.\n"; 
} else {
  $cc_info['number'] = $card_num;
}

if ( strpos( $cc_info['exp'], '/' ) === false ) {
  $msg .= "Expiry Date must be in the format MM / YYYY.\n";
} else {
  // strip spaces and divide mm and yyyy
  $expiry = explode('/', preg_replace('/\s+/', '', $cc_info['exp']));
  $mm = $expiry[0];
  $yy = $expiry[1];
  if ( strlen( $mm ) != 2 || strlen( $yy ) != 4 ) {
    $msg .= "Expiry Date must be in the format MM / YYYY.\n";
  } else if ( intval( $mm ) > 12 || intval( $yy ) < 2019 ) {
    if ( intval( $mm ) > 12 ) $msg .= "Expiry Month cannot be greater than 12.\n";
    if ( intval( $yy ) < 2019 ) $msg .= "Expiry Year cannot be less than 2019.\n";
  } else {
    $exp = $expiry[1] . '-' . $expiry[0];
    $cc_info['exp'] = $exp;
  }
}

$cvc = $cc_info['cvc'];
if ( !is_numeric( $cvc ) ) {
  $msg .= "CVC can only have numbers.\n";
}

if ( !empty( $msg ) ) {
  echo $msg;
  exit;
}

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