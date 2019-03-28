<?
$admin_auth = array('school'); 
require('header.php');
require_once 'class.adminSchools.php';
require_once 'class.schoolClasses.php';
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
        <h1>Teacher Names For Pushka</h1>
        
        <p class="info">Please Note: Maximum size allowed is 36 characters including spacing. Any characters entered past 36 will be removed.</p>
        <div align="center">
        	<button class="save">Save</button>
        </div>
        <?
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        //print_r( $schools );
        $schoolClasses = array();
		
        foreach ( $schools as $id => $school ) {
            $s = new SchoolClasses( $id );
			$classes = $s->getClasses();
            $schoolClasses[$id] = $classes;
        }

        foreach ( $schoolClasses as $school => $grades ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Teacher</th>";
			echo "<th>Hebrew Name for Pushka</th>";
            echo "</tr>";
			
			//get principal name
			$sql = "select he_name_principal, he_name_p2 from schools where school_id = " . $school;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			echo "<tr><td>Principal</td><td>Principal</td><td><input size='30' type='text' name='hname' class='keyboardInput principal' 
                    id='" . $school . "' value=\"" . $row['he_name_principal'] . "\" /></td></tr>";
			echo "<tr><td>Principal 2</td><td>Principal 2</td><td><input size='30' type='text' name='hname' class='keyboardInput principal2' 
                    id='" . $school . ":2' value=\"" . $row['he_name_p2'] . "\" /></td></tr>";
            foreach ( $grades as $grade ) {
            	$gr = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
            	echo "<tr><td>" . $gr . "</td><td>" . $grade['class_teacher'] . "</td><td>" . 
            	"<input size='30' type='text' name='hname' class='keyboardInput heb' 
                    id='" . $grade['class_id'] . "' value=\"" . $grade['teacher_hname'] . "\" /></td></tr>";
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
    		alert( p2 );
    		var school = $(".principal2").attr('id');
    		var pos = school.indexOf(':');
    		school = school.substring(0, pos);
    		$.post('ajax/updatePrincipal2Name.php', { school_id : school, he_name : p2});
    		
    		var saved = 0;
    		var num = $(".heb").length;
    		$(".heb").each( function() {
    			var id = $(this).attr('id');
    			var hname = $(this).val();
    			$.post('ajax/updateTeacherHeNames.php', { class_id : id, hname : hname }, function( success ) { saved++ });
    		});
    		setTimeout( function() {
    			if (saved == num) {
    				window.location = "teacherNamesPushka.php";
    			}
    		}, 500);
    	});
    </script>
</html>