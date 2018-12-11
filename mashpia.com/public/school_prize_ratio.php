<?
$admin_auth = array('school'); 
require('header.php');

require_once 'class.schoolPrizeRatio.php';
$spr = new SchoolPrizeRatio();

require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
require_once 'class.schoolsUsers.php';

foreach ( $schools as $id => $name ) {
    $su = new SchoolsUsers( $id );
    $users = $su->getUsers();
    echo $name . " : " . $spr->getRatio( count( $users ) ) . "<br />";
}
?>