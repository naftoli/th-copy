<?
//print_r( $_GET );
$school_id = $_GET['school'];
$class_id = $_GET['class'];
$user_id = $_GET['user'];

$types = '';
if (isset($_GET['gender'])) {
	$gender = $_GET['gender'];
	switch ($gender) {
		case 'm':
			$types = "2,12";
			break;
		case 'f':
			$types = "3,13";
	}
}

require 'db.php';

$names = array();
$sql = "select first, last, first_he, last_he, he_name, class_grade, class_sub, school_name    
        from users u 
        join schools s using (school_id) 
        join classes c on u.class_id = c.class_id  
        # where u.school_id = $school_id 
        where u.user_registered > 0";
if (!empty($types)) {
	$sql .= " and school_type_id in (" . $types . ")";
}
if ( $class_id > 0 ) {
    $sql .= " and class_id = " . $class_id;
}
if ( $user_id > 0 ) {
    $sql .= " and user_id = " . $user_id;
}
$sql .= " order by c.class_grade, c.class_sub, last, first";
//echo $sql;

$temp = '';
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	$school = $row['school_name'];
	$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	if (empty($temp)) {
		$names[] = $school . "<br />" . $grade;
		$temp = $grade;
	} else if ($temp != $grade) {
		$names[] = $school . "<br />" . $grade;
		$temp = $grade;
	}
	
	$heName = empty( $row['he_name'] ) ? $row['first_he'] . '<br />' . $row['last_he'] : $row['he_name'];
	$names[] = empty( $heName ) ? $row['first'] . '<br />' . $row['last'] : $heName;
}
//echo "<pre>"; print_r($names); echo "</pre>"; exit;
?>
<html>
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            @font-face {
                font-family: DirtyEgo;
                src: url('fonts/DIRTYEGO.TTF');
                
            }
            @font-face {
                font-family: Gothic;
                src: url('fonts/HWYGWDE.TTF');
                
            }
            .page {
                width: 11in.;
                height: 6.5cm;
            }
            .name {
                margin: auto;
                width: 16cm;
                font-size: 90px;
                font-weight: bold;
                text-align: center;
                font-family: DirtyEgo;
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
        <div class="no-print">
            <p>Printing Instructions:<br />
            Step 1: Set the Orientation to <u>Landscape</u><br />
            Step 2: Check 'Shrink to fit Page Width'<br />
            Step 3: In Options check 'Print Background (colors & images)'<br />
            Step 4: In the second tab set all Margins to 0.0 inches (All Sides)<br />
            Step 5: Set all Headers & Footers to Blank</p>
            <p class='print'>
                <input type="button" value="Print" onclick="window.print()" />
            </p>
        </div>
        <? foreach ( $names as $name ) { ?> 
        <div class="page"></div>
        <div class="name">
            <?=$name?>
        </div>
        <div class="page-break"></div>
        <? } ?>
    </body>
</html>
