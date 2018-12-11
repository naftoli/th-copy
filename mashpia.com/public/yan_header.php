<?
$admin_auth = array('school'); 
require('header.php');

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$sql = "select * from line_campaigns where year = " . $year;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	$yudAlef = $row['start_date'];
	$campaigns[$row['id']] = strtolower( $row['type'] );
}

//$yudAlef = 2457851; // 5777
//$campaigns = array(
//	9 => 'tanya',
//	10 => 'mishna'
//); 
/*
require_once 'class.maosChittim.php';
$m = new MaosChittim(5774);
 * 
 */
?>