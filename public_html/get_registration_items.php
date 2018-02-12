<?php
include("db.php");
//include("check_admin_id.php");

Class getEligiblePackagesPerStudent
{
	protected	$user_id;
	
	function __construct(){		
	}
	
	function determine_package($user_id)
	{
		$sql = " SELECT * FROM users u " .
			   " LEFT JOIN schools s ON u.school_id = s.school_id" .
			   " LEFT JOIN classes c ON u.class_id = c.class_id" .	   
			   " LEFT JOIN admin_auths a ON u.user_id = a.id and  auth='user' and role_id = 1" .	   			   
			   " WHERE user_id= " . $user_id ; 
		echo $sql;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		echo "<br>User ID = "  . $row['user_id'] ;
		echo "<br>Class ID = "  . $row['class_id'] ;
		echo "<br>School ID = "  . $row['school_id'] ;
		echo "<br>School Name = "  . $row['school_name'] ;
		echo "<br>Class Grade = "  . $row['class_grade'] ;
		echo "<br>" ;
		echo "<br>Add on One = "  . $row['add_on_one'] ; 
		echo "<br>Add on Two = "  . $row['add_on_two'] ;
		echo "<br>Parent = "  . $row['admin_id'] ;
		echo "<br>* where option 1=don't add, 2=add to all grades, 3=certain grades, 4=parents decide<br>";
		
		$arr = array();
		$arr[0][0] = "36";

		// -----------------------------------------
		// for add-on ONE	
		// -----------------------------------------
		switch ($row['add_on_one']) {
			case 1:
				// do nothing
				echo "<br>case 1: - do not add add-on one<br>";
				break;
			case 2:
				echo "<br>case 2<br>";
				$arr[1][0] = "50";
				$arr[1][1] = "includes album and folder";
				break;
			case 3:
				echo "case 3 - check school add-on grade";
				$sql2 = " SELECT * FROM school_add_on_grades " .
						" WHERE school_id = " . $row['school_id'] .
						" and add_on_number = 1 " .
						" and grade = " . $row['class_grade'] ;
				echo "<br>" . $sql2 . "<br>";
				$query2 = mysql_query($sql2);
				if($query2){					
					$row2 = mysql_fetch_assoc($query2);
					$arr[1][0] = "50";
					$arr[1][1] = "includes album and folder";
				}				
				break;
			case 4:
				echo "case 4 - parents decide, dont show it here";
					// do nothing here
				break;								
		}
		
		// -----------------------------------------
		// for add-on TWO
		// -----------------------------------------
		switch ($row['add_on_two']) {
			case 1:
				// do nothing
				echo "<br>case 1: - do not add add-on one<br>";
				break;
			case 2:
				echo "<br>case 2<br>";
				if ($row['class_grade'] > 2 && $row['class_grade'] < 8 )
				{	
					$arr[2][0] = "50";
					$arr[2][1] = "option 2";
				}
				break;
			case 3:
				echo "case 3 - check school add-on grade";
				$sql2 = " SELECT * FROM school_add_on_grades " .
						" WHERE school_id = " . $row['school_id'] .
						" and add_on_number = 2 " .
						" and grade = " . $row['class_grade'] ;
				echo "<br>" . $sql2 . "<br>";
				$query2 = mysql_query($sql2);
				if($query2){					
					$row2 = mysql_fetch_assoc($query2);
					$arr[2][0] = "50";
					$arr[2][1] = "option 2";
				}				
				break;
			case 4:
				echo "case 4 - parents decide, dont show it here";
					// do nothing here
				break;								
		}
		
		return $arr;
		}
		
}

$user_id = $_GET['user'];
$myclass = new getEligiblePackagesPerStudent();
$arr = $myclass->determine_package($user_id);

echo "<br><br>Items for this student:<br><br>";

//print_r($arr);
foreach($arr as $value){
	echo ">>" . $value[0]." ". $value[1] . "<br>";
}






