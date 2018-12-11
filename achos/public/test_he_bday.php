<?
require_once 'db.php';
$sql = "select dob_he from users where dob_he > 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
$hBday = $row['dob_he'];
	if ( strpos( $hBday, '/' ) ) {
		//$jewish = jdtojewish($hBday, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH);
		$j = iconv('WINDOWS-1255', 'UTF-8', $hBday);
		echo $j . "<br />";
	} else {
		echo $hBday . "<br />";
	}
}
?>
                                