<?php
//header("Location: ../under_construction.php"); exit;
$admin_auth = array('school');
require '../header.php';

$d = unixtojd();
$day = date("N", jdtounix($d));
$end = $d;

switch ($day) {
    // 1 is monday, 7 is sunday
    case 1:
        $end -= 4;
        break;
    case 2:
        $end -= 5;
        break;
    case 3:
        $end -= 6;
        break;
    case 4:
        break;
    case 5:
		$end -= 1;
        break;
    case 6:
        $end -= 2;
        break;
    case 7:
		$end -= 3;
        break;
    default:
        break;
}
//$start = $end - 125;
//$start = 2457178;
//$start = $end - 28;
$start = 2457928;

if ((isset($_GET['school']) && isset($_GET['grade']) && isset($_GET['user']))) {
	$school_id 		= $_GET['school'];
	$class_id 		= $_GET['grade'];
	$user_id		= $_GET['user'];
	$startReport 	= $_GET['start'];
	$endReport		= $_GET['end'];
} else {
	header("Location: ../mark_missions2.php");
	exit;
}

if ($user_id == -1) {
	$sql = "select user_id from users where class_id = " . $class_id . " order by last, first limit 1";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$user_id = $row['user_id'];
}

// ***** REPORT DATES ***** //
include("../classes/report.php");
$reports = array();
$sql = "SELECT * FROM reports 
		WHERE report_type='mission_cover_sheet' 
		AND visibility != 'none' 
		and start_date >= $start 
		and end_date <= $end 
		ORDER BY start_date";    
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
    $report = new report($row);
    array_push($reports, $report);
}
// ***** REPORT DATES ***** //
/*
$he = false;
if (isset($_GET['he']) && $_GET['he'] == 1) {
	$he = true;
}
 * 
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Mark Missions</title>
		<link rel="stylesheet" href="newStyle.css?v=2.2" type="text/css" />
	</head>
	
	<body>
		<div class="noPrint" style="margin: auto; width: 60px;">
			<input type="button" value="Back" onclick="window.location = '../admin.php'" />
		</div>
		<?		
		require_once '../class.schoolClasses.php';
		$sc = new SchoolClasses( $school_id );
		$classes = $sc->getClasses();
		
		require_once '../class.schoolsUsers.php';
		$su = new SchoolsUsers( $school_id );
		$su->setClasses('all');
		$temp = $su->getUsers();
		$students = $su->getUserNames();
		?>
		<div id="marking">
			<div id="outer">
				<div id="grade">
					<div class="arrow-left"></div>
					<select name='grade'>
						<?
						foreach ($classes as $class) {
							if ($class['class_id'] == $class_id) {
								$grade = $class['class_grade'] . (empty($class['class_sub']) ? '' : '-' . $class['class_sub']);
								echo "<option value='" . $class['class_id'] . "' selected='selected'>" . 
									$grade . "</option>";
							} else {
								echo "<option value='" . $class['class_id'] . "'>" . 
									$class['class_grade'] . (empty($class['class_sub']) ? '' : '-' . $class['class_sub']) . 
									"</option>";
							}
						}
						?>
					</select>
					<div class="arrow-right"></div>
				</div>
				<div id="student">
					<div class="arrow-left"></div>
					<select name='user'>
						<?
						foreach ($students[$grade] as $id => $name) {
							if ($id == $user_id) {
								echo "<option value='" . $id . "' selected='selected'>" . $name . "</option>";
							} else {
								echo "<option value='" . $id . "'>" . $name . "</option>";
							}
						}
						?>
					</select>
					<div class="arrow-right"></div>
				</div>
				<div id="parsha">
					<div class="arrow-left"></div>
					<select name='parsha'>
						<?
						foreach ($reports as $report) {
							if ($startReport == $report->start_date) {
								echo "<option value='" . $report->start_date . ':' . $report->end_date . 
									"' selected='selected'>" . $report->report_name . "</option>";
							} else {
								echo "<option value='" . $report->start_date . ':' . $report->end_date . 
									"'>" . $report->report_name . "</option>";
							}
						}
						?>
					</select>
					<div class="arrow-right"></div>
				</div>
				
				<div id='change'>
					<button>Update</button>
				</div>
								
			</div>
		</div>
		<div style="clear: both"></div>
		
		<div align="center" style="margin: auto; padding-top: -20px; padding-bottom: 20px;">
			OR Enter User ID: <input type="text" name="userLookup" id="userLookup" />
			<button id="changeUser">GO</button>
		</div>
		
		<?
		require_once 'classes/missions.php';
		require_once 'classes/noPicMission.php';
		require_once 'classes/picMission.php';
		
		$m = new Missions( $startReport, $endReport, $user_id );
		$missions = $m->getMissions();
		
		$objMissions = array();
		foreach ( $missions as $mission ) {
			if ($startReport >= 2457116 && $endReport <= 2457129) {
				$type = 1;
			} else {
				$type = $mission->pic_mission_type;
			}
			$objMissions[] = MissionDisplay::getInstance( $type, $mission );
		}
		
		foreach ($objMissions as $obj) {
			$id = $obj->user_id;
			if ($obj->lang_id == 1) {
				echo "<div class='userMission' id='user-" . $id . "' >";
			} else if ($obj->lang_id == 2) {
				echo "<div class='userMission he' id='user-" . $id . "' dir='rtl' >";
			}
			if (isset($_GET['col']) && $_GET['col'] == 2) $obj->markMissionCol();
			else $obj->markMission();
			echo "</div>";
		}
		?>
		<div style="clear: both"></div>
		<br /><br />
	</body>
	<script src="../scripts/functions.js"></script>
	<script src="../jquery.js"></script>
	<script>
		$( function() {
			$("#changeUser").click( function() {
				var user = $("#userLookup").val();
				if (user > 0) {
					$.post('getUserInfo.php', { user : user }, function( success ) {
						var info = $.parseJSON( success );
						var school = info.school;
						var grade = info.grade;
						var start = <?=$_GET['start']?>;
						var end = <?=$_GET['end']?>;
						var cols = 1;
						var str = "newSchoolMark.php?user=" + user + "&school=" + school + "&grade=" + grade + "&start=" + start + "&end=" + end + "&col=" + cols;
						if(school == <?=$school_id?> || <?=$admin_user['auth'] == "super" ? "true" : "false";?>) {
							window.location = str;
						} else {
							alert("Invalid User ID");
						}
					});
				}
			});
			
			$("#oneCol").click( function() {
				var user = <?=$_GET['user']?>;
				var school = <?=$_GET['school']?>;
				var grade = <?=$_GET['grade']?>;
				var start = <?=$_GET['start']?>;
				var end = <?=$_GET['end']?>;
				var cols = 1;
				var str = "newSchoolMark.php?user=" + user + "&school=" + school + "&grade=" + grade + "&start=" + start + "&end=" + end + "&col=" + cols;
				window.location = str;
			});
			
			$("#twoCols").click( function() {
				var user = <?=$_GET['user']?>;
				var school = <?=$_GET['school']?>;
				var grade = <?=$_GET['grade']?>;
				var start = <?=$_GET['start']?>;
				var end = <?=$_GET['end']?>;
				var cols = 2;
				var str = "newSchoolMark.php?user=" + user + "&school=" + school + "&grade=" + grade + "&start=" + start + "&end=" + end + "&col=" + cols;
				window.location = str;
			});
			
			$(".arrow-left").click( function() {
				var val = $(this).parent().find("option:selected").prev().val();
				var type = $(this).parent().attr('id');
				if (val === undefined) {
					alert("There are no previous " + type + "s.");
					return;
				}
				
				var grade, student, parsha;
				switch (type) {
					case 'grade':
						grade = val;
						student = -1;
						parsha = $("#parsha select").val();
						break;
					case 'student':
						grade = $("#grade select").val();
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						grade = $("#grade select").val();
						student = $("#student select").val();
						parsha = val;
				}
				
				var school = <?=$school_id?>;
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newSchoolMark.php?school=" + school + "&grade=" + grade + "&user=" + student + "&start=" + start + "&end=" + end;
			});
			
			$(".arrow-right").click( function() {
				var val = $(this).parent().find("option:selected").next().val();
				var type = $(this).parent().attr('id');
				if (val === undefined) {
					alert("There are no more " + type + "s.");
					return;
				}
				
				var grade, student, parsha;
				switch (type) {
					case 'grade':
						grade = val;
						student = -1;
						parsha = $("#parsha select").val();
						break;
					case 'student':
						grade = $("#grade select").val();
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						grade = $("#grade select").val();
						student = $("#student select").val();
						parsha = val;
				}
				
				var school = <?=$school_id?>;
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newSchoolMark.php?school=" + school + "&grade=" + grade + "&user=" + student + "&start=" + start + "&end=" + end;
			});
						
			$("#grade select").change( function() {
				var grade = $(this).val();
				$.get('../ajax/getUsers.php', {id : grade}, function( data ) {
					if (data) {
						$("#student select").empty();
						var str = '';
						var users = $.parseJSON( data );
						for (key in users) {
							str += "<option value=" + key + ">" + users[key] + "</option>";
						}
						$("#student select").append( str );
					}
				});
			});
			
			$("#change").click( function() {
				var school = <?=$school_id?>;
				var grade = $("#grade select").val();
				var student = $("#student select").val();
				var parsha = $("#parsha select").val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newSchoolMark.php?school=" + school + "&grade=" + grade + "&user=" + student + "&start=" + start + "&end=" + end;
			});
			
			var user_id = <?=$user_id?>;			
			$(".checkboxDaily").click( function() {
				var info = $(this).attr('id');
				var split = info.indexOf(':');
				var task = info.substring(0,split);
				var date = info.substring(++split);
				var url = '';
				var div = this;
				
				if ($(div).hasClass('marked')) { 
					url = "../delete_functions.php?function_name=delete_daily_task_mark";
				} else if ($(div).hasClass('unmarked')) {
					url = "../add_functions.php?function_name=add_daily_task_mark";
				}
				
				var parameters = [user_id, task, date];
				url += "&parameters=" + parameters;
				
				$.getJSON(url, function(success) {
					if (success == false) {
						alert("Update not performed. Please try again.");
					} else {
						$.post('../ajax/updateMedalsRanks.php', { user : user_id });
						update(div);
					}
				});
			});
			
			$(".checkbox").not(".textInput").click( function() {
				var info = $(this).attr('id');
				var split = info.indexOf(':');
				var task = info.substring(0,split);
				var date = info.substring(++split);
				var url = '';
				var div = this;
			
				if ($(div).hasClass('marked')) { 
					url = "../delete_functions.php?function_name=delete_task_mark";
				} else if ($(div).hasClass('unmarked')) {
					url = "../add_functions.php?function_name=add_task_mark";
				}
				var parameters = [user_id, task, date];
				url += "&parameters=" + parameters;
				//alert(url);
				$.getJSON(url, function(success) {
					if (success == false) {
						alert("Update not performed. Please try again.");
					} else {
						$.post('../ajax/updateMedalsRanks.php', { user : user_id });
						update(div);
					}
				});
			});
			
			$(".textInput input").blur( function() {
				var text = $(this).parent();
				var info = $(text).attr('id');
				var split = info.indexOf(':');
				var task = info.substring(0,split);
				var date = info.substring(++split);
				var url = '';
				var div = this;
				
				var val = $(this).val();
				if (val == '') {
					val = 0;
				}
				//if (val > 0) {
	                var parameters = [user_id, task, date, val];
	                url = "../add_functions.php?function_name=add_mark&parameters=" + parameters;
	                $.getJSON(url, function(success) {
						if (success != 1) {
							alert("Update not performed. Please try again.");
						} else {
							$.post('../ajax/updateMedalsRanks.php', { user : user_id });
							update(div);
							/*
							$.post('../ajax/updateBpByTaskID.php', {
								task : task, 
								user : user_id 
							});
							*/
						}
					});
				//}
			});
			
			function update(div) {
				if ($(div).hasClass('marked')) {
					$(div).removeClass('marked');
					$(div).addClass('unmarked');
					if (!$(div).hasClass('textInput')) {
						$(div).empty();
					}
				} else if ($(div).hasClass('unmarked')) {
					$(div).removeClass('unmarked');
					$(div).addClass('marked');
					if (!$(div).hasClass('textInput')) {
						$(div).html("<span class='checkmark'>&#10004;</span>");
					}
				}
			}
			
			$("#checkAll").click( function() {
				$("#loading").show();
				var tasks = '';
				var dates = '';
				var boxes = [$(".checkboxDaily"),$(".checkbox")];
				//var boxes = [$(".checkbox")];
				
				$(boxes).each( function(i, e) {
					$(e).each( function() {
						var info = $(this).attr('id');
						var split = info.indexOf(':');
						var task = info.substring(0,split);
						var date = info.substring(++split);
						
						if ($(this).hasClass('unmarked') && !$(this).hasClass('textInput')) {
							tasks += task + ':';
							dates += date + ':';
						}
					})
				});
				
				tasks = tasks.substring(0, tasks.length - 1);
				dates = dates.substring(0, dates.length - 1);
								
				var parameters = [user_id, tasks, dates];
				$.post('/ajax/updateMarks.php', { action : 'add', data : parameters }, function( success ) {
					if (success == false) {
                        alert("Update not performed.");
						$("#loading").hide();
                    } else {
                    	$(boxes).each( function(i, e) {
							$(e).each( function() {
	                    		if ($(this).hasClass('unmarked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('unmarked');
									$(this).addClass('marked');
	                    			$(this).html("<span class='checkmark'>&#10004;</span>");
	                    		}
	                    	});
	                    });
	                    $(".dailyRow").attr("checked", true);
	                    $("#loading").hide();
                    }
				});
				/*
				var url = "../add_functions.php?function_name=add_date_tasks_marks";
				url += "&parameters=" + parameters;
				
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
                    	$(boxes).each( function(i, e) {
							$(e).each( function() {
	                    		if ($(this).hasClass('unmarked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('unmarked');
									$(this).addClass('marked');
	                    			$(this).html("<span class='checkmark'>&#10004;</span>");
	                    		}
	                    	});
	                    });
	                    $(".dailyRow").attr("checked", true);
	                    $("#loading").hide();
                    }
                });
                */
			});
			
			$("#uncheckAll").click( function() {
				var tasks = '';
				var dates = '';
				var boxes = [$(".checkboxDaily"),$(".checkbox")];
				
				$(boxes).each( function(i, e) {
					$(e).each( function() {
						var info = $(this).attr('id');
						var split = info.indexOf(':');
						var task = info.substring(0,split);
						var date = info.substring(++split);
						
						if ($(this).hasClass('marked') && !$(this).hasClass('textInput')) {
							tasks += task + ':';
							dates += date + ':';
						}
					})
				});
				
				tasks = tasks.substring(0, tasks.length - 1);
				dates = dates.substring(0, dates.length - 1);
								
				var parameters = [user_id, tasks, dates];				
				$.post('/ajax/updateMarks.php', { action : 'delete', data : parameters }, function( success ) {
					if (success == false) {
                        alert("Update not performed.");
						$("#loading").hide();
                    } else {
                    	$(boxes).each( function(i, e) {
							$(e).each( function() {
	                    		if ($(this).hasClass('marked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('marked');
									$(this).addClass('unmarked');
	                    			$(this).empty();
	                    		}
	                    	});
	                    });
	                    $(".dailyRow").attr("checked", false);
	                    $("#loading").hide();
                    }
				});
				/*
				var url = "../delete_functions.php?function_name=delete_date_tasks_marks";
				url += "&parameters=" + parameters;
				
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
                    	$(boxes).each( function(i, e) {
							$(e).each( function() { 
	                    		if ($(this).hasClass('marked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('marked');
									$(this).addClass('unmarked');
	                    			$(this).empty();
	                    		}
	                   		});
                    	});
                    	$(".dailyRow").attr("checked", false);
                    }
                });
                */
			});
			
			$(".dailyRow").click( function() {
				var checked = $(this).is(":checked");
				var tasks = '';
				var dates = '';
				var boxes = $(this).parent().next('.dailyBoxes').find('.checkboxDaily');
				
				$(boxes).each( function() {
					var info = $(this).attr('id');
					var split = info.indexOf(':');
					var task = info.substring(0,split);
					var date = info.substring(++split);
					
					if (checked) {
						if ($(this).hasClass('unmarked')) {
							tasks += task + ':';
							dates += date + ':';
						}
					} else {
						if ($(this).hasClass('marked')) {
							tasks += task + ':';
							dates += date + ':';
						}
					}
				});
				
				tasks = tasks.substring(0, tasks.length - 1);
				dates = dates.substring(0, dates.length - 1);
								
				var parameters = [user_id, tasks, dates];
				if (checked) {
					var url = "../add_functions.php?function_name=add_date_tasks_marks";
					url += "&parameters=" + parameters;
				} else {
					var url = "../delete_functions.php?function_name=delete_date_tasks_marks";
					url += "&parameters=" + parameters;
				}
				
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
						$.post('../ajax/updateMedalsRanks.php', { user : user_id });
                    	$(boxes).each( function() {
                    		if (checked) {
	                    		if ($(this).hasClass('unmarked')) {
	                    			$(this).removeClass('unmarked');
									$(this).addClass('marked');
	                    			$(this).html("<span class='checkmark'>&#10004;</span>");
	                    		}
	                    	} else {
	                    		if ($(this).hasClass('marked')) {
	                    			$(this).removeClass('marked');
									$(this).addClass('unmarked');
	                    			$(this).empty();
	                    		}
	                    	}
	                    });
                    }
                });
			});
			
			$(".userMission").each( function() {			
				var user = $(this).attr('id');	
				var user_id = user.substring(user.indexOf('-') + 1);
		    	var image = 'All';
		    	var elem = this;
		    	
				$.ajax({
		            url: '../ajax/getMissionInfo.php', 
		            async: false, 
		            data: {user_id : user_id, type : image}, 
		            success: function(data, textStatus, jqXHR) {
		                data = $.parseJSON(data);
		                
		                var stickers = {
		            		1	: 'Shabbos Mevorchim Tehillim.gif', 
							4	: 'Tefillah.gif',
							12	: 'Mivtzoim.gif',
							13	: 'Niggunim.gif',
							16	: 'Sticker - Hiskashrus outline.png', 
							21	: 'sefer hamitzvos bw.png',
							27	: 'Tanya.gif',
							40	: 'Yomei Dipagra.gif',
							41	: 'Avos Ubonim.gif',
							42	: 'Vihalachta Bidrachov.gif',
							45	: 'Cheshbon Hanefesh.gif',
							90	: 'Chitas.gif',
							100	: 'Sticker - Brias Haguf_outline bw.png'
		            	}
		            	
		            	var str = "<div class='finalFooter'>";
						console.log(data);
		                $.each(data, function(i, val) { 
		                    str += "<span class='footer_info'>";
		                    var j = 0;
		                    var s = stickers;
		                    $.each(val, function(indx, value) {
		                        //build footer info
		                        if (j++ == 0) { //first get sticker info
		                            str += "<img src='stickerOutlines/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
		                        } else { //then get medal info
		                            str += "<i>" + value + " to " + indx + "</i>";
		                        }
		                    });
		                    str += "</span>"; 
		                });
		                str += "</div>";
		                $(elem).find("#" + user_id).append(str);
		            }
		    	});
	        });
		});
	</script>
</html>