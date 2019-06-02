<?php
ini_set('display_errors',1);
require_once 'classes/charidyEmails.php';

$e = new charidyEmails();
$e->sendEmails();

$errors = $e->getErrors();
if ( $errors ) {
  foreach ( $errors as $error ) {
    echo $error . "<br />";
  }
} else {
  echo "done";
}