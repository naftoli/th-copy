<? 
$admin_auth = array('user');
require '../header.php';

// pesach 5777
$dates['start'] = array(2457851, 2457858);
$dates['end'] = array(2457857, 2457864);

include("../classes/admin.php");
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
	}
}

if (isset($_GET['user']) && $_GET['user'] != -1) {
	$user_id = $_GET['user'];
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Print Missions</title>
		<link rel="stylesheet" href="newStyle.css?v=2.2" type="text/css" />
	</head>
	
	<body>
		<!--
		<div id="oldMissions" align="center">
			<a href="../parents_print_report.php">Take me back to the old style mission sheets!</a><br />
			Click <a href="../parentSettings.php">here</a> to add or remove pictures from mission sheets.</a>
		</div>
		-->
		<div id="marking">
			<div id="outerParent" style="margin-left: 38%">
				<div id="student">
					<div class="arrow-left"></div>
					<select name='user' id="user">
						<option value='-1'>All Children</option>
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
				<!--
				<div>
					<button id='change'>Change</button>
				</div>
				-->
			</div>
		</div>
		<p class="noPrint" style="margins: auto; text-align: center; color: red; margin-top: -10px; margin-bottom: 20px;">IMPORTANT: Printing Missions will only work properly in FIREFOX BROWSER<br />with the settings outlined in "Print Instructions"</p>
		<div id="instructions" class="noPrint">
        	<input type="button" value="Print Instructions" 
        		onclick="window.open('instructions.php', '_blank', 'width=450, height=375, menubar=no, scrollbars=no, status=no, toolbar=no, titlebar=no')" />
			<input type="button" value="Print" onclick="window.print()" />
			<!--<input type="button" value="Back" onclick="window.location = '../admin.php'" />-->
		</div>
		<?
		require_once 'classes/missions.php';
		require_once 'classes/noPicMission.php';
		require_once 'classes/picMission.php';
		
		$numMissions = count($dates['start']);
		$missions = array();
		for ($i = 0; $i < $numMissions; $i++) {
			$startReport = $dates['start'][$i];
			$endReport = $dates['end'][$i];	
			if ( isset($user_id) ) {
				$m = new Missions( $startReport, $endReport, $user_id );
				$missions[] = $m->getMissions();
			} else {
				foreach ($children as $child) {
					$m = new Missions( $startReport, $endReport, $child->user_id );
					$missions[] = $m->getMissions();
				}
			}
			
			$objMissions = array();
			foreach ( $missions as $info ) {
				foreach ( $info as $mission ) {
					//$type = $mission->pic_mission_type;
					$type = 1;
					$objMissions[] = MissionDisplay::getInstance( $type, $mission );
				}
			}
			
			foreach ($objMissions as $obj) {
				$id = $obj->user_id;
				if ($obj->lang_id == 1) {
					echo "<div class='userMission' id='user-" . $id . "' >";
				} else if ($obj->lang_id == 2) {
					echo "<div class='userMission he' id='user-" . $id . "' dir='rtl' >";
				}
				$obj->printMission();
				echo "</div>";
				echo "<div style='clear: both; page-break-after: always'></div>";
			}
		}
		?>
	</body>
	
	<script src="../jquery.js"></script>
	<script>
		$( function() {
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
				window.location.href = "newParentPrintYT.php?user=" + student;
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
				window.location.href = "newParentPrintYT.php?user=" + student;
			});
			
			$("#user").change( function() {
				var student = $(this).val();
				window.location.href = "newParentPrintYT.php?user=" + student;
			});
			
			$("#change").click( function() {
				var student = $("#student select").val();
				if (student == 1) student = -1;
				window.location.href = "newParentPrintYT.php?user=" + student;
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
	    	//window.print();
		});
	</script>
</html>