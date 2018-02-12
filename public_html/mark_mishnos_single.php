<?
$admin_auth = array('school');
require_once 'header.php';

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

require_once 'class.schoolClasses.php';
require_once 'class.schoolsUsers.php';
if (isset($_GET['school']) && $_GET['school'] > 0) {
	$s = new SchoolClasses( $_GET['school'] );
	$su = new SchoolsUsers( $_GET['school'] );
} else {
	foreach ($schools as $id => $school) {
		$s = new SchoolClasses( $id );
		$su = new SchoolsUsers( $id );
		break;
	}
}
$grades = $s->getClasses();

if (isset($_GET['grade']) && $_GET['grade'] > 0) {
	$firstGrade = $_GET['grade'];
} else {
	$firstGrade = $grades[0]['class_id'];
}
$su->setClasses( array($firstGrade) );
$su->getUsers();
$students = $su->getUserNames();

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
			echo "Click <a href='mishna_settings.php'>here</a> to setup points per line.";
			exit;
        }
        */
		?>
        
        <form action="mark_mishnos_single_action.php" method="post">
	        <fieldset id="choice">
	        	<legend>Choose Grades / Students</legend>
				<?
				$numSchools = count($schools);
				if ($numSchools == 1) {
					foreach ($schools as $schoolID => $school) {
						echo "<input type='hidden' id='school' name='school' value='" . $schoolID . "' />";
					}
				} else {
					echo "<select name='school' id='school'>";
					foreach ($schools as $schoolID => $school) {
						if (isset($_GET['school']) && $_GET['school'] == $schoolID) {
							echo "<option value='" . $schoolID . "' selected>" . $school . "</option>";
						} else {
							echo "<option value='" . $schoolID . "'>" . $school . "</option>";
						}
					}
					echo "</select>";
				}
				?>
				<br />
				<select name="grade" id="grade">
					<?
					foreach ($grades as $grade) {
						$desc = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
						if (isset($_GET['grade']) && $_GET['grade'] == $grade['class_id']) {
							echo "<option value='" . $grade['class_id'] . "' selected>" . $desc . "</option>";
						} else {
							echo "<option value='" . $grade['class_id'] . "'>" . $desc . "</option>";
						}
					}
					?>
				</select>
				<br />
				
				<select name="student" id="student">
					<?
					foreach ($students as $info) {
						foreach ($info as $user_id => $student) {
							if (isset($_GET['user']) && $_GET['user'] == $user_id) {
								echo "<option value='" . $user_id . "' selected>" . $student . "</option>";
							} else {
								echo "<option value='" . $user_id . "'>" . $student . "</option>";
							}
						}
					}
					?>
				</select>
	        </fieldset>
	        
	        <br />
			<input type="submit" name="submit" value="Save" />
	        <fieldset>
	        	<legend>Choose Mishnos</legend>
	        	<select name="mesechto" id="mesechto"></select>
	        	
	        	<div id="perokim"></div>
	        </fieldset>
	        <br />
	        <input type="submit" name="submit" value="Save" />
		</form>
	</body>
	<script>
		$("#school").change( function() {
			var val = $(this).val();
			var f = m;
			$.get('ajax/getClasses.php', {id : val, hasUsers : 1}, function( reply ) {
				var grades = $.parseJSON( reply );
				$("#grade").empty();
				$("#student").empty();
				var str = "";
				for (var g in grades) {
					str += "<option value='" + g + "'>" + grades[g] + "</option>";
				}
				$("#grade").append( str );
				var g = f;				
				var val = $("#grade").val();
				$.get('../ajax/getUsers.php', {id : val}, function( reply ) {
					var students = $.parseJSON( reply );
					$("#student").empty();
					var str = "";
					for (var s in students) {
						str += "<option value='" + s + "'>" + students[s] + "</option>";
					}
					$("#student").append( str );
					m();
				});
			});
		});
		
		$("#grade").change( function() {
			var val = $(this).val();
			var g = m;
			$.get('ajax/getUsers.php', {id : val}, function( reply ) {
				var students = $.parseJSON( reply );
				$("#student").empty();
				var str = "";
				for (var s in students) {
					str += "<option value='" + s + "'>" + students[s] + "</option>";
				}
				$("#student").append( str );
				m();
			});
		});
		
		$("#student").change( function() {
			m();
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
				$.post('ajax/getPerokim.php', { mesechto : mesechto, user : user, getLearned : true }, function( reply ) { 
					var he = <?=json_encode( $he_chars )?>;
					var info = $.parseJSON( reply );
					var perokim = info[0];
					var learned = info[1];

					var html = "<input type='checkbox' id='entireM' /> Entire מסכת <br />\
							<input type='checkbox' name='mesecthoAtOnce' id='mesechtoAtOnce' /> בבת אחת<br />";
					for (var perek in perokim) {
						html += "<br /><div id='perek:" + perek + "'> \
							<input type='checkbox' class='perek' /> פרק " + he[perek];
						html += "<br /><input type='checkbox' name='perekAtOnce|" + perek + "' class='perekAtOnce' /> בבת אחת";
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
					
					$("#mesechtoAtOnce").click( function() {
						var elem = $(this).parent().find('#entireM');
						if (!elem.is(":checked")) {
							return false;
						}
					});
					
					$(".perekAtOnce").click( function() {
						var elem = $(this).parent().find('.perek');
						if (!elem.is(":checked")) {
							return false;
						}
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
						
						if (!checked) {
							//make sure mesechto is 'unchecked'
							$(this).parent().parent().find('#entireM').attr('checked', false);
							$(this).parent().parent().find('#mesechtoAtOnce').attr('checked', false);
						}
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
							//make sure perek and mesechto are 'unchecked'
							$(this).parent().parent().parent().parent().parent().find('.perek').attr('checked', false);
							$(this).parent().parent().parent().parent().parent().find('.perekAtOnce').attr('checked', false);
							$(this).parent().parent().parent().parent().parent().parent().find('#entireM').attr('checked', false);
							$(this).parent().parent().parent().parent().parent().parent().find('#mesechtoAtOnce').attr('checked', false);
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
				var school = <?=$_GET['school']?>;
				var grade = <?=$_GET['grade']?>;
				var user = <?=$_GET['user']?>;
				$.post('ajax/updateSummary.php', {school : school, grade : grade, user : user});
				alert('saved');
			<? } ?>
		});
	</script>
</html>