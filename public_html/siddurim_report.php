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
            border: 1px solid black;
        }
        .new-page {
        	page-break-after: always;
        }
    </style>
    <body>
		<?
		$grades = array();
		$details = array();
		$i = 1;
		$schools = array();
		$sql = "SELECT s.school_id, s.school_name, s.school_gender, c.class_id, c.class_grade, c.class_sub, c.class_teacher
				FROM classes c
				JOIN schools s
				USING ( school_id ) 
				WHERE c.class_era =0
				AND s.school_era IS NULL 
				ORDER BY school_name, class_grade, class_sub";
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc($result) ) {
			$school_id = $row['school_id'];
			$school = $row['school_name'];
			$gender = $row['school_gender'];
			$class_id = $row['class_id'];
			$class = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
			$teacher = $row['class_teacher'];						
			$sql2 = "select * from user_add_ons 
					join users using (user_id) 
					where user_id in (
						select user_id from users where class_id = $class_id
					)
					and school_add_on_id = 11";
			$result2 = mysql_query( $sql2 );
			$num = mysql_num_rows( $result2 );
			if ( $num ) {
				while( $row2 = mysql_fetch_assoc( $result2 ) ) {
					$name = $row2['first'] . " " . $row2['last'];
					$g = $row2['gender'];
					//echo "<tr><td>" . $i++ . "</td><td>" . $school . "</td><td>" . $gender . "</td><td>" . $class . 
					"</td><td>" . $teacher . "</td><td>" . $name . "</td><td>" . $g . "</td></tr>";
					$grades[$school][$gender][$class][$teacher][$name] = $g;
					$details[$school][$class][$name] = $g;
				}
			} else {
				//echo "<tr><td>" . $i++ . "</td><td>" . $school . "</td><td>" . $gender . "</td><td>" . $class . 
					"</td><td>" . $teacher . "</td><td colspan='2'>&nbsp;</td></tr>";
					$grades[$school][$gender][$class][$teacher] = array();
			}
			$schools[$school_id] = $school;	
		}

		foreach( $grades as $school => $info ) {
			foreach( $info as $schoolGender => $classes ) {
				echo "<table>";
				echo "<tr><th colspan='3'>$school</th></tr>";
				echo "<tr><th>Class</th><th>Teacher</th><th>Total</th></tr>"; 
				foreach( $classes as $class => $teachers ) {
					foreach( $teachers as $teacher => $names ) {
						echo "<tr><td>" . $class . "</td><td>" . $teacher . "</td><td>" . count( $names ) . "</td></tr>";
					}
				}
				echo "</table><br /><br />";
			}
			if ( isset( $details[$school] ) ) {
				echo "<table>";
				echo "<tr><th colspan='3'>$school Details</th></tr>";
				echo "<tr><th>Class</th><th>Name</th><th>Gender</th></tr>";
				foreach( $details[$school] as $class => $names ) {
					foreach( $names as $name => $gender ) {
						echo "<tr><td>" . $class . "</td><td>" . $name . "</td><td>" . $gender . "</td></tr>";
					}
				}
				echo "</table><div class='new-page'></div><br /><br />";
			} 
			$school_id = array_search( $school, $schools );
			include 'get_siddurim_info.php';
			if ( $blue || $purple ) {
				echo "<table>";
				echo "<tr><th colspan='2'>$school Additional Purchases</th></tr>";
				if ( $blue ) {
					echo "<tr><td>Blue</td><td>" . $blue . "</td></tr>";
				}
				if ( $purple ) {
					echo "<tr><td>Purple</td><td>" . $purple . "</td></tr>";
				}
				echo "</table><div class='new-page'></div><br /><br />";
			}
		}
		?>
	</body>
</html>