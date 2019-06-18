<?
include_once( dirname(__FILE__) . "/../header.php" );

if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    render_json_error( "Invalid Request", "Invalid Request Type. Expecting POST" );

$schools = [];
$sql = "select school_id, school_name from schools 
        where chidon = 1 and test_school = 0 
        order by school_name";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['school_name']] = $row['school_id'];
}
json_response( $schools );
?>