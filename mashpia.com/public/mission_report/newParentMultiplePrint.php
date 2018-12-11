<? 
$admin_auth = array('user');
require '../header.php';

if (!isset($_POST)) {
	header("Location: ../chidrens_tasks_new.php");
	exit;
}

$child_ids = explode(':', $_POST['children']);
$period_info = explode(':', $_POST['periods']);

$dates = array();
foreach ($period_info as $period) {
    $info = explode(';', $period);
    $dates['start'][] = $info[0];
    $dates['end'][] = $info[1];
}

require_once 'classes/missions.php';
require_once 'classes/noPicMission.php';
require_once 'classes/picMission.php';

$missions = array();
$numDates = count($dates['start']);
if ( $_POST['method'] == 'byChild' ) {
	foreach ($child_ids as $child) {
        for ( $i = 0; $i < $numDates; $i++ ) {
            $m = new Missions( $dates['start'][$i], $dates['end'][$i], $child );
			$missions[] = $m->getMissions();
		} 
	}
} else if ( $_POST['method'] == 'byWeek' ) {
    for ( $i = 0; $i < $numDates; $i++ ) {
        foreach ($child_ids as $child) { 
            $m = new Missions( $dates['start'][$i], $dates['end'][$i], $child );
			$missions[] = $m->getMissions();
		}
    }       
}

$objMissions = array();
foreach ( $missions as $info ) {
	foreach ( $info as $mission ) {
		$type = $mission->pic_mission_type;
		$objMissions[] = MissionDisplay::getInstance( $type, $mission );
	}
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
			<div id="instructions" class="noPrint" style="margin-top: 10px;">
        	<input type="button" value="Print Settings" 
        		onclick="window.open('instructions.php', '_blank', 'width=400, height=175, menubar=no, scrollbars=no, status=no, toolbar=no, titlebar=no')" />
			<input type="button" value="Print" onclick="window.print()" />
		</div>
		<?		
		foreach ($objMissions as $obj) {
			$id = $obj->user_id;
			echo "<div class='userMission' id='user-" . $id . "'>";
			$obj->printMission();
			echo "</div>";
			echo "<div style='clear: both; page-break-after: always'></div>";
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
				
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentPrint.php?user=" + student + "&start=" + start + "&end=" + end;
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
				window.location.href = "newParentPrint.php?user=" + student + "&start=" + start + "&end=" + end;
			});
			
			$("#change").click( function() {
				var student = $("#student select").val();
				id (student == 1) student = -1;
				var parsha = $("#parsha select").val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentPrint.php?user=" + student + "&start=" + start + 
					"&end=" + end;
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
	    	window.print();
		});
	</script>
</html>