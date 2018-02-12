<?
$sql1 = "select count(*) as total from user_add_ons uao 
		join users u using (user_id) 
		where school_id = $school_id 
		and uao.school_add_on_id = 11 
		and u.gender = 'm'";
$result1 = mysql_query( $sql1 );
$row1 = mysql_fetch_assoc($result1);
$boys = $row1['total'];

$sql2 = "select count(*) as total from user_add_ons uao 
		join users u using (user_id) 
		where school_id = $school_id 
		and uao.school_add_on_id = 11 
		and u.gender = 'f'";
$result2 = mysql_query( $sql2 );
$row2 = mysql_fetch_assoc($result2);
$girls = $row2['total'];

//find out if school has already purchased any extra siddurim
$sql3 = "select description
        from siddur_purchases 
        where school_id = " . $school_id;
$result3 = mysql_query( $sql3 );

$blue = 0;
$purple = 0;
while ( $row3 = mysql_fetch_assoc( $result3 ) ) {
	$description = $row3['description'];
	if ( $i = strpos($description, ':') ) {
		//school purchased extra blue and purple
		$blueArr = explode(' ', substr($description, 0, $i));
		$purpleArr = explode(' ', substr($description, ($i+1)));
		$blue += $blueArr[3];
		$purple += $purpleArr[3];
	} else {
		$descriptionArr = explode(' ', $description);
		$color = $descriptionArr[4];
		$$color += $descriptionArr[3];
	}
}
?>