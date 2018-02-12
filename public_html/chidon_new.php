<?
header("Location: chidon");
exit; 
if (isset($_POST['submit'])) {
	//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
	require_once 'db.php';
	
	$school = mysql_real_escape_string(trim($_POST['school']));
	$location = mysql_real_escape_string(trim($_POST['location']));
	$type = mysql_real_escape_string($_POST['type']);
	$grades = mysql_real_escape_string(implode(',', $_POST['grades']));
	
	$sql = "insert into chidon_new 
			set school = '$school', 
			location = '$location', 
			type = '$type', 
			grades = '$grades'";
	if (@mysql_query($sql)) {
		$success = true;
		$msg = "Your registration has been accepted. Thank you.";
	} else {
		$msg = "There was an error processing your registration, please try again or contact tzivos hashem.<br /><br />";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Chidon Registration Form</title>
		<script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
		<script>
			$(function() {
				$("#submit").click(function() {
					var errors = [];
					
					var agree = $("#agree").is(":checked");
					var school = $("#school").val();
					var type = $(".type:checked").length;
					var grades = $(".grades:checked").length;
					
					if (school == "") {
						errors.push("Please enter a school name.");
					} 
					if (!type) {
						errors.push("Please choose school type.");
					}
					if (!grades) {
						errors.push("Please select at least one grade.");
					}
					if (!agree) {
						errors.push("Please indicate your agreement in joining the chidon.")
					}
					
					if (errors.length > 0) {
						var str = '';
						for (error in errors) {
							str += errors[error] + "\n";
						}
						alert(str);
						return false;
					} else {
						return true;
					}
				});
			});
		</script>
		<style>
			.main {
				margin-left: 20%;
			}
            .school table {
            	font-size: 14px;
            }
            td {
            	vertical-align: top;
            }
    	</style>
	</head>
	
	<body>
		<div class='main'>
			<div style='float: left; margin-right: 20px;'>
				<img src="images/Chidon-Logo.jpg" />
			</div>
			
	        <h1>Chidon Registration Form</h1>
	        
	        <? 
	        if (isset($msg)) {
	        	echo $msg; 
	        	if ($success) {
	        		exit;
	        	}
	        }	
	        ?>
	        
	        <form action="chidon_new.php" method="post">
	        	<div class='school'>
	        		<table>
	        			<tr>
	        				<td>School Name</td>
	        				<td><input type='text' name='school' id='school' size='40' /></td>
	        			</tr>
	        			<tr>
	        				<td>School Location</td>
	        				<td><input type='text' name='location' id='location' size='40' /></td>
	        			</tr>
	        			<tr>
	        				<td>School Type</td>
	        				<td>
	        					<input type="radio" name="type" class="type" value="boys" /> Boys School<br />
	        					<input type="radio" name="type" class="type" value="girls" /> Girls School<br />
	        					<input type="radio" name="type" class="type" value="mixed" /> Boys & Girls School
	        				</td>
	        			</tr>
	        			<tr>
	        				<td width="120">Grades Joining</td>
	        				<td>
        						<? 
        						for ($i = 4; $i < 9; $i++) {
        							echo "<input type='checkbox' name='grades[]' class='grades' value='$i' /> $i<br />";
								}
								?> 	        							
	        				</td>
	        			</tr>
	        			<tr>
	        				<td colspan='2'>
	        					<input type="checkbox" name="agree" id="agree" />
	        					Yes! Our school will IY"H be teaching the Yahadus Curriculum and 
	        					joining the Chidon next year.<br />
	        					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	        					Click <a href="http://www.livinglessons.com/index.php?option=com_virtuemart&Itemid=14&vmcchk=1">here</a> to purchase the curriculum for your school.
	        				</td>
	        			</tr>
	        			<tr>
	        				<td colspan="2">
	        					<br />
	        					<input type="submit" name="submit" id="submit" value="submit" />
	        				</td>
	        			</tr>
	        		</table>
	        	</div>
	        </form>
	    </div>
	</body>
</html>