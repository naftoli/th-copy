<?php
require_once 'classes/charidyEmail.php';

$e = new charidyEmail();
$e->sendEmails();

$errors = $e->getErrors();
if ( $errors ) {
  foreach ( $errors as $error ) {
    echo $error . "<br />";
  }
} else {
  echo "done";
}