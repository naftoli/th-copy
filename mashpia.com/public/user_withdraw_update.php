<? 
require('db.php');

	function beginning_of_hebrew_year() 
	{
		return dateThisYear(13, 18);
	}
	
	
	function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting) 
			$starting = unixtojd();
			
		$today = cal_from_jd($starting, CAL_JEWISH);
		
		return cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
	}
	
/*$sql = "SELECT * FROM user_withdraw";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{
	$print_date = explode('-', $row['print_date']);
	$print_day = explode(' ', $print_date[2]);
	$julian_date = gregoriantojd($print_date[1], $print_day[0], $print_date[0]);
	$update_sql = "UPDATE user_withdraw SET jul_print_date=" . $julian_date . " WHERE code_id=" . $row['code_id'];
	$update_query = mysql_query($update_sql);
}
echo "DONE";
*/

$beginning_of_hebrew_year = beginning_of_hebrew_year();
$sql = "SELECT user_id, count(*) as no_of_vouchers FROM user_withdraw WHERE jul_print_date >= " . $beginning_of_hebrew_year . " GROUP BY user_id";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$vouchers_earned_this_year = $row['no_of_vouchers'];
	if ($row['no_of_vouchers'] >= 64)
		echo "USER ID:" . $row['user_id'] . " " . $row['no_of_vouchers'] . "<br />";
}

//echo "USER ID:" . $row['user_id'] . " " . $row['jul_print_date'] . " " . $beginning_of_hebrew_year . "<br />";
echo "DONE";