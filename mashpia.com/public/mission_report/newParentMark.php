<? 
$admin_auth = array('user');
require '../header.php';

$d = unixtojd();
$day = date("N");
$end = $d;

switch ($day) {
    case 1:
        $end += 3;
        break;
    case 2:
        $end += 2;
        break;
    case 3:
        $end += 1;
        break;
    case 4:
        break;
    case 5:
		$end += 6;
        break;
    case 6:
        $end += 5;
        break;
    case 7:
		$end += 4;
        break;
    default:
        break;
}
$start = $end - 27;
$start = 2457991;


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

include("../classes/admin.php");
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
	}
}

if (isset($_GET['user'])) {
	$user_id		= $_GET['user'];
	$startReport 	= $_GET['start'];
	$endReport		= $_GET['end']; 
} else {
	$user_id 		= $children[0]->user_id;
	$startReport 	= $end - 6;
	$endReport 		= $end;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Print Missions</title>
		<link rel="stylesheet" href="newStyle.css?v2.2" type="text/css" />
	</head>
	
	<body>
		<!--
		<div id="oldMissions" align="center">
			<a href="../parents_date_tasks_report_new.php">Take me back to the old style mission sheets!</a><br />
			Click <a href="../parentSettings.php">here</a> to change mission settings</a>
		</div>
		-->
		<div id="marking">
			<div id="outerParent">
				<div id="student">
					<div class="arrow-left"></div>
					<select name='user' id="user">
						<?
						foreach ($children as $child) {
							$id = $child->user_id;
							$name = $child->first . ' ' . $child->last;
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
					<select name='parsha' id="parshaSelection">
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
				<!--
				<div>
					<button id='change'>Change</button>
				</div>
				-->
			</div>
		</div>
		<div style="clear: both"></div>
		<?
		require_once 'classes/missions.php';
		require_once 'classes/noPicMission.php';
		require_once 'classes/picMission.php';
		
		$m = new Missions( $startReport, $endReport, $user_id );
		$missions = $m->getMissions();
		
		$objMissions = array();
		foreach ( $missions as $mission ) {
			$type = $mission->pic_mission_type;
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
	</body>
	<script src="../scripts/functions.js"></script>
	<script src="../jquery.js"></script>
	<script>
		$( function() {
			$("#oneCol").click( function() {
				var user = <?=$user_id?>;
				var start = <?=$startReport?>;
				var end = <?=$endReport?>;
				var cols = 1;
				var str = "newParentMark.php?user=" + user + "&start=" + start + "&end=" + end + "&col=" + cols;
				window.location = str;
			});
			
			$("#twoCols").click( function() {
				var user = <?=$user_id?>;
				var start = <?=$startReport?>;
				var end = <?=$endReport?>;
				var cols = 2;
				var str = "newParentMark.php?user=" + user + "&start=" + start + "&end=" + end + "&col=" + cols;
				window.location = str;
			});
			
			$(".arrow-left").click( function() {
				var val = $(this).parent().find("option:selected").prev().val();
				var type = $(this).parent().attr('id');
				if (val === undefined) {
					alert("There are no previous " + type + "s.");
					return;
				}
				
				var student, parsha;
				switch (type) {
					case 'student':
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						student = $("#student select").val();
						parsha = val;
				}
				
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentMark.php?user=" + student + "&start=" + start + "&end=" + end;
			});
			
			$(".arrow-right").click( function() {
				var val = $(this).parent().find("option:selected").next().val();
				var type = $(this).parent().attr('id');
				if (val === undefined) {
					alert("There are no more " + type + "s.");
					return;
				}
				
				var student, parsha;
				switch (type) {
					case 'student':
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						student = $("#student select").val();
						parsha = val;
				}
				
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentMark.php?user=" + student + "&start=" + start + "&end=" + end;
			});
			
			$("#user").change( function() {
				var student = $(this).val();
				var parsha = $("#parsha select").val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentMark.php?user=" + student + "&start=" + start + 
					"&end=" + end;
			});
			
			$("#parshaSelection").change( function() {
				var student = $("#student select").val();
				var parsha = $(this).val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentMark.php?user=" + student + "&start=" + start + 
					"&end=" + end;
			});
			
			$("#change").click( function() {
				var student = $("#student select").val();
				var parsha = $("#parsha select").val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentMark.php?user=" + student + "&start=" + start + 
					"&end=" + end;
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
			
			$(".checkbox").click( function() {
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
				var tasks = '';
				var dates = '';
				var boxes = [$(".checkboxDaily"),$(".checkbox")];
				
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
				/*
				var parameters = [user_id, tasks, dates];
				$.post('/ajax/updateMarks.php', { action : 'add', data : parameters }, function( success ) {
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
                    }
				});
				*/
													
				var parameters = [user_id, tasks, dates];
				var url = "../add_functions.php?function_name=add_date_tasks_marks";
				url += "&parameters=" + parameters;
				//alert(url);
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
						$.post('../ajax/updateMedalsRanks.php', { user : user_id });
                    	$(boxes).each( function(i, e) {
							$(e).each( function() {
	                    		if ($(this).hasClass('unmarked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('unmarked');
									$(this).addClass('marked');
	                    			$(this).html("<span class='checkmark'>&#10004;</span>");
	                    		}
	                    	});
	                    });
                    }
                });
                
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
				var url = "../delete_functions.php?function_name=delete_date_tasks_marks";
				url += "&parameters=" + parameters;
				
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
						$.post('../ajax/updateMedalsRanks.php', { user : user_id });
                    	$(boxes).each( function(i, e) {
							$(e).each( function() { 
	                    		if ($(this).hasClass('marked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('marked');
									$(this).addClass('unmarked');
	                    			$(this).empty();
	                    		}
	                   		});
                    	});
                    }
                });
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