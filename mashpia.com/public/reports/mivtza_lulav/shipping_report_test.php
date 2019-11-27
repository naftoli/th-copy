<?php
ini_set('display_errors', 1);


$admin_auth = array('school'); 	
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php'; 

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php'; 
$year = GlobalSettings::getRegistrationYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php'; 
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // needed for including chidon only schools
$schools = $as->getSchools();

$combined_users = [];

$users_sql = "SELECT * FROM lulav_purchases WHERE year = $year";
$user_query = mysql_query( $users_sql );
while ( $row = mysql_fetch_assoc( $user_query ) ) {
    $users[] = $row['users'];
}

$shipping_sql = "SELECT DISTINCT s.*, e.class_grade, e.class_sub, b.first, b.last, c.users ";
$shipping_sql .= "FROM schools s ";
$shipping_sql .= "JOIN admin_auths a ON s.school_id = a.id AND a.position = 'Base Commander' ";
$shipping_sql .= "JOIN admins b ON b.admin_id = a.id ";
$shipping_sql .= "JOIN mivtzoim_purchases.lulav_purchases c ON c.users IN (" . implode(',', $users) . ") ";
$shipping_sql .= "JOIN users d ON d.school_id = s.school_id ";
$shipping_sql .= "JOIN classes e ON e.class_id = d.class_id ";
$shipping_sql .= "WHERE s.school_id IN (" . implode(',', array_keys($schools)) . ") ";
$shipping_sql .= "GROUP BY d.user_id ORDER BY school_name, first, last";
echo $shipping_sql;

/*$shipping_query = mysql_query( $shipping_sql );
while ( $row = mysql_fetch_assoc( $shipping_query ) ) {
    $combined_users[$row['school_id']][] = $row;
}*/
