<?
$admin_auth = array('school'); 
require('header.php'); 
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Mishna Settings</title>
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
        <h1>Mishna Settings</h1>
        
        <div class="infobox">
        	Please note: The settings per base, platoon, soldier are INDEPENDANT of each other. In other words if you 
        	create a setting for the platoon it will override the base setting. 
        </div>
        
        <fieldset id="settings">
        	<legend>Points per Line</legend>
        	Please choose how you would like to setup your points per line<br />
        	<input type="radio" name="choice" class="choice" value="1"> 
    		By Base
    		<input type="radio" name="choice" class="choice" value="2"> 
    		By Platoon
    		<input type="radio" name="choice" class="choice" value="3"> 
    		By Soldier
        </fieldset>
        <br />
        
        <fieldset id="schoolPoints">
        	<legend>By School</legend>
        	<table>
        		<tr>
        			<th></th>
        			<th>Points Per Line</th>
        		</tr>
        		<tr>
        			<td>If you learn one line</td>
        			<td><input type='text' size="3" class="points reg" /></td>
        		</tr>
        		<tr>
        			<td>If you learn whole Perek Bevas Achas</td>
        			<td><input type='text' size="3" class="points p_points" /></td>
        		</tr>
        		<tr>
        			<td>If you learn entire Mesechta Bevas Achas</td>
        			<td><input type='text' size="3" class="points m_points" /></td>
        		</tr>
        		<tr>
        			<td>If you learn entire Seder Bevas Achas</td>
        			<td><input type='text' size="3" class="points s_points" /></td>
        		</tr>
        		<tr>
        			<td>If you learn entire Shas Bevas Achas</td>
        			<td><input type='text' size="3" class="points shas_points" /></td>
        		</tr>
        	</table>
        </fieldset>
        
        <fieldset id="platoonPoints">
        	<legend>By Platoon</legend>
        </fieldset>
        
        <fieldset id="studentPoints">
        	<legend>By Student</legend>
        </fieldset>
        
        <br />
        <button id="continue">Continue</button>
	</body>
	
    <script type="text/javascript">
    	$( function() {
    		hideAll();
    		
    		function hideAll() {
    			$("#schoolPoints").hide();
    			$("#platoonPoints").hide();
    			$("#studentPoints").hide();
    			$("#continue").hide();
    		}
    		
    		$("#continue").click( function() {
    			window.location = "assign_mishnos.php";
    		});
    		
    		var id = <?=$admin_user['auths']['school'][0]?>;
    		
    		$(".points").blur( function() {
    			var points = $(this).val();
    			var name = $(this).attr('class');
    			var arr = name.split(' ');
    			var ppl = arr[1];
    			
    			var type;
    			switch (ppl) {
    				case 'reg':
    					type = 'points';
    					break;
    				default:
    					type = ppl;
    					break;
    			}
    			    			
    			if (points > 0) {
	    			$.post('ajax/setMishnaPPL.php', {
	    				school : id, 
	    				points : points, 
	    				type : type
	    			}, function( success ) {
	    				if (success == 1) {
	    					//alert("Updated");
	    				} else {
	    					alert("Error Updating");
	    				}
	    			});
	    		}
			});
    		
    		$(".choice").click( function() {
    			var val = $(this).val();
    			switch (val) {
    				case '1':
    					$.post('ajax/getBasePPL.php', {
    						school : id, 
    					}, function( success ) {
    						var info = $.parseJSON( success );
    						if (info) {
    							$("#schoolPoints .reg").val(info.points);
    							$("#schoolPoints .p_points").val(info.p_points);
    							$("#schoolPoints .m_points").val(info.m_points);
    							$("#schoolPoints .s_points").val(info.s_points);
    							$("#schoolPoints .shas_points").val(info.shas_points);
    						}
    					});
    					
    					$("#schoolPoints").show();
    					$("#platoonPoints").hide();
    					$("#studentPoints").hide();
    					break;
    				case '2':
    					$("#schoolPoints").hide();
    					$("#studentPoints").hide();
    					getPlatoons();
    					break;
    				case '3':
    					$("#schoolPoints").hide();
    					$("#platoonPoints").hide();
    					getStudents();
    					break;
    			}
    			$("#continue").show();
    		});
    		    		
    		function getPlatoons() {
    			$("#platoonPoints").empty();
    			//get classes / users
    			$.get('ajax/getPlatoonPPL.php', {id : id}, function(data) {
    				var grades = $.parseJSON(data);
    				var html = "<legend>By Platoon</legend>";
    				html += "<table><tr><th>Platoon</th><th>If you learn one line</th><th>If you learn whole Perek Bevas Achas</th>";
    				html += "<th>If you learn entire Mesechta Bevas Achas</th><th>If you learn entire Seder Bevas Achas</th>";
    				html += "<th>If you learn entire Shas Bevas Achas</th></tr>";
    				for (var class_id in grades) {
    					for (var grade in grades[class_id]) {
    						var points = grades[class_id][grade]['reg'];
    						var pPoints = grades[class_id][grade]['perek'];
    						var mPoints = grades[class_id][grade]['mesechto'];
    						var sPoints = grades[class_id][grade]['seder'];
    						var shasPoints = grades[class_id][grade]['shas'];
	    					html += "<tr><td>" + grade + "</td><td class='middle'><input type='text' id='" + class_id + "' size='3' class='ppoints reg'";
	    					if (points) html += " value='" + points + "'";
	    					html += " /></td><td class='middle'><input type='text' id='" + class_id + "' size='3' class='ppoints p_points'";
	    					if (pPoints) html += " value='" + pPoints + "'";
	    					html += " /></td><td class='middle'><input type='text' id='" + class_id + "' size='3' class='ppoints m_points'";
	    					if (mPoints) html += " value='" + mPoints + "'";
	    					html += " /></td><td class='middle'><input type='text' id='" + class_id + "' size='3' class='ppoints s_points'";
	    					if (sPoints) html += " value='" + sPoints + "'";
	    					html += " /></td><td class='middle'><input type='text' id='" + class_id + "' size='3' class='ppoints shas_points'";
	    					if (shasPoints) html += " value='" + shasPoints + "'";
	    					html += " /></td></tr>";
	    				}
    				}
    				html += "</table>";
    				$("#platoonPoints").append(html);
    				$("#platoonPoints").show();
    				
    				$(".ppoints").blur( function() {
    					var val = $(this).val();
    					var name = $(this).attr('class');
    					var arr = name.split(' ');
		    			var ppl = arr[1];
		    			
		    			var type;
		    			switch (ppl) {
		    				case 'reg':
		    					type = 'points';
		    					break;
		    				default:
		    					type = ppl;
		    					break;
		    			}		    			

    					if (val > 0) {
	    					var class_id = $(this).attr('id');
	    					$.post('ajax/setMishnaPPL.php', {
	    						school : id, 
	    						grade : class_id, 
	    						points : val, 
	    						type : type
	    					}, function( success ) {
	    						if (success == 1) {
	    							//alert("Updated");
	    						} else {
	    							alert("Error updating");
	    						}
	    					});
	    				}
    				});
    			});
    		}
    		
    		function getStudents() {
    			$("#studentPoints").empty();
    			//get classes / users
    			$.get('ajax/getUserPPL.php', {id : id}, function(data) {
    				var info = $.parseJSON(data);
    				var html = "<legend>By Student</legend>";
    				html += "<table><tr><th>Platoon</th><th>Soldier</th><th>If you learn one line</th><th>If you learn whole Perek Bevas Achas</th>";
    				html += "<th>If you learn entire Mesechta Bevas Achas</th><th>If you learn entire Seder Bevas Achas</th>";
    				html += "<th>If you learn entire Shas Bevas Achas</th></tr>";
    				for (var class_id in info) {
    					for (var grade in info[class_id]) {
        					for (var user_id in info[class_id][grade]) { 
        						for (var user in info[class_id][grade][user_id]) {
        							var points = info[class_id][grade][user_id][user]['reg'];
		    						var pPoints = info[class_id][grade][user_id][user]['perek'];
		    						var mPoints = info[class_id][grade][user_id][user]['mesechto'];
		    						var sPoints = info[class_id][grade][user_id][user]['seder'];
		    						var shasPoints = info[class_id][grade][user_id][user]['shas'];
	        						html += "<tr><td>" + grade + "</td><td>";
	        						html += user + "</td><td class='middle'><input type='text' id='" + class_id + ':' + user_id + "' class='spoints reg' size='3'";
	        						if (points) {
	        							html += " value='" + points + "'";
	        						}
	        						html += " /></td><td class='middle'><input type='text' id='" + class_id + ':' + user_id + "' class='spoints p_points' size='3'";
	        						if (pPoints) {
	        							html += " value='" + pPoints + "'";
	        						}
	        						html += " /></td><td class='middle'><input type='text' id='" + class_id + ':' + user_id + "' class='spoints m_points' size='3'";
	        						if (mPoints) {
	        							html += " value='" + mPoints + "'";
	        						}
	        						html += " /></td><td class='middle'><input type='text' id='" + class_id + ':' + user_id + "' class='spoints s_points' size='3'";
	        						if (sPoints) {
	        							html += " value='" + sPoints + "'";
	        						}
	        						html += " /></td><td class='middle'><input type='text' id='" + class_id + ':' + user_id + "' class='spoints shas_points' size='3'";
	        						if (shasPoints) {
	        							html += " value='" + shasPoints + "'";
	        						}
	        						html += " /></td></tr>";
        						}
        					}
        				}
    				}
    				html += "</table>";
    				$("#studentPoints").append(html);
    				$("#studentPoints").show();
    				
    				$(".spoints").blur( function() {
    					var val = $(this).val();
    					if (val > 0) {
	    					var info = $(this).attr('id');
	    					var pos = info.indexOf(':');
	    					var class_id = info.substring(0,pos);
	    					var user_id = info.substring(pos+1);
	    					
	    					var name = $(this).attr('class');
			    			var arr = name.split(' ');
			    			var ppl = arr[1];
			    			
			    			var type;
			    			switch (ppl) {
			    				case 'reg':
			    					type = 'points';
			    					break;
			    				default:
			    					type = ppl;
			    					break;
			    			}
		    			
	    					$.post('ajax/setMishnaPPL.php', {
	    						school : id, 
	    						grade : class_id, 
	    						user : user_id, 
	    						points : val, 
	    						type : type
	    					}, function( success ) {
	    						if (success == 1) {
	    							//alert("Updated");
	    						} else {
	    							alert("Error updating");
	    						}
	    					});
	    				}
    				});
    			});
    		}
    	});
	</script>
</html>