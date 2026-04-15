<?php
ini_set('display_errors',1);
require_once( dirname(__FILE__) . '/../../db.php' );
require_once( dirname(__FILE__) . '/../../class.globalSettings.php' );
$year = GlobalSettings::getCurrentYear();

//**************** PARSE POST PARAMS ****************//
$level  = isset( $_POST['level'] )  ? mysql_real_escape_string( $_POST['level'] )   : false;
$grade  = isset( $_POST['grade'] )  ? mysql_real_escape_string( $_POST['grade'] )   : false;
$id     = isset( $_POST['id'] )     ? mysql_real_escape_string( $_POST['id'] )      : false;
// are we only showing one grade or all grades?
$byGrade =  $grade && $grade != 'false';

$cache_key = "world:lines:{$year}:{$level}:" . ( $byGrade ? $grade : 'all' ) . ":{$id}";
$rows = Cache::remember( $cache_key, 120, function () use ( $year, $level, $byGrade, $grade, $id ) {
    $campaigns = [];
    $campaign_query = mysql_query(
        "SELECT * FROM line_campaigns WHERE year = " . $year
    );
    while ( $row = mysql_fetch_assoc( $campaign_query ) ) {
    	$campaigns[$row['id']] = strtolower( $row['type'] );
    }

    $rows = [];
    if ( $level == 1 ) {
        require_once( dirname(__FILE__) . "/functions/school_lines.php" );
        $rows = school_lines( $campaigns, $byGrade ? $grade : false );
    } elseif ( $level == 2 ) {
        require_once( dirname(__FILE__) . "/functions/grade_lines.php" );
        $rows = grade_lines( $campaigns, $id );
    } elseif ( $level == 3 ) {
        require_once( dirname(__FILE__) . "/functions/student_lines.php" );
        $rows = student_lines( $campaigns, $id );
    }

    return $rows;
} );

echo json_encode( $rows );