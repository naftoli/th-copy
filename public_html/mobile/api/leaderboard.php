<?php // basic functions and authentication
include( dirname(__FILE__) ."/header.php" );

if ( !($_SERVER['REQUEST_METHOD'] == "POST") )
    render_json_error( "Invalid Request" );
// parse the POST params
$user_id    = post_param( 'user_id' );
$gender     = post_param( 'gender' );
$location   = post_param( 'location' );
$rank       = post_param( 'rank' );
$offset     = post_param( 'offset' );
// and make sure everything is submitted
if ( !$user_id || !$location || $gender === false || $rank === false )
    render_json_error( "Invalid Request" );
// get the ID of the selected location
if ( $location === "base" ) {
    $school_id_query = mysql_query( 
        "SELECT school_id FROM users WHERE user_id = '$user_id'"
    );
    $school_id = mysql_fetch_assoc( $school_id_query )['school_id']; // get the school ID
} else if ( $location === "platoon" ) {
    $class_id_query = mysql_query( 
        "SELECT class_id FROM users WHERE user_id = '$user_id'"
    );
    $class_id = mysql_fetch_assoc( $class_id_query )['class_id']; // get the school ID
}

$leaderboard_sql = 
     " SELECT user_id, first, last, rank, medal_count, mission_count FROM users u "
    ." JOIN (SELECT user_id, MAX(rank_ord) AS rank, date_promoted FROM rank_marks GROUP BY user_id) rm USING (user_id) "
    ." JOIN (SELECT user_id, COUNT(*) AS medal_count FROM medal_marks GROUP BY user_id) mm USING (user_id) "
    ." JOIN (SELECT user_id, COUNT(*) AS mission_count FROM date_tasks_mission_marks GROUP BY user_id) dtmm USING (user_id) "
    ." WHERE u.user_registered IS NOT NULL ";
if ( $gender )
    $leaderboard_sql .= " AND u.gender = '$gender' ";
if ( $rank )
    $leaderboard_sql .= " AND rm.rank = '$rank' ";
// filter by location
if ( $location === "base" )
    $leaderboard_sql .= " AND u.school_id = '$school_id' ";
else if ( $location === "platoon" )
    $leaderboard_sql .= " AND u.class_id = '$class_id' ";
// sort and limit
$leaderboard_sql .= " ORDER BY rank DESC, medal_count DESC, mission_count DESC, date_promoted DESC;";

$leaderboard_query = mysql_query( $leaderboard_sql );
$leaderboard = fetch_results_assoc( $leaderboard_query ); // get the results

$user_location = 0;
foreach( $leaderboard as $index => $user ) {
    if ( $user['user_id'] === $user_id ) {
        $user_location = $index + 1;
        break;
    }   
}

$total = count( $leaderboard );
// Shimmy requested that we play with the numbers:
if ( $location === "army" && ( !$rank || $rank == 1 ) ) {
    $total += $gender ? 500 : 1000;
}

render_json_response([
    "leaderboard" => array_slice($leaderboard, intval($offset), 25),
    "user_location" => $user_location,
    "total" => $total
]);
