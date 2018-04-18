<?php // basic functions and authentication
include( dirname(__FILE__) ."/header.php" );

if ( !($_SERVER['REQUEST_METHOD'] == "POST") )
    render_json_error( "Invalid Request" );
// parse the POST params
$user_id    = post_param( 'user_id' );
$gender     = post_param( 'gender' );
$location   = post_param( 'location' );
$rank       = post_param( 'rank' );
// and make sure everything is submitted
if ( !$user_id || !$location || $gender === false || $rank === false )
    render_json_error( "Invalid Request" );

$leaderboard_sql = 
     " SELECT first, last, rank, medal_count, mission_count FROM users u "
    ." JOIN (SELECT user_id, MAX(rank_ord) AS rank, date_promoted FROM rank_marks GROUP BY user_id) rm USING (user_id) "
    ." JOIN (SELECT user_id, COUNT(*) AS medal_count FROM medal_marks GROUP BY user_id) mm USING (user_id) "
    ." JOIN (SELECT user_id, COUNT(*) AS mission_count FROM date_tasks_mission_marks GROUP BY user_id) dtmm USING (user_id) "
    ." WHERE u.user_registered IS NOT NULL ";
if ( $gender )
    $leaderboard_sql .= " AND u.gender = '$gender' ";
if ( $rank )
    $leaderboard_sql .= " AND rank = '$rank' ";
// sort and limit
$leaderboard_sql .= " ORDER BY rank DESC, medal_count DESC, mission_count DESC, date_promoted DESC LIMIT 100;";

$leaderboard_query = mysql_query( $leaderboard_sql );
$leaderboard = fetch_results_assoc( $leaderboard_query ); // get the results

render_json_response( $leaderboard );
