<?
$admin_auth = array('school'); 
require('header.php'); 

$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = 10909")), 0));
echo $cur_points;

$points = header_icorpa_points_multi( array('user_code' => array(8460190758922783744)) );
echo "<pre>"; print_r( $points ); echo "</pre>";
?>