<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$donation = $_POST['donation_info'];

//echo "<pre>"; print_r( $donation ); echo "</pre>";

function checkMandatory( $values ) {
  $missing = [];

  // map of needed fields with their more user friendly description
  $mandatory = [
    'email'           =>  'Email Address', 
    'name'            =>  'Donor Name',
    'display_name'    =>  'Display Name',
    'num'             =>  'Credit Card Number',
    'exp'             =>  'Credit Card Expiry',
    'cvv'             =>  'Security Code',
    'address'         =>  'Billing Address',
    'city'            =>  'Billing City',
    'state'           =>  'Billing State',
    'zip'             =>  'Billing Zip',
    'country'         =>  'Billing Country'
  ];

  foreach ( $values as $k => $v ) {
    if ( is_array( $v ) ) $missing += checkMandatory( $v );
    else {
      if ( in_array( $k, array_keys( $mandatory ) ) ) {
        $val = trim( $v );
        if ( empty( $val ) ) {
          $missing[] = $mandatory[$k];
        }
      }
    }
  }
  return $missing;
}

$missing = checkMandatory( $donation );

// in order to have the alert box show with line breaks, needs to be in this weird format
if ( !empty( $missing ) ) {
  $message = 
'You have not filled out the following mandatory field(s).';
foreach ( $missing as $field ) {
  $message .= 
"
$field";
}

  echo json_encode([
    'success'   =>  false, 
    'message'   =>  $message
  ]);
  exit;
}

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        *
    FROM
        chidon_donors
    WHERE
        email = :email
");
$res = $stmt->execute([
  ':email'  =>  $donation['email']
]);