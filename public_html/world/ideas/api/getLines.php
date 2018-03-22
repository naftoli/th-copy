<?php
ini_set('display_errors',1);
require_once( $_SERVER['DOCUMENT_ROOT'] . '/db.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php' );


$year = GlobalSettings::getChidonYear();

$campaign_query = mysql_query(
    "SELECT * FROM line_campaigns WHERE year = " . $year
);
while ( $row = mysql_fetch_assoc( $campaign_query ) ) {
	$campaigns[$row['id']] = strtolower( $row['type'] );
}

//**************** PARSE POST PARAMS ****************//
$level      = isset( $_POST['level'] )      ? mysql_real_escape_string( $_POST['level'] )       : false;
$grade      = isset( $_POST['grade'] )      ? mysql_real_escape_string( $_POST['grade'] )       : false;
$school_id  = isset( $_POST['school_id'] )  ? mysql_real_escape_string( $_POST['school_id'] )   : false;
$class_id   = isset( $_POST['class_id'] )   ? mysql_real_escape_string( $_POST['class_id'] )    : false;
// are we only showing one grade or all grades?
$byGrade =  $grade && $grade != 'false';

//**************** SCHOOL LEVEL REPORT ****************//
$rows = [];
if ( $level == 1 ) {
    require_once( dirname(__FILE__) . "/functions/school_lines.php" );
    $rows = school_lines( $campaigns, $byGrade ? $grade : false );
} elseif ( $level == 2 ) {

} elseif ( $level == 3 ) {
    
}

echo json_encode( $rows );