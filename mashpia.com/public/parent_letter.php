<?
session_start();
$admin_auth = array('school'); 
require('header.php'); 

if (isset($_POST['submit'])) {
	//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
	
	$ids = array();
	foreach ($_POST as $k => $v) {
		if (is_int($k)) {
			$ids[] = $k;
		}
	}

    switch ($_POST['choice']) {
		case 1:
			$type = 'class';
			break;
		case 2:
			$type = 'user';
			break;
		case 3:
			$type = 'school';
			break;
	}
	
	switch ($_POST['signature']) {
		case 1:
			$signed = 'bc';
			break;
		case 2:
			$signed = 'teacher';
			break;
	}
	
	$s = $_POST['school'];
	$sch = explode(':', $s);
	$_SESSION['school'] = $sch[0];
	$_SESSION['schoolName'] = $sch[1];
	$_SESSION['choice'] = $type;
	$_SESSION['signature'] = $signed;
	$_SESSION['admin_id'] = $admin_user['admin_id'];
	if (!empty($ids))
		$_SESSION['ids'] = implode(',', $ids);
	
	header("Location: parent_letter2.php");
}

require_once 'class.adminSchools.php';      
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Letter to Parents</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
        	fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
        
        <script src="jquery-1.8.1.min.js"></script>
        <script src="scripts/jquery.styleselect.js"></script>       

        <script>
        	$(function() {
        		<? if (count($schools) > 1) : ?>
	        		$("#school").sSelect();
	        		$("#choice").hide();
        		<? endif; ?>
        		$("#grades").hide();
        		$("#users").hide();
        		$("#signature").hide();
        		$("#letter").hide();
        		$("#submit").hide();
        		
        		$("#school").change(function() {
	        		$("#grades").hide();
	        		$("#users").hide();
	        		$(".signature").attr('checked', false);
        		    $("#signature").hide();
        		    $("#letter").hide();
	        		$("#submit").hide();
          			$(".choice").attr('checked', false);
        			$("#choice").show();
        		});
        		
        		$(".choice").click(function() {
        			$("#grades").empty();
        			$("#grades").hide();
        			$("#users").empty();
        			$("#users").hide();
        			$(".signature").attr('checked', false);
        			$("#signature").hide();
        			$("#letter").hide();
        			$("#submit").hide();
        			var val = $(this).val();
        			var s = $("#school").val();
        			var id = s.substr(0,s.indexOf(':'));
        			if (val == 1) {
	        			$.get('ajax/getClasses.php', {id : id}, function(data) {
	        				var grades = $.parseJSON(data);
	        				$("#grades").append("<legend>Choose By Platoon</legend><table id='platoonsTable'><tr><th>Platoon</th><th>Select</th></tr><tr><td>Select All</td><td><input type='checkbox' name='all' id='allGrades' /></td></tr>");
	        				for (grade in grades) {
	        					$("#platoonsTable").append("<tr><td>" + grades[grade] + "</td><td><input type='checkbox' name='" + grade + "' id='" + grade + "' class='grade' /></td></tr>");
	        				}
	        				$("#grades").append("</table>");
	        				$("#grades").show();
	        				$("#signature").show();
	        				
	        				$("#allGrades").click(function() {
        						var grades = $(".grade");
        						var checked = $(this).is(":checked");
        						$.each(grades, function() {
        							$(this).attr('checked', checked);
        						});
	        				});
	        			});
	        		} else if (val == 2) {
	        			//get classes / users
	        			$.get('ajax/getUsersInSchool.php', {id : id}, function(data) {
	        				//alert(data);
	        				var users = $.parseJSON(data);
	        				$("#users").append("<legend>Choose By Soldier</legend><table id='usersTable'><tr><th>Platoon</th><th>Soldier</th><th>Select</th></tr>");
	        				$("#usersTable").append("<tr><td colspan='2'>All soldiers</td><td><input type='checkbox' name='all' id='allUsers' /></td></tr>");
	        				for (grade in users) {
	        					for (sub in users[grade]) {
		        					for (user in users[grade][sub]) {
		        						$("#usersTable").append("<tr><td>" + grade + "-" + sub + "</td><td>" + users[grade][sub][user] + "</td><td><input class='user' type='checkbox' id='" + user + "' name='" + user + "' /></td></tr>");
		        					}
		        				}
	        				}
	        				$("#users").append("</table>");
	        				$("#users").show();
	        				$("#signature").show();
	        				
	        				$("#allUsers").click(function() {
	        					var users = $(".user");
        						var checked = $(this).is(":checked");
        						$.each(users, function() {
        							$(this).attr('checked', checked);
        						});
	        				});
	        			});
	        		} else if (val == 3) {
	        			$(".signature").attr('checked', false);
	        			$("#signature").show();
	        		}
        		});
        		
        		$(".signature").click(function() {
        			//$("#letter").show();
        			$("#submit").show();
        		});
        	});
        </script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Letter to Parents</h1>
        
        <form action="parent_letter.php" method="post">
	        
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
	        	<input type="radio" name="choice" class="choice" value="3" /> Send to entire school<br />
	        	<input type="radio" name="choice" class="choice" value="1" /> Choose by platoon<br />
	        	<input type="radio" name="choice" class="choice" value="2" /> Choose by soldier
	        </fieldset>
	        
	        <br />
	        <fieldset id="grades">
	        </fieldset>
	        
	        <fieldset id="users">
	        </fieldset>
	        
	        <br />
	        <fieldset id="signature">
	        	<legend>Choose Signature</legend>
	        	<input type="radio" name="signature" class="signature" value="1" /> Sign Letter by Base Commander <!--<a href="#" class="popup">[view / edit]</a>--><br />
	        	<input type="radio" name="signature" class="signature" value="2" /> Sign Letter by Teacher <!--<a href="#" class="popup">[view / edit]</a>--><br />
	        </fieldset>

	        <br />
	        <input type="submit" name="submit" id="submit" value="next" />
	    </form>
    </body>
</html>
