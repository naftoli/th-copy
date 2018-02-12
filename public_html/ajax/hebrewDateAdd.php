<?
$user_id = $_POST['user_id'];
require '../db.php';
$sql = "select dob from users where user_id = " . $user_id;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$dob = $row['dob'];

$arrDate = explode('-', $dob);
$jd = gregoriantojd($arrDate[1], ($arrDate[2]+1), $arrDate[0]);
$jewish = jdtojewish($jd, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH);
$j = iconv('WINDOWS-1255', 'UTF-8', $jewish);

$sql = "update users set dob_he = '" . mysql_real_escape_string( $j ) . "', dob_he_offset = 1 where user_id = " . $user_id;
//echo $sql;
mysql_query( $sql );

require_once '../class.birthday.php';
$b = new Birthday( $user_id );
$b->setBirthday();
?>