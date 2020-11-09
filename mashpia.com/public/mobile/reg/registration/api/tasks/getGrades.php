<?
include_once( dirname(__FILE__) . "/../header.php" );

if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    render_json_error( "Invalid Request", "Invalid Request Type. Expecting POST" );

$school_id = post_param( "school_id" );

if ( !$school_id )
    render_json_error( "Invalid Request", "Invalid Request Paramaters" );

$grades = [];
$sql = "select * from classes where school_id = " . $school_id . " order by class_grade, class_sub";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
  $grade = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
  $grades[$row['class_id']] = $grade;
}
json_response( $grades );
?>