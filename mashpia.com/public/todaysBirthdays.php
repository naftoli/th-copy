<?
require 'db.php';

$heDate = jdtojewish(unixtojd());
$arrHeDate = explode('/', $heDate);
$heMM = $arrHeDate[0];
$heDD = $arrHeDate[1];

//need to customize sql query
//if user was born in leap year and in adar II it will still be 6 (php 5.4-)
switch ($heMM) {
	case 6:
	case 7:
		//find out if current year is leap year
		$hYear = $arrHeDate[2];
		if (((7 * $hYear + 1) % 19) < 7) {
			$leap = true;
		} else {
			$leap = false;
		}
		
		if ($leap) {
			if ($heMM == 6) {
				$sql = "select * from he_dob 
						where he_mm = $heMM 
						and born_in_leap = 1 
						and he_dd = $heDD";
			} else if ($heMM == 7) {
				$sql = "select * from he_dob 
						where 
						( 
							(he_mm = 7 and he_dd = $heDD) 
						or 
							(he_mm = 6 and born_in_leap = 0 and he_dd = $heDD)
						)";
			}
		} else {
			$sql = "select * from he_dob 
					where 
					(he_mm = 6 or he_mm = 7) 
					and he_dd = $heDD";
		}

		break;
	default:
		$sql = "select * from he_dob where he_mm = $heMM and he_dd = $heDD";
		break;
}				

$users = array();
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

$bdays = array();
$sql = "select u.user_id, u.first, u.last, s.school_name  
		from users u 
		join schools s using (school_id) 
		where u.user_id in (" . implode(',', $users) . ") 
		order by last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$bdays[] = $row;
}

echo "<pre>"; print_r($bdays); echo "</pre>";
?>
