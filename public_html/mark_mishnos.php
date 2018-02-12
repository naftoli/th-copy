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
        if (isset($_GET['msg'])) {
        	if ($_GET['msg'] != 'y') {
	        	echo urldecode($_GET['msg']) . "<br />";
				echo "Click <a href='mishna_settings.php'>here</a> to setup points per line.";
				exit;
			} else {
				echo "<div align='center' style='color:red'>Saved.</div>";
			}
        } 
		?>
        
        <form action="mark_mishnos_action.php" method="post">
			<fieldset id="choice">
				<legend>Choose Grade</legend>
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
				<!--
				<br />
				<select name="year" id="year">
					<option value='5776'>5776</option>
					<option value='5777'>5777</option>
					<option value="5778">5778</option>
					<option value="5779">5779</option>
				</select>
				-->
	        </fieldset>
	        <br />
	        <div align="center">
	        	<input type="submit" name="submit" value="Save Marks" id="submit" />
	        </div>
	        
	        <fieldset style="width: 45%; float: left;">
	        	<legend>Choose Students</legend>
	        	<input type="checkbox" id="allStudents" /> All Students<br />
	        	<div id="students" style="line-height: 2; font-size: 12px; padding-top: 10px;">
					<?
					foreach ($students as $info) {
						foreach ($info as $user_id => $student) { ?>
							<input type='checkbox' class='student' name='student[]' value='<?=$user_id?>'
								<?= (isset($_GET['user']) && $_GET['user'] == $user_id) ? "checked" : ""  ?>
							/><?=$student?><br />
						<? }
					} ?>
				</div>
	        </fieldset>
	        
	        <fieldset style="width: 45%; float: right;">
	        	<legend>Choose Mishnos</legend>
	        	<select name="mesechto" id="mesechto"></select>
	        	
	        	<div id="perokim"></div>
	        </fieldset>
	        
	        <div style="clear: both"></div>
	        <br />
		</form>
	</body>
	<script>
		$("form").submit( function() {
			// make sure at least one child is selected
			if (!$("#allStudents:checked").length && !$(".student:checked").length) {
				alert('You must choose at least one student!');
				return false;
			}
			if (!$("#perokim input:checked").length) {
				alert('You have not chosen any mishnos to mark!');
				return false;
			}
		});
		
		$("#school").change( function() {
			var val = $(this).val();
			var f = m;
			$.get('ajax/getClasses.php', {id : val, hasUsers : 1}, function( reply ) {
				var grades = $.parseJSON( reply );
				$("#grade").empty();
				$("#students").empty();
				var str = "";
				for (var g in grades) {
					str += "<option value='" + g + "'>" + grades[g] + "</option>";
				}
				$("#grade").append( str );
				var g = f;				
				var val = $("#grade").val();
				$.get('ajax/getUsers.php', {id : val}, function( reply ) {
					var students = $.parseJSON( reply );
					$("#students").empty();
					var str = "";
					for (var s in students) {
						str += "<input type='checkbox' name='student[]' value='" + s + "' />" + students[s] + "<br />";
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
				$("#students").empty();
				var str = "";
				for (var s in students) {
					str += "<input type='checkbox' name='student[]' class='student' value='" + s + "' />" + students[s] + "<br />";
				}
				$("#students").append( str );
				m();
			});
		});
		
		$("#allStudents").click( function() {
			var val;
			if ($(this).is(":checked")) {
				val = true;
			} else {
				val = false;
			}
			$(".student").attr('checked', val);
		});
		
		$("#mesechto").change( function() {
			p();
		});
		
		var m = function getMesechtos() {
			/*
			var school = $("#school").val();
			var grade = $("#grade").val();
			var year = 5776;
			var fn = p;
			
			$.post('ajax/getMishnosToMark.php', {
					school : school, 
					grade : grade, 
					year : year, 
			}, function( reply ) {
				var info = $.parseJSON( reply );

				$("#mesechto").empty();
				$("#perokim").empty();
				var list = "";
				for (var mID in info) {
					list += "<option value='" + mID + "'> " + info[mID] + "</option>";
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
			*/
			$.post('ajax/getAllMishnos.php', function( success ) {
				var mesechtos = $.parseJSON( success );
				$("#mesechto").empty();
				$("#perokim").empty();
				var html = '';
				for (var id in mesechtos) {
					html += "<option value='" + id + "'>" + mesechtos[id] + "</option>";
				}
				$("#mesechto").append( html );
				p();
			});
		};
		
		var p = function getPerokim() {
			var mesechto = $("#mesechto").val();
			var allLearned = new Array();
			
			if (mesechto) {
				$.post('ajax/getPerokim.php', { mesechto : mesechto }, function( reply ) { 
					var he = <?=json_encode( $he_chars )?>;
					var perokim = $.parseJSON( reply );
					//var perokim = info[0];
					//var learned = info[1];

					var html = "<input type='checkbox' id='entireM' /> Entire מסכת <br />\
							<input type='checkbox' name='mesecthoAtOnce' id='mesechtoAtOnce' /> בבת אחת<br />";
					for (var perek in perokim) {
						html += "<br /><div id='perek:" + perek + "'> \
							<input type='checkbox' class='perek' /> פרק " + he[perek];
						html += "<br /><input type='checkbox' name='perekAtOnce|" + perek + "' class='perekAtOnce' /> בבת אחת";
						html += "<br /><table><tr><th>Mishna</th><th>Lines Learned</th></tr>";
						for (var mishna in perokim[perek]) {
							html += "<tr><td><input type='checkbox' class='mishna'";
							//if (learned[perek][mishna]) {
							//	html += " checked";
							//}
							html += " /> " + he[mishna] + " </td><td>\
								<input name='" + perek + ':' + mishna + "' class='lines' type='text' size='2'";
							//if (learned[perek][mishna]) {
							//	html += " value='" + learned[perek][mishna] + "'";
							//}
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
							$(elem).attr('checked', true);
							$(elem).trigger('click');
							$(elem).attr('checked', true);
						}
					});
					
					$(".perekAtOnce").click( function() {
						var elem = $(this).parent().find('.perek');
						if (!$(elem).is(":checked")) {
							$(elem).attr('checked', true);
							$(elem).trigger('click');
							$(elem).attr('checked', true);
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
							//$(this).parent().parent().parent().parent().parent().find('.perek').attr('checked', true);
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
		
		$( function() {
			m();
		});
	</script>
</html>