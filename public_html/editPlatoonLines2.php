<?
$admin_auth = array('school');
require_once 'header.php';

require 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get campaigns for current year
$sql = "select * from line_campaigns where year = " . $year;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	if (strtolower($row['type']) == 'tanya') $tanyaCampaign = $row['id'];
	else if (strtolower($row['type']) == 'mishna') $mishnaCampaign = $row['id'];
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Edit Tanya / Mishna Lines</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			tr, th, td {
				padding: 2px;
				font-size: 11px;
			}
			.middle {
				text-align: center;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Edit Tanya / Mishna Lines</h1>
		
		<div class="loading">
       		<img src="images/loading.gif" />
       	</div>
	</body>
	
	<script>
		$( function() {
			var tanyaCampaign = <?=$tanyaCampaign;?>;
			var mishnaCampaign = <?=$mishnaCampaign;?>;
			
			$(".loading").load('ajax/getPlatoonBP.php', function() {
				$(".tanya").blur( function() {
					var id = $(this).parent().parent().find('.classID').val();
					var school = $(this).parent().parent().find('.schoolID').val();
					var num = $(this).val().trim();
					var num = num.replace(/\,/g,'');
		        	if (isNaN(num)) {
		        		alert("You must enter a number.");
		        		return;
		        	}
		        	
		        	var str = $(this).attr('class');
					if (str.indexOf('pledge') != -1) {
						var table = 'lines_pledged';
					} else if (str.indexOf('learn') != -1) {
						var table = 'lines_learned';
					}
						
		        	$.post('ajax/updateBalPehCampaign.php', {
		        		id : tanyaCampaign, 
		        		val : num, 
		        		grade : id, 
		        		school : school, 
		        		table : table, 
		        		updateSummary : 1
		        	}, function( data ) {
		        		if (data == 1) {
		        			alert("Updated.");
		        		} else {
		        			alert("Error updating.");
		        		}
		        	});
				});
				
				$(".mishna").blur( function() {
					var id = $(this).parent().parent().find('.classID').val();
					var school = $(this).parent().parent().find('.schoolID').val();
					var num = $(this).val().trim();
					var num = num.replace(/\,/g,'');
		        	if (isNaN(num)) {
		        		alert("You must enter a number.");
		        		return;
		        	}
		        	
		        	var str = $(this).attr('class');
					if (str.indexOf('pledge') != -1) {
						var table = 'lines_pledged';
					} else if (str.indexOf('learn') != -1) {
						var table = 'lines_learned';
					}
						
		        	$.post('ajax/updateBalPehCampaign.php', {
		        		id : mishnaCampaign, 
		        		val : num, 
		        		grade : id, 
		        		school : school, 
		        		table : table, 
		        		updateSummary : 1
		        	}, function( data ) {
		        		if (data == 1) {
		        			alert("Updated.");
		        		} else {
		        			alert("Error updating.");
		        		}
		        	});
				});
			});
		});
	</script>
</html>