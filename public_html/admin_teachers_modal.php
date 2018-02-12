<?
$admin_auth = array('school'); 
require('header.php');

$school_id = $_GET['id'];
require_once 'class.schoolClasses.php';
$s = new SchoolClasses($school_id);
$grades = $s->getClasses();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Class Teacher</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
	        body {
        		background: none;
        	}
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
	</head>
	
	<body>
		<h2>Class Teacher Info</h2>
		<table>
			<?
			foreach ($grades as $grade) {
				$g = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
				echo "<tr><td>" . $g . "</td><td><input type='text' id='" . $grade['class_id'] . "' class='teacher' value='" . $grade['class_teacher'] . "' /></td></tr>";
			}
			?>
		</table>
		<h2></h2>
        <table>
            <tr>
                <td><input type="button" name="submit" id="submit" value="Update" onclick="update()" /></td>
            </tr>
        </table>
	</body>
	
	<script src="scripts/jquery-1.8.3.js"></script>
    <script>
        function update() {
        	var teachers = [];
        	var t = $(".teacher");
        	t.each(function() {
        		var id = $(this).attr('id');
        		var val = $(this).val();
        		teachers.push(id + ':' + val);
        	});
			$.post('ajax/updateTeachers.php', {
				teachers : teachers
			}, function(data) {
				if (data) {
					alert("updated");
					window.close();
				}
			});
		}
    </script>
</html>