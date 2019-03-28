<?php
require 'db.php';

$jYear = 5777;
$sql = "select u.user_id, u.first, u.last, u.first_he, u.last_he, u.he_name, c.class_grade, c.class_sub, s.school_name, u.dob  
        from users u 
        join schools s on (s.school_id = u.school_id) 
        join classes c on (c.class_id = u.class_id)         
        where u.user_registered > 0 
        and u.dob > 0 
        and u.school_id in (61,269) 
        order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
//echo $sql; 

$info = array();
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    //check users dob to see if within dates range
    //get month and day of hebrew birthday
    $dob = explode( '-', $row['dob'] );
    if ( $dob[1] == 0 || $dob[2] == 0 ) continue;
    $jd = gregoriantojd( $dob[1], $dob[2], $dob[0] );
    $jewish = jdtojewish( $jd );
    $j = explode( '/', $jewish );
    $jMonth = $j[0];
    $jDay = $j[1];
    //find this year's jd equivalent of hebrew birthday
    $jdNow = jewishtojd( $jMonth, $jDay, $jYear );
    $jewishNow = jdtojewish( $jdNow, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH );
    $jNow = iconv('WINDOWS-1255', 'UTF-8', $jewishNow);
	$age = (date('Y') - $dob[0] + 1); 
	
	$beginning = 2457277;
	$end = 2457661;
	
	if ($jdNow >= $beginning && $jdNow <= $end) {
		if (isset($info[$row['school_name']][$age])) $info[$row['school_name']][$age]++;
		else $info[$row['school_name']][$age] = 1;	
	}
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<html>
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            th, td {
            	padding: 5px;
            	font-size: 12px;
            }
        </style>
    </head>
    
    <body>
        <?
        foreach ($info as $school => $other) {
        	echo "<table><caption>" . $school . "</caption>";
			echo "<tr><th>Age</th><th>Total</th></tr>";
			foreach ($other as $age => $total) {
				echo "<tr><td>" . $age . "</td><td>" . $total . "</td></tr>";
			}
			echo "</table><br /><br />";	
        }
        ?>
    </body>
</html>






