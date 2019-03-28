<?
require 'db.php';
	
$users = array();
$sql = "select hd.*, u.first, u.last, s.school_name 
		from he_dob hd 
		join users u using (user_id) 
		join schools s using (school_id)";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row;
}

$bdays = array();
$years = array( 5775, 5776 );

foreach ( $users as $user ) {
	if ( $user['he_mm'] && $user['he_dd'] ) {
		foreach ($years as $year) {		
			if ($year == 5775) {
				if ($user['he_mm'] < 12) continue;
			}
			
			if ($year == 5775) { //not leap year
				$heJD = jewishtojd( $user['he_mm'], $user['he_dd'], $year );
			} else if ($year == 5776) { //leap year
				if ( !$user['born_in_leap'] && $user['he_mm'] == 6 ) {
					$heJD = jewishtojd( 7, $user['he_dd'], $year );
				} else {
					$heJD = jewishtojd( $user['he_mm'], $user['he_dd'], $year );
				}
			}
			 
			$greg = jdtogregorian( $heJD );
			$arrGreg = explode('/', $greg);
			$date = $arrGreg[2] . '-' . $arrGreg[0] . '-' . $arrGreg[1];
			
			if ( $date ) {
				$bdays[] = array(
					'name'		=>	$user['first'] . ' ' . $user['last'], 
					'school'	=>	$user['school_name'], 
					'user_id'	=>	$user['user_id'], 
					'date'		=>	$date
				);
			}
		}
	}
}
echo "<pre>"; print_r( $bdays ); echo "</pre>";
?>