<?php
define( 'MASHPIA_AUTH_REQUIRED', true );
require_once( __DIR__ .'/header/header.php' );

$current_user->beta = $current_user->beta == 1 ? 0 : 1; // toggle the enrollment in the beta program
$current_user->save();

header("Location: ../admin.php");
die();