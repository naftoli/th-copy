<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shipping Info</title>
<style type='text/css'>
tr, th, td {
	border: 1px dashed black;
	padding: 6px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Shipping Info</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School ID</th>
<th>School</th>
<th>School Phone</th>
<th>Shipping Contact</th>
<th>Contact Phone</th>
<th>Address</th>
</tr>
<?
//get list of schools
include_once('db.php');

$schools = array();
$sql = "select * from schools where school_era is NULL order by school_name";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	$schools[] = $row;
}

foreach ( $schools as $school ) {
	/*	
	echo "<pre>";
	print_r( $school );
	echo "</pre>";
	 * 
	 */
	
	echo "<tr><td>" . $school['school_id'] . "</td><td>" . 
		$school['school_name'] . "</td><td>" . $school['school_phone'] . 
		"</td><td>" . $school['shipping_first'] . " " . $school['shipping_last'] . 
		"</td><td>" . $school['shipping_phone'] . "</td><td>" . 
		$school['shipping_address1'] . "<br />" . 
		( !empty( $school['shipping_address2'] ) ? $school['shipping_address2'] . "<br />" : "" ) . 
		$school['shipping_city'] . ", " . $school['shipping_state'] . "<br />" . 
		$school['shipping_postal'] . "<br />" . 
		$school['shipping_country'] . "</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
