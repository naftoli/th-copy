<?php
ini_set('display_errors',1);
require_once 'classes/charidyEmails.php';

$testEmails = [
  'shimmy@tzivoshashem.org', 
  'cth@tzivoshashem.org', 
  'mushka@tzivoshashem.org', 
  'naftoli@tzivoshashem.org', 
  'pessi@tzivoshashem.org', 
  'chidon@tzivoshashem.org',
  'hakhel@tzivoshashem.org',
  'design@tzivoshashem.org',
  'chayazirkind@gmail.com', 
  'emmegreene@tzivoshashem.org', 
  'shimmyweinbaum@gmail.com'
];

$emailNums = [5,6];
foreach ( $emailNums as $num ) {
  echo "Sending Email #:" . $num . "<br />";
  $e = new charidyEmails();
  $e->setEmailNum( $num );
  $e->setRecipients();
  $e->sendEmails();

  $errors = $e->getErrors();
  if ( $errors ) {
    foreach ( $errors as $error ) {
      echo $error . "<br />";
    }
  } else {
    echo "done.<br />";
  }
}