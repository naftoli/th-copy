<?
require '../../../db.php';
$subject_id = mysql_real_escape_string( $_POST['subject'] );
$cat = mysql_real_escape_string( $_POST['cat'] );
$year = mysql_real_escape_string( $_POST['year'] );
$lang = mysql_real_escape_string( $_POST['lang'] );

$sql = "select * from mandatory_cats where cat = \"" . $cat . "\" and subject_id = " . $subject_id . " and year = " . $year . " and lang_id = " . $lang;
$result = mysql_query( $sql );
if (mysql_num_rows($result) > 0) {
	echo 1;
} else {
	echo 0;
}
?>