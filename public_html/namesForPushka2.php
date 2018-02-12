<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Names For Pushka</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="mobile/reg/js/keyboard.js" charset="UTF-8"></script>
        <link rel="stylesheet" type="text/css" href="mobile/reg/css/keyboard.css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .heb {
            	text-align: right;
            }
            .info {
            	font-size: 16px;
            	font-weight: bold;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Names For Pushka</h1>
        
        <p class="info">Please Note: Maximum size allowed is 36 characters including spacing. Any characters entered past 36 will be removed.</p>
        <div align="center">
        	<button class="save">Save</button>
        </div>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolClasses.php';
        require_once 'class.schoolsUsers.php';      
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
		$schoolClasses = array();
        $schoolsUsers = array();
		
        foreach ( $schools as $id => $school ) {
        	$sc = new SchoolClasses( $id );
			$classes = $sc->getClasses();
            $schoolClasses[$id] = $classes;
			
            $su = new SchoolsUsers( $id );
			$users = $su->getUsers();
			$temp = array();
			foreach ( $users as $user ) {
				$reg = $user['user_registered'];
				if ($reg < '2015-10-21 10:55:00') continue;
				$temp[$reg][] = $user;
			}
			//ksort($temp);
            $schoolsUsers[$id] = $temp;
        }
		
		foreach ( $schoolClasses as $school => $grades ) {
			$sqlConf = "select conf_pushka_users from schools where school_id = " . $school;
			$resConf = mysql_query( $sqlConf );
			$rowConf = mysql_fetch_assoc( $resConf );
            echo "<h2>" . $schools[$school] . "</h2>";
			echo "<p><input type='checkbox' class='confirm' id='" . $school . "'";
			if ( $rowConf['conf_pushka_users'] ) echo " checked='checked'";
			echo " /> I take responsibility that all names are spelled correctly.";
			echo "<br />I understand that TH Headquarters will NOT be responsible for any spelling errors.</p>";
			
			echo "<h2>Staff</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Teacher</th><th>Hebrew Name for Pushka</th></tr>";
			
			//get principal name
			$sql = "select he_name_principal, he_name_p2 from schools where school_id = " . $school;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			echo "<tr><td>Principal</td><td>Principal</td><td><input size='30' type='text' name='hname' class='keyboardInput principal' 
                    id='" . $school . "' value=\"" . $row['he_name_principal'] . "\" /></td></tr>";
			echo "<tr><td>Principal 2</td><td>Principal 2</td><td><input size='30' type='text' name='hname' class='keyboardInput principal2' 
                    id='" . $school . ":2' value=\"" . $row['he_name_p2'] . "\" /></td></tr>";
            foreach ( $grades as $grade ) {
            	$gConf = "select conf_hname from classes where class_id = " . $grade['class_id'];
				$gRes = mysql_query( $gConf );
				$gRow = mysql_fetch_assoc( $gRes );
            	$gr = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
            	echo "<tr><td>" . $gr . "</td><td>" . $grade['class_teacher'] . "</td><td>" . 
            	"<input size='30' type='text' name='hname' class='keyboardInput hebT' 
                    id='" . $grade['class_id'] . "' value=\"" . $grade['teacher_hname'] . "\" /></td></tr>";
            }
            echo "</table><br />";			
			
			echo "<h2>Soldiers</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>First Name</th><th>Last Name</th><th>Registered Date</th>";
			echo "<th>Hebrew Name for Pushka</th></tr>";
            foreach ( $schoolsUsers[$school] as $info ) {
            	foreach ( $info as $user ) {
	            	$reg = $user['user_registered'];
	            	$hname = $user['he_name'];
	            	if ( empty($hname) ) $hname = $user['first_he'] . " " . $user['last_he'];
	                echo "<tr><td>" . $user['class_grade'] . ( empty( $user['class_sub'] ) ? '' : "-" . $user['class_sub'] ) . 
	                    "</td><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>" . $reg . "</td><td> 
	                    <input size='30' type='text' name='hname' class='keyboardInput heb' 
	                    id='" . $user['user_id'] . "' value=\"" . $hname . "\" /></td></tr>";
	            }
            }
            echo "</table><br />";			
        }
        ?>
        <div align="center">
        	<button class="save">Save</button>
        </div>
    </body>
    <script>
    	$(".save").click( function() {
    		var principal = $(".principal").val();
    		var school = $(".principal").attr('id');
    		$.post('ajax/updatePrincipalName.php', { school_id : school, he_name : principal });
    		
    		var p2 = $(".principal2").val();
    		var school = $(".principal2").attr('id');
    		var pos = school.indexOf(':');
    		school = school.substring(0, pos);
    		$.post('ajax/updatePrincipal2Name.php', { school_id : school, he_name : p2 });
    		
    		var savedT = 0;
    		var numT = $(".hebT").length;
    		$(".hebT").each( function() {
    			var id = $(this).attr('id');
    			var hname = $(this).val();
    			$.post('ajax/updateTeacherHeNames.php', { class_id : id, hname : hname }, function( success ) { savedT++ });
    		});
    		
    		var saved = 0;
    		var num = $(".heb").length;
    		$(".heb").each( function() {
    			var id = $(this).attr('id');
    			var hname = $(this).val();
    			$.post('ajax/updateHeNames.php', { user_id : id, hname : hname }, function( success ) { saved++ });
    		});
    		setTimeout( function() {
    			if (saved == num && savedT == numT) {
    				window.location = "namesForPushka.php";
    			}
    		}, 500);
    	});
    	
    	$(".toggle").click( function() {
    		$(".check").attr('checked', true);
    		$(".check").trigger('click');
    		$(".check").attr('checked', true);
    	});
    	
    	$(".check").click( function() {
    		var id = $(this).attr('id');
    		var checked = $(this).is(":checked");
    		var val;
    		if (checked) {
    			val = 1;
    		} else {
    			val = 0;
    		}
    		$.post('ajax/confirmPushkaName.php', { user : id, value : val });
    	});
    	
    	$(".toggleT").click( function() {
    		$(".checkT").attr('checked', true);
    		$(".checkT").trigger('click');
    		$(".checkT").attr('checked', true);
    	});
    	
    	$(".checkT").click( function() {
    		var id = $(this).attr('id');
    		var checked = $(this).is(":checked");
    		var val;
    		if (checked) {
    			val = 1;
    		} else {
    			val = 0;
    		}
    		$.post('ajax/confirmPushkaNameT.php', { grade : id, value : val });
    	});
    	
    	$(".confirm").click( function() {
    		var id = $(this).attr('id');
    		var checked = $(this).is(":checked");
    		var val;
    		if (checked) {
    			val = 1;
    		} else {
    			val = 0;
    		}
    		$.post('ajax/confirmPushkaNames.php', { school : id, value : val });
    		$(".save").trigger('click');
    	});
    </script>
</html>