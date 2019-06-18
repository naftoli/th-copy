<?
include_once( dirname(__FILE__) . "/../header.php" );

if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    render_json_error( "Invalid Request", "Invalid Request Type. Expecting POST" );

$class_id = post_param( "class_id" );

if ( !$class_id )
    render_json_error( "Invalid Request", "Invalid Request Paramaters" );

$users = [];
$sql = "select user_id, first, last from users where class_id = " . $class_id . " order by last, first";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
  $name = $row['first'] . ' ' . $row['last'];
  $users[$name] = $row['user_id'];
}
json_response( $users );
?>