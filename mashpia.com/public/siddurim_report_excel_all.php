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
    </style>
    <body>
		<?
		$grades = array();
		$details = array();
		$i = 1;
		$schools = array();
		$sql = "SELECT s.school_id, s.school_name, c.class_id, c.class_grade, c.class_sub, c.class_teacher
				FROM classes c
				JOIN schools s
				USING ( school_id ) 
				ORDER BY school_name, class_grade, class_sub";
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc($result) ) {
			$school_id = $row['school_id'];
			$school = $row['school_name'];
			$class_id = $row['class_id'];
			$class = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
			$teacher = $row['class_teacher'];						
			$grades[$school][$class_id][$class] = $teacher;
		}
		
		foreach( $grades as $school => $classes ) {
			foreach( $classes as $class_id => $info ) {
				foreach( $info as $class => $teacher ) { 
					$sql = "select * from user_add_ons 
							join users using (user_id) 
							where user_id in (
								select user_id from users where class_id = $class_id
							)
							and school_add_on_id = 11";
					$result = mysql_query( $sql );
					while ( $row = mysql_fetch_assoc( $result ) ) {
						$gender = $row['gender'];
						$details[$school][$class_id][$class][] = array('gender' => $gender, 
																		'fname' => $row['first'], 
																		'lname' => $row['last']);
					}
				}
			}
		}
		?>
		<table>
			<tr>
				<th>Blue</th>
				<th>Purple</th>
				<th>Last Name</th>
				<th>First Name</th>
				<th>School</th>
				<th>Grade</th>
			</tr>
			<? 
			$blue = 0;
			$purple = 0;
			$showDetails = false;
			$prevSchool = "";
			foreach( $grades as $school => $classes ) {
				if ( $prevSchool == "" ) {
					$prevSchool = $school;
				} 
				if ( $prevSchool != $school ) {
					$prevSchool = $school;
					$showDetails = true;
				} else {
					$showDetails = false;
				}
				
				foreach( $classes as $class_id => $class ) {
					foreach( $class as $grade => $teacher ) {
						$teacher = ucwords( strtolower( $teacher ) );
						if ( strstr($teacher, 'Rabbi') ) {
							echo "<tr><td>1</td><td>&nbsp;</td>";
							$blue++;
						} else {
							echo "<tr><td>&nbsp;</td><td>1</td>";
							$purple++;
						}
						$pos = strrpos($teacher, ' ');
						$first = substr($teacher, 0, $pos);
						$last = substr($teacher, ($pos+1));
						echo "<td>" . $last . "</td><td>" . $first . "</td><td>" . 
							$school . "</td><td>" . $grade . " - Teacher</td></tr>";
					}
				}
				
				if ( $showDetails ) {
					if ( isset( $details[$school] ) ) { 
						foreach( $details[$school] as $class_id => $class ) {
							foreach( $class as $grade => $info ) {
								foreach( $info as $user ) {
									$gender = strtolower( $user['gender'] );
									$first = $user['fname'];
									$last = $user['lname'];
									if ( $gender == 'm' ) {
										echo "<tr><td>1</td><td>&nbsp;</td>";
										$blue++;
									} else {
										echo "<tr><td>&nbsp;</td><td>1</td>";
										$purple++;
									}
									echo "<td>" . $last . "</td><td>" . $first . "</td><td>" . 
										$school . "</td><td>" . $grade . "</td></tr>";
								}
							}
						}
					}
					echo "<tr><td colspan='6'>&nbsp;</td></tr>";
				}
			}
			echo "<tr><td>" . $blue . "</td><td>" . $purple . "</td><td colspan='4'>&nbsp;</td></tr>";
			?>
		</table>
	</body>
</html>