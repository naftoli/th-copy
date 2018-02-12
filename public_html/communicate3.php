<?
session_start();
if (!isset($_SESSION['type']) || !isset($_SESSION['method']) || !isset($_SESSION['signature']) 
	|| !isset($_POST['submit'])) {
	header("Location: communicate.php");
}
	
$_SESSION['content'] = $_POST['content'];
if ($_SESSION['type'] == 'missions') {
	$_SESSION['start'] = $_POST['start'];
	$_SESSION['end'] = $_POST['end'];
}

$admin_auth = array('school'); 
require('header.php');

echo "<pre>";
//print_r($_SESSION);
//print_r($_POST);
echo "</pre>";

require_once 'class.adminSchools.php';      
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$ranks = array();
$sql = "select rank_ord, rank_name from ranks order by rank_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$ranks[$row['rank_ord']] = $row['rank_name'];
} 
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="communicate.css" rel="stylesheet" type="text/css">
		<title>Communicate with Parents</title>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Communicate with Parents</h1>
		
		<form action="communicate4.php" method="post">
		
			<? if (count($schools) > 1) : ?>
	        <fieldset>
	        	<legend>Choose School</legend>
	        	<select name="school" id="school">
	        		<option value="0">Choose School</option>
	        		<?
	        		foreach ($schools as $id => $name) {
	        			echo "<option value='$id:$name'>" . $name . "</option>";
	        		}
					?>
	        	</select>
	        </fieldset>
	        <? else : ?>
	        	<? $key = key($schools); ?>
	        	<input type="hidden" name="school" id="school" value="<?=$key . ':' . $schools[$key]?>" />
	        <? endif; ?>
	        
	        <br />
	        <fieldset id="choice">
	        	<legend>Selection</legend>
	        	<input type="radio" name="choice" class="choice" value="1" /> Send to entire school<br />
	        	<input type="radio" name="choice" class="choice" value="2" /> Choose by platoon<br />
	        	<input type="radio" name="choice" class="choice" value="3" /> Choose by soldier<br />
	        	<input type="radio" name="choice" class="choice" value="4" /> Choose by rank
	        </fieldset>
	        
	        <br />
	        <fieldset id="grades"></fieldset>
	        
	        <fieldset id="users"></fieldset>
	        
	        <fieldset id="rank">
	        	<legend>Choose by Rank</legend>
	        	<select name="rank">
		        	<?
		        	foreach ($ranks as $id => $rank) {
		        		echo "<option value='$id'>$rank</option>";
		        	}
		        	?>
	        	</select>
	        </fieldset>
	        
	        <br />
	        <input type="submit" name="submit" id="submit" value="continue" />
		</form>
		
		<script>
			$(function() {
        		<? if (count($schools) > 1) : ?>
	        		$("#school").sSelect();
	        		$("#choice").hide();
        		<? endif; ?>
        		$("#grades").hide();
        		$("#users").hide();
        		$("#rank").hide();
        		$("#submit").hide();
        		
        		<? if ($_SESSION['method'] == 'email') : ?>
	 	    		var c = confirm("Emails will only be sent out to those students that have email addresses on file.\nWould you like to update your student's email addresses?");
	 	    		if (c) {
	 	    			window.open("student_emails.php", "_blank");
	 	    		}
	 	    	<? endif; ?>
       		});
       		
       		$("#school").change(function() {
        		$("#grades").hide();
        		$("#users").hide();
        		$("#rank").hide();
        		$("#submit").hide();
      			$(".choice").attr('checked', false);
    			$("#choice").show();
    		});
    		
    		$(".choice").click(function() {
				$("#grades").empty();
				$("#grades").hide();
				$("#users").empty();
				$("#users").hide();
				$("#rank").hide();
				
				var val = $(this).val();
				var s = $("#school").val();
				var id = s.substr(0,s.indexOf(':'));
				
				<? if ($_SESSION['method'] == 'email') : ?>
				var email = 1;
				<? else : ?>
				var email = 0;
				<? endif; ?>
				
				if (val == 2) {
	    			$.get('ajax/getClasses.php', {id : id}, function(data) {
	    				var grades = $.parseJSON(data);
	    				$("#grades").append("<legend>Choose By Platoon</legend><table id='platoonsTable'><tr><th>Platoon</th><th>Select</th></tr><tr><td>Select All</td><td><input type='checkbox' name='all' id='allGrades' /></td></tr>");
	    				for (grade in grades) {
	    					$("#platoonsTable").append("<tr><td>" + grades[grade] + "</td><td><input type='checkbox' name='" + grade + "' id='" + grade + "' class='grade' /></td></tr>");
	    				}
	    				$("#grades").append("</table>");
	    				$("#grades").show();
	    				
	    				$("#allGrades").click(function() {
							var grades = $(".grade");
							var checked = $(this).is(":checked");
							$.each(grades, function() {
								$(this).attr('checked', checked);
							});
	    				});
	    			});
	    		} else if (val == 3) {
	    			//get classes / users
	    			$.get('ajax/getUsersInSchool.php', {id : id, email : email}, function(data) {
	    				//alert(data);
	    				var users = $.parseJSON(data);
	    				if (email) {
	    					$("#users").append("<legend>Choose By Soldier</legend><table id='usersTable'><tr><th>Platoon</th><th>Soldier</th><th>Email on file</th><th>Select</th></tr>");
	    					$("#usersTable").append("<tr><td colspan='2'>All soldiers</td><td>&nbsp;</td><td><input type='checkbox' name='all' id='allUsers' /></td></tr>");
	    				} else {
	    					$("#users").append("<legend>Choose By Soldier</legend><table id='usersTable'><tr><th>Platoon</th><th>Soldier</th><th>Select</th></tr>");
	    					$("#usersTable").append("<tr><td colspan='2'>All soldiers</td><td><input type='checkbox' name='all' id='allUsers' /></td></tr>");
	    				}
	    				for (grade in users) {
	    					for (sub in users[grade]) {
	        					for (user in users[grade][sub]) {
	        						if (email) {
	        							var userInfo = users[grade][sub][user];
	        							var flag = userInfo.indexOf(':');
	        							var userName = userInfo.substr(0, flag-1);
	        							var userEmail = userInfo.substr(flag+1);
	        							$("#usersTable").append("<tr><td>" + grade + "-" + sub + "</td><td>" + userName + "</td><td>" + userEmail + "</td><td><input class='user' type='checkbox' id='" + user + "' name='" + user + "' /></td></tr>");
	        						} else {
	        							$("#usersTable").append("<tr><td>" + grade + "-" + sub + "</td><td>" + users[grade][sub][user] + "</td><td><input class='user' type='checkbox' id='" + user + "' name='" + user + "' /></td></tr>");
	        						}
	        					}
	        				}
	    				}
	    				$("#users").append("</table>");
	    				$("#users").show();
	    				
	    				$("#allUsers").click(function() {
	    					var users = $(".user");
							var checked = $(this).is(":checked");
							$.each(users, function() {
								$(this).attr('checked', checked);
							});
	    				});
	    			});
	    		} else if (val == 4) {
	    			$("#rank").show();
	    		}
	    		$("#submit").show();
			});
		</script>
	</body>
</html>