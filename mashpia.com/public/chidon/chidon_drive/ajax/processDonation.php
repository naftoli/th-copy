<?php
$admin_auth = ['school'];
define( "MASHPIA_AUTH_REQUIRED", true );
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$year = $_POST['year'];
$donation = $_POST['donation'];
$user_donations = $_POST['user_donations'];

// process donation amount through authorize
echo "<pre>"; print_r( $current_user ); echo "</pre>"; exit;

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