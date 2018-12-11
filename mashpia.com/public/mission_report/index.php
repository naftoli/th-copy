<?
require '../db.php';
$agent = $_SERVER['HTTP_USER_AGENT'];
include 'getTasks.php';
$days_of_week = array("F", "ש", "S", "M", "T", "W", "T");

$subjectIcons = array(
	1	=>	'Tehillim.png',
	4	=>	'Tefilla.png',
	12	=>	'Mivtzoim.png',
	13	=>	'Niggunim.png',
	16	=>	'hiskashrus.png',
	21	=>	'sefer hamitzvos.png',
	27	=>	'',
	40	=>	'Yom Dipagra.png',
	41	=>	'Father Son.png',
	42	=>	'Footsteps.png',
	45	=>	'Cheshbon Hanefesh.png',
	90	=>	'Chitas.png',
	100	=>	'Brias Haguf.png'
);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Mission Sheets</title>
	<?
	if (strpos($agent, 'Firefox')) {
		echo "<link rel='stylesheet' type='text/css' href='style.css' />";
	} else if (strpos($agent, 'Chrome')) {
		echo "<link rel='stylesheet' type='text/css' href='style2.css' />";
	}
    ?>
	<title>Mission Sheets</title>
	<script src="js/jquery.js"></script>
</head>

<body>

<? require 'create_report.php'; ?>

	<script>
		$(function() {
			var user_id = <?=$user_id?>;
	    	var image = 'All';
	    	
			$.ajax({
	            url: '../ajax/getMissionInfo.php', 
	            async: false, 
	            data: {user_id : user_id, type : image}, 
	            success: function(data, textStatus, jqXHR) {
	                data = $.parseJSON(data);
	                var stickers = {
	            		1	:	'Sticker - WWTC bw.png',
						4	:	'Sticker - Tefilah bw.png',
						12	:	'Sticker - Mivtzoim bw.png',
						13	:	'Sticker - Nigunnim bw.png',
						16	:	'Sticker - Hiskashrus bw.png',
						21	:	'Sticker - Sefer Hamitzvos bw.png',
						27	:	'Sticker - Tanya bw.png',
						40	:	'Sticker - Yomei Dipagra bw.png',
						41	:	'Sticker - Avos Ubanim b w.png',
						42	:	'Sticker - Halachta Bidrachav bw.png',
						45	:	'Sticker - Cheshbon Hanefesh bw.png',
						90	:	'Sticker - Chitas bw.png',
						100	:	'Sticker - Brias Haguf_outline bw.png'
	            	}
	            	var str = "<div>";
	                $.each(data, function(i, val) { 
	                    str += "<span class='footer_info'>";
	                    var j = 0;
	                    var s = stickers;
	                    $.each(val, function(indx, value) {
	                        //build footer info
	                        if (j++ == 0) { //first get sticker info
	                            str += "<img src='image/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
	                        } else { //then get medal info
	                            str += "<i>" + value + " to " + indx + "</i>";
	                        }
	                    });
	                    str += "</span>"; 
	                });
	                str += "</div>";
	                $("#" + user_id).append(str);
	            }
	         });
		});
	</script>
	
</body>
</html>