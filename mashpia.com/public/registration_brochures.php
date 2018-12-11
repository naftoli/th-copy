<?
$admin_auth = array('school'); 
require('header.php');
?>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <style type='text/css'>
        table {
            font-size: 12px;
        }
        th, td {
            padding: 3px 10px;
        }
    </style>
    <body>
    	<table>
    		<tr>
    			<th>School</th>
    			<th>Brochures</th>
    			<th>Children Registered</th>
    		</tr>
<?
/*
//setup brochures database
$sql2 = "select school_id, count(*) as total 
		from users 
		where user_registered > 0 
		and school_id is not null 
		group by school_id 
		order by school_id";
$res2 = mysql_query( $sql2 );
while ( $row2 = mysql_fetch_assoc( $res2 ) ) {
	$total = $row2['total'];
	$extra = round( $total * 0.33, 0 );
	$qry = "update registration_brochures 
			set brochures = " . ($total + $extra) . " 
			where school_id = " . $row2['school_id'] . " 
			and year = '5774'";				
	mysql_query( $qry );
}
*/	
$total = 0;
$totalRegistered = 0;
$sql = "select * from registration_brochures 
		join schools using (school_id) 
		order by school_name";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['brochures'] . "</td>";
	$sql2 = "select count(*) as registered from users where school_id = " . $row['school_id'] . " and user_registered > 0";
	$result2 = mysql_query( $sql2 );
	$row2 = mysql_fetch_assoc( $result2 );
	echo "<td>" . $row2['registered'] . "</td></tr>";
	$total += $row['brochures'];
	$totalRegistered += $row2['registered'];
}
echo "<tr><td align='right'><b>Total:</b></td><td><b>" . $total . "</b></td><td><b>" . $totalRegistered . "</b></td></tr>";
?>
		</table>
	</body>
</html>