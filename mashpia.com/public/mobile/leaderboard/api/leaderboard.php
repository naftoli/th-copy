<?php // basic functions and authentication
include( dirname(__FILE__) ."/header.php" );

if ( !($_SERVER['REQUEST_METHOD'] == "POST") )
    render_json_error( "Invalid Request" );

// parse the POST params
$user_id   = post_param( 'user_id' );
$gender    = post_param( 'gender' );
$location  = post_param( 'location' );
$rank      = post_param( 'rank' );
$offset    = intval( post_param( 'offset' ) );

// and make sure everything is submitted
if ( !$user_id || !$location || $gender === false || $rank === false )
    render_json_error( "Invalid Request" );

// resolve leaderboard scope
$scope_id = 'all';
if ( $location === "base" ) {
    $school_id_query = mysql_query(
        "SELECT school_id FROM users WHERE user_id = '$user_id'"
    );
    $scope_id = mysql_fetch_assoc( $school_id_query )['school_id'];
} else if ( $location === "platoon" ) {
    $class_id_query = mysql_query(
        "SELECT class_id FROM users WHERE user_id = '$user_id'"
    );
    $scope_id = mysql_fetch_assoc( $class_id_query )['class_id'];
}

$cache_key = "leaderboard:list:{$location}:{$scope_id}:{$gender}:{$rank}";

$leaderboard = Cache::remember($cache_key, 120, function () use ($gender, $location, $rank, $scope_id) {
    $leaderboard_sql =
         " SELECT user_id, first, last, first_he, last_he, rank, medal_count, mission_count FROM users u "
        ." JOIN (SELECT user_id, MAX(rank_ord) AS rank, date_promoted FROM rank_marks GROUP BY user_id) rm USING (user_id) "
        ." JOIN (SELECT user_id, COUNT(*) AS medal_count FROM medal_marks GROUP BY user_id) mm USING (user_id) "
        ." JOIN (SELECT user_id, SUM( mission_count ) AS mission_count FROM date_tasks_mission_marks GROUP BY user_id) dtmm USING (user_id) "
        ." WHERE u.user_registered IS NOT NULL ";

    $leaderboard_sql .= "AND user_id not in (59350,59351) ";

    if ( $gender )
        $leaderboard_sql .= " AND u.gender = '$gender' ";

    if ( $rank )
        $leaderboard_sql .= " AND rm.rank = '$rank' ";

    if ( $location === "base" )
        $leaderboard_sql .= " AND u.school_id = '$scope_id' ";
    else if ( $location === "platoon" )
        $leaderboard_sql .= " AND u.class_id = '$scope_id' ";

    $leaderboard_sql .= " ORDER BY rank DESC, medal_count DESC, mission_count DESC, date_promoted DESC;";

    $leaderboard_query = mysql_query( $leaderboard_sql );
    return fetch_results_assoc( $leaderboard_query );
});

$user_location = 0;
foreach ( $leaderboard as $index => $user ) {
    if ( $user['user_id'] == $user_id ) {
        $user_location = $index + 1;
        break;
    }
}

$total = count( $leaderboard );
if ( $location === "army" && ( !$rank || $rank == 1 ) ) {
    $total += $gender ? 500 : 1000;
}

render_json_response([
    "leaderboard" => array_slice($leaderboard, $offset, 24),
    "user_location" => $user_location,
    "total" => $total
]);