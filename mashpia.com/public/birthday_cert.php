<?
require 'db.php';
require 'class.globalSettings.php';
$jYear = GlobalSettings::getCurrentYear();

//echo "<pre>"; print_r( $_POST ); echo "</pre>";
$parshaDates = array();
if ( isset( $_GET['school'] ) ) {
    $school_id = $_GET['school'];
    $class_id = $_GET['class'];
    $user_id = $_GET['user'];
    $parsha = $_GET['parsha'];
    $gender = $_GET['gender'];
    $dates = explode( ":", $parsha );
    $start = $dates[0];
    $end = $dates[1];
	$parshaDates[$start] = $parsha;
} else if ( isset( $_POST['schools'] ) ) {
    $schools = explode( ':', $_POST['schools'] );
    $parshas = explode( ':', $_POST['parshas'] );
	$gender = $_POST['gender'];
    //get dates
    $dates = array();
    $numDates = 0;
    $sqlReport = "select start, end, name from parshos where end in (" . implode( ',', $parshas ) . ")";
    $resultReport = mysql_query( $sqlReport );
    while ( $rowReport = mysql_fetch_assoc( $resultReport ) ) {
        $dates['start'][] = $rowReport['start'];
        $dates['end'][] = $rowReport['end'];
        $parshaDates[$rowReport['start']] = $rowReport['name'];
        $numDates++;
    }
}

$names = [];
if ( isset( $school_id ) ) {
    $sql = "select u.user_id, u.first, u.last, u.first_he, u.last_he, u.he_name, c.class_grade, c.class_sub, s.school_name, u.dob  
            from users u 
            join schools s on (s.school_id = u.school_id) 
            join classes c on (c.class_id = u.class_id)         
            where u.user_registered > 0 
            and u.dob > 0 "; 
    if ( $school_id > 0 ) {
        $sql .= " and u.school_id = $school_id ";
    }
    if ( $class_id > 0 ) {
        $sql .= " and u.class_id = " . $class_id;
    }
    if ( $user_id > 0 ) {
        $sql .= " and u.user_id = " . $user_id;
    }
    if ( $gender == 'm' ) {
        $sql .= " and school_type_id in (2,12) ";
    } else if ( $gender == 'f' ) {
        $sql .= " and school_type_id in (3,13) ";
    }
    $sql .= " order by c.class_grade, c.class_sub, u.last, u.first";
} else {
    $sql = "select u.user_id, u.first, u.last, u.first_he, u.last_he, u.he_name, c.class_grade, c.class_sub, s.school_name, u.dob  
            from users u 
            join schools s on (s.school_id = u.school_id) 
            join classes c on (c.class_id = u.class_id)         
            where u.user_registered > 0 
            and u.dob > 0 
            and u.school_id in (" . implode( ',', $schools ) . ")"; 
     if ( $gender == 'm' ) {
        $sql .= " and (gender = 'M' or gender = 'm') ";
     } else if ( $gender == 'f' ) {
        $sql .= " and (gender = 'F' or gender = 'f') ";
     }
	 $sql .= "order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
     //echo $sql;
}
//echo $sql; exit;

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
	
	//find the english year corresponding to hebrew birthday
	$bd = jdtogregorian($jdNow);
	$bdArr = explode('/', $bd);
	$age = ($bdArr[2] - $dob[0]);
	/*
	if ( $row['user_id'] == 17100 ) {
		echo "<pre>"; print_r( $dob ); echo "</pre>";
		echo $jd . "<br />";
		echo $jewish . "<br />";
		echo $jdNow . "<br />";
		echo $start . ' - ' . $end;
	}
	 * 
	 */ 
    
    if ( isset( $school_id ) ) {
        //if hebrew date within range then we are good and add user to array otherwise don't add to array
        if ( $jdNow >= $start && $jdNow <= $end ) {
            $grade = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
			$heName = empty( $row['he_name'] ) ? 
				(( empty( $row['first_he'] ) && empty( $row['last_he'] )) ? $row['first'] . ' ' . $row['last'] :
				$row['first_he'] . '  ' . $row['last_he']) : $row['he_name'];
			$names[$row['school_name']][$grade][$jNow][$parsha][] = array( 'age' => $age, 'name' => $heName );
        }
    } else {
        //loop through dates array to check if hebrew jd birthday falls within any of them
        $found = false;
        for ( $i = 0; $i < $numDates; $i++ ) {
            if ( $jdNow >= $dates['start'][$i] && $jdNow <= $dates['end'][$i] ) {
                $found = true;
                break;
            }
        }
        if ( $found ) {
            $grade = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
			$heName = empty( $row['he_name'] ) ? 
				(( empty( $row['first_he'] ) && empty( $row['last_he'] )) ? $row['first'] . ' ' . $row['last'] :
				$row['first_he'] . ' ' . $row['last_he']) : $row['he_name'];
			$names[$row['school_name']][$grade][$jNow][$parshaDates[$dates['start'][$i]]][] = array( 'age' => $age, 'name' => $heName );
        }
    }
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            @font-face {
                font-family: DirtyEgo;
                src: url('fonts/DIRTYEGO.TTF');
            }
            .page {
                height: 15cm;
            }
            .name {
                margin-left: 2.6cm;
                width: 6cm;
                font-size: 30px;
                font-weight: bold;
                text-align: center;
                font-family: DirtyEgo;
                transform: rotate(180deg);
            }
            .grade {
                font-size: 12px;
                font-weight: normal;
            }
            .page-break {
                page-break-after: always;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
            @media screen {
                .no-print { 
                    margin-left: 38%;
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 12px;
                }
                .print {
                    margin-left: 16%;
                }
            }
        </style>
    </head>
    
    <body>
        <?
        if ( empty( $names ) ) {
            echo "Sorry there are no names that meet your criteria, please go back and revise the options.";
            exit;
        }
        ?>
        <div class="no-print">
            <p>Printing Instructions:<br />
            Step 1: Set the Orientation to <u>Portrait</u><br />
            Step 2: Check 'Shrink to fit Page Width'<br />
            Step 3: In Options check 'Print Background (colors & images)'<br />
            Step 4: In the second tab set all Margins to 0.0 inches (All Sides)<br />
            Step 5: Set all Headers & Footers to Blank</p>
            <p class='print'>
                <input type="button" value="Print" onclick="window.print()" />
            </p>
        </div>
        <? 
        //echo "<pre>"; print_r( $names ); echo "</pre>";
        foreach ( $names as $school => $info ) {
            foreach ( $info as $grade => $dates ) {
	        	foreach ( $dates as $date => $other ) {
	        		foreach ( $other as $parsha => $children ) {
	                    foreach ( $children as $child ) {
	                        ?> 
	                        <div class="page"></div>
	                        <div class="name">
	                            <?=$child['name']?><br />
	                            <span class="grade">
	                                <?=$school?> Platoon: <?=$grade?> - <?=$child['age']?> yrs. old
	                                <br /><?=$date?> - <?=$parsha?>
	                            </span>
	                        </div>
	                        <div class="page-break"></div>
	                        <?
	                    } 
	                }
				}
            }
        }
        ?>
    </body>
</html>
