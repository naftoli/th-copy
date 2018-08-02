<?php
ini_set('display_errors',1);
header('Content-type: application/json');
require 'classes/ChabadShliach.php';
$shliach = new ChabadShliach( $_POST['key'] );
$mosadID = $_POST['mosad'];

// make sure we get all needed info
$success = false;
if ( $shliach->authenticate() ) {
    $shliach->setDebug( true );
    if ( $shliach->setPersonalInfo() ) {
        $shliach->setCenters( array( $mosadID ) );
        if ( $shliach->setMosdos() ) {
                $success = true;
                // do something with info
                print_r( $shliach->getMosdos() );
        }
    }
}

if ( !$success ) {
      echo $shliach->getError();
}