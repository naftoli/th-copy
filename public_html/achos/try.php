<?php
require_once 'class.report.php';
$r = new Report();
echo "Originally: " . print_r( $r->getReportDates() ) . "<br />";
$r->setPreviousDates();
$dates = $r->getReportDates();
echo "Now: " . print_r( $dates );
?>