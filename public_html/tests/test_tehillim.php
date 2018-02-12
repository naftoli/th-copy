<?php 
$admin_auth = array('school');
require_once '../header.php';
require_once '../class.shabbosMevorchim.php';

$sm = new ShabbosMevorchim();
$sm->setReportDates();
$dates = $sm->getReportDates();
echo "<pre>"; print_r($dates); echo "</pre>";
$sm->setDebug();
$sm->setArmyResults();

/*
require_once '../class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

echo "before computing school results: " . time() . "<br />";
foreach ($schools as $id => $name) {
    $sm->setSchoolResults( $id );
}
echo "after computing school results: " . time() . "<br />";
*/
?>
