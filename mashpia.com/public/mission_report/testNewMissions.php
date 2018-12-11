<?
require_once 'slimDB.php';
chdir("classes");
require_once 'missions.php';
require_once 'noPicMission.php';
require_once 'picMission.php';
require_once 'picMission2.php';

chdir("../");

$user_id = 20923;
$start = 2457872;
$end = 2457878;

$m = new Missions( $start, $end, $user_id );
$missions = $m->getMissions();
//echo "<pre>"; print_r( $missions ); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Missions</title>
		<link rel="stylesheet" href="newStyle.css?v1.3" type="text/css" />
	</head>
	
	<body>
		<?
		$objDisplay = array();
		foreach ( $missions as $mission ) {
			//$objDisplay[] = MissionDisplay::getInstance( 1, $mission );
			$objDisplay[] = new PicMission2( $mission );
		}
		
		foreach ( $objDisplay as $obj ) {
			$id = $obj->user_id;
			echo "<div class='userMission' id='user-" . $id . "'>";
			$obj->setMissionType(2);
			$obj->printMission();
			echo "</div>";
		}
		?>
	</body>
	
	<script src="../jquery.js"></script>
	<script>
		$(function() {
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