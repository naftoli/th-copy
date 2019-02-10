<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$year = 5778;
$donation = $_POST['donation_info'];

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/AuthorizeAPIRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/PaymentProfile.php';

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;
use classes\authorize\PaymentProfile;

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

// prepare variables for donor table
$name = $donation['name'];
$display_name = $donation['anonymous'] ? 'Anonymous' : $donation['display_name'];
$email = $donation['email'];
$phone = $donation['phone'];

// prepare variables for donation table
$amount = $donation['amount'];
$forFamily = $donation['family'];
$forChild = $donation['forChild'];

// prepare variables for payment
$cc_num = $donation['cc']['num'];
$cc_exp = $donation['cc']['exp'];
$cc_security = $donation['cc']['cvv'];

// prepare billing address
$billing = $donation['cc']['billing'];
$address = $billing['address'] . ' ' . $billing['apt'];
$city = $billing['city'];
$state = $billing['state'];
$zip = $billing['zip'];
$country = $billing['country'];

$success = true;
$error_msg = [];
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
if ( !$res ) $error_msg[] = "Database Error.";
else {
  $row = $stmt->fetch();
  if ( empty( $row ) ) {
    // create donor
    $qry = "
      INSERT INTO chidon_donors 
      SET 
          name = :name,
          display_name = :display_name,
          phone = :phone, 
          email = :email
    ";
    $stmt2 = $MASHPIA_DB->prepare( $qry );
    $res2 = $stmt2->execute([
      ':name'         =>  $name,
      ':display_name' =>  $display_name,
      ':phone'        =>  $phone,
      ':email'        =>  $email
    ]);
    if ( $res2 ) {
        $donor_id = $MASHPIA_DB->lastInsertId();
    } else {
      $donor_id = $row['chidon_donor_id'];
      $customer_id = $row['authorize_customer_profile_id'];

      // update donor
      $qry = "
        UPDATE chidon_donors 
        SET 
            name = :name,
            display_name = :display_name,
            phone = :phone
        WHERE
            chidon_donor_id = :id
      ";
      $stmt2 = $MASHPIA_DB->prepare( $qry );
      $res2 = $stmt2->execute([
        ':name'         =>  $name,
        ':display_name' =>  $display_name,
        ':phone'        =>  $phone,
        ':id'           =>  $donor_id
      ]);
    }
  }
}

// create payment profile (and customer profile if needed)
if ( !$customer_id ) {
  $description = "Customer profile for " . $name;
  $paymentProfile = PaymentProfile::createBasicArray( $cc_num, $cc_exp, $cc_security );
  $customerProfile = CustomerProfile::create( 'Chidon_Drive_Donor_' . $donor_id, $email, $description, $paymentProfile );
  if ( $customerProfile instanceof CustomerProfile ) {
      $customer_id = $customerProfile->customerProfileId;
      $payment_id = $customerProfile->paymentProfiles[0]['customerPaymentProfileId'];
  } else {
      $error_msg[] = $customerProfile['message'];
  }

  if ( !($customer_id && $payment_id) ) {
      $error_msg[] = "Error creating donor profile.";
  } else {
    // update table
    $stmt3 = $MASHPIA_DB->prepare("
      UPDATE chidon_donors 
      SET 
          authorize_customer_profile_id = :customer, 
          authorize_payment_profile_id = :payment 
      WHERE
          chidon_donor_id = :id
    ");
    $res3 = $stmt3->execute([
      ':customer'   =>  $customer_id, 
      ':payment'    =>  $payment_id
    ]);
    if ( !$res3 ) {
      $error_msg[] = "Error updating authorize profile for donor.";
    }
  } else {
    $error_msg[] = "Error creating donor.";
  }
} else {
  // create a new payment profile
  $paymentProfile = PaymentProfile::create( $cc_num, $cc_exp, $cc_security, $customer_id );
  if ( $paymentProfile instanceof PaymentProfile ) {
      $payment_id = $paymentProfile->customerPaymentProfileId;
  } else {
      // get error
      $error_msg[] = $paymentProfile['messages']['message'][0]['text'];
  }
}

if ( $success && $donor_id && $customer_id && $payment_id ) {
  // now we can process the amount through authorize
  
}

if ( count( $error_msg ) > 0 ) {
  echo json_encode([
    'success'   =>  false,
    'message'   =>  $error_msg
  ]);
} else {
  echo json_encode([
    'success'   =>  true,
    'message'   =>  "Thank you for your donation. You should be getting a confirmation email shortly with your transaction details."
  ]);
}