<?php
require '../../../db.php';
$user_id = mysql_real_escape_string( $_POST['user'] );

$response = []; // create a blank array for the response

/** GET THE USERS RANK */
$rank_query = mysql_query( 
    "SELECT MAX(rank_ord) AS rank FROM rank_marks WHERE user_id = " . $user_id
);
if (mysql_num_rows($rank_query)) {
	$row = mysql_fetch_assoc($rank_query);
	$response['rank'] = intval( $row['rank'] );
} else {
	$response['rank'] = 1;
}
$rank = $response['rank'];

/** GET THE USERS PROMOTION DATES */
$ranks_promoted_query = mysql_query(
    "SELECT * FROM rank_marks WHERE user_id = $user_id ORDER BY rank_ord;"
);
$ranks_promoted = [];
while( $row = mysql_fetch_assoc( $ranks_promoted_query ) ){
    $ranks_promoted[ $row['rank_ord'] ] = date( "m/d/Y", jdtounix( $row['date_promoted'] ) );
}
$response['ranksPromoted'] = $ranks_promoted;

/** GET MEDALS EARNED */
$medals = [];
$medals_query = mysql_query(
     " SELECT medal_ord, subject_id, profile_photo_id, date_awarded "
    ." FROM medal_marks "
    ." JOIN medals_subjects USING (subject_id, medal_ord) "
	." WHERE user_id = " . $user_id . " "
	." order by date_awarded, medal_ord"
);
while ( $row = mysql_fetch_assoc( $medals_query ) ) {
    // convert the julian date to hebrew date
	$jdate = jdtojewish( $row['date_awarded'], true, CAL_JEWISH_ADD_GERESHAYIM );
	$awarded = $str1 = iconv( 'WINDOWS-1255', 'UTF-8', $jdate ); // convert to utf-8
	//$pos = strrpos($awarded, ' ');
	//$awarded = substr($awarded, 0, $pos);
	$medals[] = array(
		'subject'	=>	$row['subject_id'],
		'medal'		=>	$row['medal_ord'], 
		'photo'		=>	$row['profile_photo_id'], 
		'awarded'	=>	$awarded
	);
}
$response['totalMedals'] = mysql_num_rows( $medals_query );
$response['medals'] = $medals;

/** GET THE MEDALS REQUIRED FOR THE REST OF THE BOARD */
$medalsInfo = [];
$medals_info_query = mysql_query(
    "SELECT rank_ord, medals_required FROM ranks ORDER BY rank_ord"
);
while ( $row = mysql_fetch_assoc( $medals_info_query ) ) {
	$medalsInfo[$row['rank_ord']] = $row['medals_required'];
}
$response['medalsInfo'] = $medalsInfo;

// send the response to the client
echo json_encode( $response );
?>