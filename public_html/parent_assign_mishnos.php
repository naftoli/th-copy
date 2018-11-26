<?
$admin_auth = array('user');
require_once 'header.php';

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
$admin->get_markable_children();

$children = array();
foreach ($admin->children as $child) {
	//filter out children with no school/class id
	if (!empty($child->school_id) && !empty($child->class_id)) {
		$children[] = $child;
		if (isset($_GET['child']) && $_GET['child'] == $child->user_id) {
			$schoolID = $child->school_id;
			$classID = $child->class_id;
		}
	}
}

if (!empty($children) && !isset($schoolID)) {
	$schoolID = $children[0]->school_id;
	$classID = $children[0]->class_id;
}

require_once 'class.mishnaInfo.php';
$sedorim = MishnaInfo::getSedorim();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Assign Mishnayos</title>
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
            .middle {
            	text-align: center;
            	margin: auto;
            }
        </style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1>Assign Mishnayos</h1>
        
        <form action="assign_mishnos_action.php" method="post">
	        <fieldset id="choice">
	        	<legend>Choose Child</legend>
				<?
				echo "<input type='hidden' id='school' name='school' value='" . $schoolID . "' />";
				echo "<input type='hidden' id='grade' name='grade' value='" . $classID . "' />";
				echo "<input type='hidden' name='parent' value='1' />";
				?>				
				<select name="student" id="student">
					<?
					foreach ($children as $child) {
						$name = $child->first . ' ' . $child->last;
						if (isset($_GET['child']) && $child->user_id == $_GET['child'])
							echo "<option value='" . $child->user_id . "' selected>" . $name . "</option>";
						else
							echo "<option value='" . $child->user_id . "'>" . $name . "</option>";
					}
					?>
				</select>
				
				<br />
				<select name="year" id="year">
					<option value='5775'>5775</option>
					<option value='5776'>5776</option>
					<option value='5777'>5777</option>
					<option value="5778">5778</option>
					<option value="5779">5779</option>
				</select>
	        </fieldset>
	        
	        <br />
	        <fieldset>
	        	<legend>Choose Mishnos</legend>
	        	<select name="choiceType" id="choiceType">
	        		<option value='1'>By Seder</option>
	        		<option value='2'>By Alphabetical List</option>
	        	</select>
	        	<br />
	        	
	        	<div id="bySeder">
		        	<select name="seder" id="seder">
		        		<?
		        		foreach ($sedorim as $sederID => $seder) {
		        			echo "<option value='" . $sederID . "'>" . $seder . "</option>";
		        		}
		        		?>
		        	</select>
		        	<br />
		        	
		        	<ul id="mesechtos"></ul>
		        </div>
		        
		        <div id="byList">
		        	<ul id="byLmesechtos"></ul>
		        </div>
	        </fieldset>
	        <br />
	        <input type="submit" name="submit" value="Submit" id="submit" />
		</form>
	</body>
	<script>	
		$("#student").change( function() {
			var user = $(this).val();
			window.location = "parent_assign_mishnos.php?child=" + user;
		});
		
		$("#choiceType").change( function() {
			var val = $(this).val();
			if (val == 1) {
				$("#byList").hide();
				$("#byLmesechtos").empty();
				getMesechtos();
				$("#bySeder").show();
			} else if (val == 2) {
				$("#bySeder").hide();
				$("#mesechtos").empty(); 
				getMesechtos('byLmesechtos');
				$("#byList").show();
			}
		});
		
		$("#seder").change( function() {
			getMesechtos();
		});
		
		$("#submit").click( function() {
			var found = false;
			var elems = ['byLmesechtos', 'mesechtos'];
			$(elems).each( function(k, v) {
				$("#" + v + " input").each( function() {
					if ($(this).is(":checked")) {
						found = true;
						return;
					}
				});
			});			
			if (!found) {
				alert("You have not selected any mesechtos.");
				return false;
			}
		});
		
		function getMesechtos( elem ) {
			if (elem) {
				var byList = 1;
			} else {
				var elem = 'mesechtos';
				var byList = 0;
			}
			var seder = $("#seder").val();
			var school = $("#school").val();
			var grade = $("#grade").val();
			var user = $("#student").val();
			var year = $("#year").val();
			
			$.post('ajax/getMishnos.php', {
				seder : seder,
				school : school, 
				grade : grade, 
				user : user, 
				year : year, 
				byList : byList
			}, function( reply ) {
				var info = $.parseJSON( reply );
				$("#" + elem).empty();
				var list = '';
				for (var id in info) {
					var pos = id.indexOf(':');
					var mID = id.substring(pos+1);
					for (var mesechto in info[id]) {
						if (info[id][mesechto])
							list += "<li><input type='checkbox' name='mesechtos[]' value='" + mID + "' checked /> " + mesechto + "</li>";
						else
							list += "<li><input type='checkbox' name='mesechtos[]' value='" + mID + "' /> " + mesechto + "</li>";
					}
				}
				$("#" + elem).append( list );
			});
		}
		
		$( function() {
			$("#byList").hide();
			getMesechtos();
		});
	</script>
</html>