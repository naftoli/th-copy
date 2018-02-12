<?
$admin_auth = array('user');
require_once 'header.php';

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
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

$he_chars = array(
	1	=>	'א',
	2	=>	'ב',
	3	=>	'ג',
	4	=>	'ד',
	5	=>	'ה',
	6	=>	'ו',
	7	=>	'ז',
	8	=>	'ח',
	9	=>	'ט',
	10	=>	'י',
	11	=>	'יא',
	12	=>	'יב',
	13	=>	'יג',
	14	=>	'יד',
	15	=>	'טו',
	16	=>	'טז',
	17	=>	'יז',
	18	=>	'יח',
	19	=>	'יט',
	20	=>	'כ',
	21	=>	'כא',
	22	=>	'כב',
	23	=>	'כג',
	24	=>	'כד',
	25	=>	'כה',
	26	=>	'כו',
	27	=>	'כז',
	28	=>	'כח',
	29	=>	'כט',
	30	=>	'ל'
);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Mark Mishnos</title>
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
        <h1>Mark Mishnos</h1>
        
        <?
		/*
        if (isset($_GET['msg']) && $_GET['msg'] != 'y') {
        	echo urldecode($_GET['msg']) . "<br />";
			echo "Please contact your school and have them setup the points per line.";
			exit;
        }
        */
		?>
        
        <form action="mark_mishnos_action.php" method="post">
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
	        </fieldset>
	        
	        <br />
	        <fieldset>
	        	<legend>Choose Mishnos</legend>
	        	<select name="mesechto" id="mesechto"></select>
	        	
	        	<div id="perokim"></div>
	        </fieldset>
	        <br />
	        <input type="submit" name="submit" value="Save" id="submit" />
		</form>
	</body>
	<script>		
		$("#student").change( function() {
			var user = $(this).val();
			window.location = "parent_mark_mishnos.php?child=" + user;
		});
		
		$("#mesechto").change( function() {
			p();
		});
		
		var m = function getMesechtos() {
			var school = $("#school").val();
			var grade = $("#grade").val();
			var user = $("#student").val();
			var fn = p;
			
			$.post('ajax/getMishnosToMark.php', {
					school : school, 
					grade : grade, 
					user : user 
			}, function( reply ) {
				var info = $.parseJSON( reply );

				$("#mesechto").empty();
				var list = "";
				if (Object.keys(info).length > 0) {
					for (var id in info) {
						var pos = id.indexOf(':');
						var mID = id.substring(pos+1);
						list += "<option value='" + mID + "'> " + info[id] + "</option>";
					}
				}
				var getPerokim = true;
				if (list == "")	{
					list += "<option value='0'>No Mesechtos Available</option>";
					getPerokim = false;
				}
				$("#mesechto").append( list );
				
				if (getPerokim) {
					fn();
				}
			});
		};
		
		var p = function getPerokim() {
			var mesechto = $("#mesechto").val();
			var user = $("#student").val();
			var allLearned = new Array();
			
			if (mesechto) {
				$.post('ajax/getPerokim.php', { mesechto : mesechto, user : user }, function( reply ) { 
					var he = <?=json_encode( $he_chars )?>;
					var info = $.parseJSON( reply );
					var perokim = info[0];
					var learned = info[1];

					var html = "<input type='checkbox' id='entireM' /> Entire מסכת <br />\
							<input type='checkbox' name='mesecthoAtOnce' /> בבת אחת<br />";
					for (var perek in perokim) {
						html += "<br /><div id='perek:" + perek + "'> \
							<input type='checkbox' class='perek' /> פרק " + he[perek];
						html += "<br /><input type='checkbox' name='perekAtOnce|" + perek + "' /> בבת אחת";
						html += "<br /><table><tr><th>Mishna</th><th>Lines Learned</th></tr>";
						for (var mishna in perokim[perek]) {
							html += "<tr><td><input type='checkbox' class='mishna'";
							if (learned[perek][mishna]) {
								html += " checked";
							}
							html += " /> " + he[mishna] + " </td><td>\
								<input name='" + perek + ':' + mishna + "' class='lines' type='text' size='2'";
							if (learned[perek][mishna]) {
								html += " value='" + learned[perek][mishna] + "'";
							}
							html += " /> (" + perokim[perek][mishna] + " lines total)\
								<span style='visibility:hidden'>" + perokim[perek][mishna] + "</span></td></tr>";
						}
						html += "</table></div>";
					}
					$("#perokim").empty();
					$("#perokim").append(html);

					$("#entireM").click( function() {
						if ($(this).is(":checked")) {
							var checked = true;
						} else {
							var checked = false;
						}
						$(this).parent().find('.perek').attr('checked', checked);
						$(this).parent().find('.mishna').attr('checked', checked);
						$(this).parent().find('.mishna').trigger('click');
						$(this).parent().find('.mishna').attr('checked', checked);
					});
					
					$(".perek").click( function() {
						if ($(this).is(":checked")) {
							var checked = true;
						} else {
							var checked = false;
						}
						$(this).parent().find('.mishna').attr('checked', checked);
						$(this).parent().find('.mishna').trigger('click');
						$(this).parent().find('.mishna').attr('checked', checked);
					});
					
					$(".mishna").click( function() {
						if ($(this).is(":checked")) {
							var lines = $(this).parent().next().find('span').text().trim();
							if ( $(this).parent().next().find('.lines').val() == '' )
								$(this).parent().next().find('.lines').val(lines);
							//make sure perek is also checked
							$(this).parent().parent().parent().parent().parent().find('.perek').attr('checked', true);
						} else {
							$(this).parent().next().find('.lines').val('');
						}	
					});
				});
			}
		};
		
		$("#submit").click( function() {
			return confirm("Please make sure that you have entered the correct line amounts.\nOnce you save, the points will be given and will NOT BE REFUNDABLE!\nAre you sure you want to save?");
		});
		
		$( function() {
			m();
			<? if (isset($_GET['msg']) && $_GET['msg'] == 'y') { ?>
				alert('saved');
			<? } ?>
		});
	</script>
</html>