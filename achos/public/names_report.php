<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Birthday Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript">
        	$( function() { 
        		$(".add").click( function() { 
        			var user_id = $(this).find('input[type=checkbox]').attr('name');
        			//alert( user_id );	      			
        			if ( $(this).find('input').is(':checked') ) {
        				//run script to add one to hebrew dob
        				$.post( 'ajax/hebrewDateAdd.php', {user_id : user_id}, function(data){
        					//alert( data );
        					location.reload();
        				});
        			}        			
        		});
        		
        		$(".subtract").click( function() { 
        			var user_id = $(this).find('input[type=checkbox]').attr('name');
        			//alert( user_id );
        			if ( $(this).find('input').is(':checked') ) {
        				//run script to subtract one from hebrew dob
        				$.post( 'ajax/hebrewDateSubtract.php', {user_id : user_id}, function(data){
        					//alert( data );
        					location.reload();
        				});
        			}        			
        		});
        	});
        </script>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Birthday Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers();
        }
        
        foreach ( $schoolsUsers as $school => $users ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>First Name</th><th>Last Name</th>";
			echo "<th>First Hebrew Name</th><th>Last Hebrew Name</th>";
            echo "<th>English DOB</th><th>Hebrew DOB</th>";
            echo "<th>Add Day to Hebrew DOB</th>";
            echo "<th>Subtract Day from Hebrew DOB</th>";
            echo "</tr>";
            foreach ( $users as $user ) {
                echo "<tr><td>" . $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] ) . 
                    "</td><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>" . 
                    $user['first_he'] . "</td><td>" . $user['last_he'] . "</td><td>" . 
                    $user['dob'] . "</td><td>" . $user['dob_he'] . "</td>";
				if ( !empty( $user['dob_he'] ) ) {
	           		echo "<td class='add'>" . 
	                    "<input type='checkbox' name='" . $user['user_id'] . "' /></td><td class='subtract'>
	                    <input type='checkbox' name='" . $user['user_id'] . "' /></td>";
	            }
                echo "</tr>";
            }
            echo "</table><br />";
        }
        ?>
    </body>
</html>