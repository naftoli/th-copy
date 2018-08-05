<?php
ini_set('display_errors',1);
header('Content-type: application/json');
require 'classes/ChabadShliach.php';
$shliach = new ChabadShliach( $_POST['key'] );

// make sure we get all needed info
$success = false;
if ( $shliach->authenticate() ) {
    if ( $shliach->setPersonalInfo() ) {
        $shliach->setCenters( array( 117551 ) );
        if ( $shliach->setMosdos() ) {
            $success = true;
            // do something with info
            echo json_encode( $shliach );
        }
    }
}

if ( !$success ) {
      echo $shliach->getError();
}
?>