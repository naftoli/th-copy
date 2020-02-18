<?php
require_once '../email/enrollmentEmail.php';
if ( sendEnrollmentEmail( 'naftoli@tzivoshashem.org' ) ) echo "sent";
else echo "error";