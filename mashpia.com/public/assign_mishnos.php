<?
$admin_auth = array('school');
require_once 'header.php';

if (isset($_GET['school'])) {
	$sSchool = $_GET['school'];
	$sGrade = $_GET['grade'];
	$sUser = $_GET['user'];
}

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$first = true;
require_once 'class.schoolClasses.php';
foreach ($schools as $id => $school) {
	if ($first) {
		$s = new SchoolClasses( $id );
		$first = false;
	}
	if (isset($sSchool) && $sSchool == $id) {
		$s = new SchoolClasses( $id );
	}
}
$grades = $s->getClasses();

require_once 'class.mishnaInfo.php';
$sedorim = MishnaInfo::getSedorim();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Assign Mesechtos</title>
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
            div.module {
            	padding-top: 10px;
            	padding-bottom: 8px;
            } 
            div.module li {
            	border-top: none !important;
            }
            /*
            .column {
            	float: left;
            	width: 100px;
            }
            */
        </style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1>Assign Mesechtos</h1>
		
		<?php if (isset($_GET['success']) && $_GET['success']) : ?>
		<div style="color: red">
			Assignments Saved.<br /><br />
		</div>
		<?php endif; ?>
        
        <form action="assign_mishnos_action.php" method="post">
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
						if (isset($sSchool) && $schoolID == $sSchool) {
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
						if (isset($sGrade) && $sGrade == $grade['class_id']) {
							echo "<option value='" . $grade['class_id'] . "' selected>" . $desc . "</option>";
						} else {
							echo "<option value='" . $grade['class_id'] . "'>" . $desc . "</option>";
						}
					}
					?>
				</select>
				<br />
				
				<div id="students">
					
				</div>
								
	        </fieldset>
	        <br />
	        <button id="assign">Assign Mesechtos</button>
	        
	        <div id="mishnosDiv" style="display: none">
		        <br />
		        <fieldset>
		        	<legend>Choose Mishnos</legend>
		        	<select name="choiceType" id="choiceType">
		        		<option value='1'>By Seder</option>
		        		<option value='2'>By Alphabetical List</option>
		        	</select>
		        	<br />
		        	
		        	<div id="bySeder">
		        		<div id="bySmesechtos"></div>
		        	</div>
			        
			        <div id="byList">
			        	<div id="byLmesechtos"></div>
			        </div>
			        
			        <div style="clear: both"></div>
			        <div align="center">
			        	<input type="submit" name="submit" value="Save" id="submit" />
		        		<input type="reset" name="reset" value="Reset" />
			        </div>
		        </fieldset>	        
			</div>
		</form>
	</body>
	
	<script>
		var grade = $("#grade").val();
		getUsers( grade );
		init();
		
		function init() {
			$("#assign").click( function( e ) {
				e.preventDefault();
				
				//check that ppl was setup
				var school = $("#school").val();
				var grade = $("#grade").val();
				var user = $("#student").val();
				$.post('ajax/getPPL.php', { school : school, grade : grade, user : user }, function( success ) {
					var ppl = parseInt( success );
					if (ppl) { 
						getMesechtos();
						$("#assign").hide();
						$("#mishnosDiv").show();
						$("#choice").find("select, input").attr('disabled', 'true');
						
						var str = '<input type="hidden" name="school" value="' + $("#school").val() + '" />' + 
							'<input type="hidden" name="grade" value="' + $("#grade").val() + '" />' + 
							'<input type="hidden" name="student" value="' + $("#student").val() + '" />';
						$("form").append( str );
					} else {
						alert('You have not setup the points per line.');
						window.location = "mishna_settings.php";
					}
				});
			});
			
			function getMesechtos() {
				var school = $("#school").val();
				var grade = $("#grade").val();
				var user = $("#student").val();
				alert(user);
				
				$.post('ajax/getMishnos.php', {
					school : school, 
					grade : grade, 
					user : user, 
					byList : 0 
				}, function( reply ) {
					var info = $.parseJSON( reply );
					$("#bySmesechtos").empty();
					var html = "<br /><ul class='tabs'>";
					for (var seder in info) {
						html += "<li>" + seder + "</li>";
					}
					html += "</ul>";
					for (var seder in info) {
						html += "<div class='module'><ul>";
						for (var id in info[seder]) {
							for (var mesechto in info[seder][id]) {
								if (info[seder][id][mesechto])
									html += "<li><input type='checkbox' name='mesechtos[]' value='" + id + "' checked /> " + mesechto + "</li>";
								else
									html += "<li><input type='checkbox' name='mesechtos[]' value='" + id + "' /> " + mesechto + "</li>";
							}
						}
						html += "</ul></div>";
					}
					$("#bySmesechtos").append( html );
					$("ul.tabs").tabs("div.module");
				});
			}
			
			function getMesechtosByOrder() {
				var school = $("#school").val();
				var grade = $("#grade").val();
				
				$.post('ajax/getMishnos.php', {
					school : school, 
					grade : grade, 
					byList : 1 
				}, function( reply ) {
					var info = $.parseJSON( reply );
					$("#byLmesechtos").empty();
					var cols = 6;
					var numMesechtos = Object.keys(info).length;
					var changeColumn = Math.ceil(numMesechtos / cols);
					var html = "<br /><ul class='tabs'><li>Alphabetical List of מסכתות</li></ul><div class='module'><ul><div class='column'>";
					var num = 0;
					for (var id in info) {
						for (var mesechto in info[id]) {
							if (num++ == changeColumn) {
								html += "</div><div class='column'>";
								num = 1;
							}
							if (info[id][mesechto])
								html += "<li><input type='checkbox' name='mesechtos[]' value='" + id + "' checked /> " + mesechto + "</li>";
							else
								html += "<li><input type='checkbox' name='mesechtos[]' value='" + id + "' /> " + mesechto + "</li>";
						}
					}
					html += "</div></ul></div>";
					$("#byLmesechtos").append( html );
					$("ul.tabs").tabs("div.module");
				});
			}
			
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
					getMesechtosByOrder();
					$("#byList").show();
				}
			});
		}
		
		function getUsers( id ) {
			$.get('ajax/getUsers.php', {id : id}, function( reply ) {
				var sUser = <?= isset($sUser) ? $sUser : 0 ?>;
				var students = $.parseJSON( reply );
				var str = "<select name='student' id='student'><option value='-1'";
				if (sUser == 0) str += " selected";
				str += ">All Students</option>";
				for (var s in students) {
					str += "<option value='" + s + "'";
					if (s == sUser) str += " selected";
					str += ">" + students[s] + "</option>";
				}
				str += "</select>";
				$("#students").empty();
				$("#students").append( str );				
			});
		}
		
		$("#school").change( function() {
			var val = $(this).val();
			$.get('ajax/getClasses.php', {id : val, hasUsers : 1}, function( reply ) {
				var grades = $.parseJSON( reply );
				$("#grade").empty();
				$("#students").empty();
				var str = "";
				var selected = 0;
				for (var g in grades) {
					selected = g;
					str += "<option value='" + g + "'>" + grades[g] + "</option>";
				}
				$("#grade").append( str );
				getUsers( selected );
			});
		});
		
		$("#grade").change( function() {
			var id = $("#grade").val();
			getUsers( id );
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
			var c = confirm("Are you sure you would like to submit all changes?");
			if (c) {
				return true;
			} else {
				return false;
			}
			/*	
			if (!found) {
				alert("You have not selected any mesechtos.");
				return false;
			}
			*/
		});
				
		
		$( function() {
			//$("#byList").hide();
			<? if ($numSchools == 1 || isset($sGrade)) { ?>
				$("#grade").trigger('change');
			<? } ?>
		});
		
	</script>
</html>