<?php
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
			th, .middle {
				text-align: center;
			}
			.runningTotals {
				line-height: 1.2;
				font-size: 14px;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Edit Tanya / Mishna Lines</h1>
		
		<div class="info" align="center" style="font-size: 14px; line-height: 1.3">
			PLEASE NOTE: The amounts which are shown under the Tanya Learned and Mishna Learned columns, will only sync onto the ourbirthdaygift.com website
			if you click "Confirm & Sync" next to each child. The column titled "Parent Entered" is there for your convenience. They do not "sync" anywhere.
			<br /><br />
			If ALL amounts are correct, just click "Confirm All" on the top near the totals.
			<br />
			<!--<span style="color:red">If a parent entered a larger amount, their amount will over-ride yours.</span>-->
		</div>
<?php
require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$schoolIDs = array();
foreach ($schools as $id => $school) {
	$schoolIDs[] = $id;
}

$classes = array();
$classNames = array();
foreach ($schoolIDs as $id) {
	$sql = "select * from classes where school_id = " . $id . " and class_era = 0 order by class_grade, class_sub";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$classes[$id][] = $row['class_id'];
		$classNames[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	}
}

$users = array();
$userNames = array();
foreach ($classes as $school => $grades) {
	foreach ($grades as $grade) {
		$sql = "select * from users where class_id = " . $grade . " and (user_registered > 0 or yan = 1) order by last, first";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result))	{
				$users[$school][$grade][] = $row['user_id'];
				$userNames[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			}
		}
	}
}

require_once 'class.bpSummary.php';
$bpsTanya = new BpSummary( $tanyaCampaign, 'school' );
$bpsMishna = new BpSummary( $mishnaCampaign, 'school' );

require_once 'class.balPehCampaign.php';
$tanya = BalPehCampaign::getInstance($tanyaCampaign);
$mishna = BalPehCampaign::getInstance($mishnaCampaign);

foreach ($schools as $id => $name) {
	$tanyaSummary = $bpsTanya->getSummary($id) ? $bpsTanya->getSummary($id) : 0;
	$mishnaSummary = $bpsMishna->getSummary($id) ? $bpsMishna->getSummary($id) : 0;
	?>
	<br />
	<div id="school_<?=$id?>">
		<div style="float: right">
			<button class="copy">Copy Parent Entered to Tanya Learned</button><br />
		</div>
		<div class="runningTotals">
			Total Tanya Learned: <span class="tlearned"></span><br />
			Total Tanya Synced to Ourbirthdaygift.com: <?=$tanyaSummary?><br />
			Total Mishna Learned: <span class="mlearned"></span><br />
			Total Mishna Synced to Ourbirthdaygift.com: <?=$mishnaSummary?><br />
			<input type="hidden" class="schoolID" value="<?=$id?>" />
			<button class="confirmAll">Confirm ALL</button>
		</div>
		<?
		echo "<h2>" . $name . "</h2>";
		$grandTotals['tanya']['pledged'] = 0;
		$grandTotals['tanya']['learned'] = 0;
		$grandTotals['mishna']['pledged'] = 0;
		$grandTotals['mishna']['learned'] = 0;
		?>
		<!--
		<p>
			<input type='hidden' class='schoolID' value='<?=$id?>' />
			Change all 
			<select name='type' class='type'>
				<option value='5'>Tanya</option>
				<option value='6'>Mishna</option>
			</select> 
			pledges for 
			<select name='grade' class='grade'>
				<option value="-1">All Platoons</option>
				<?
				//foreach ($classes[$id] as $class) {
				//	echo "<option value='" . $class . "'>" . $classNames[$class] . "</option>";
				//}
				?>
			</select> 
			to <input type='text' name='change' class='change' size='3' />
			<input type='submit' name='submit' class='submit' value='change' />
		</p>
		-->
		<table id="<?=$id?>">
			<tr>
				<th>Grade</th>
				<th>Student</th>
				<!--<th>Tanya Pledged</th>-->
				<th>Tanya Learned</th>
				<th>Parent Entered</th>
				<!--<th>Mishna Pledged</th>-->
				<th>Mishna Learned</th>
				<th>Sync with Website</th>
			</tr>
			<!--
			<tr>
				<th colspan="2">Totals</th>
				<th id="tpledged"></th>
				<th id="tlearned"></th>
				<th id="mpledged"></th>
				<th id="mlearned"></th>
				<th></th>
			</tr>
			-->
			<?
			//$numUsers = 0;
			foreach ($classes[$id] as $class) {
				//$numUsers += count($users[$id][$class]);
				foreach ($users[$id][$class] as $user) {
					//$totalTanya['pledged'] = $tanya->getTotalPledged( 'user' , $user );
					$totalTanya['learned'] = $tanya->getTotalLearned( 'user', $user );
					$totalTanya['parentEntered'] = $tanya->getParentEntered( $user );
	
					//$totalMishna['pledged'] = $mishna->getTotalPledged( 'user', $user );
					$totalMishna['learned'] = $mishna->getTotalLearned( 'user', $user );
					
					//$grandTotals['tanya']['pledged'] += $totalTanya['pledged'];
					$grandTotals['tanya']['learned'] += $totalTanya['learned'];
					//$grandTotals['mishna']['pledged'] += $totalMishna['pledged'];
					$grandTotals['mishna']['learned'] += $totalMishna['learned'];
					
					echo "<tr><td>" . $classNames[$class] . "</td><td>" . $userNames[$user] . 
						"<input type='hidden' class='userID' value='" . $user . "' />
						<input type='hidden' class='classID' value='" . $class . "' /> 
						<input type='hidden' class='schoolID' value='" . $id . "' /></td>
						<td class='middle'><input type='text' size='5' class='tanya learn tlearn' value='" . $totalTanya['learned'] . "' /></td>
						<td class='parentEntered'>" . ($totalTanya['parentEntered'] > 0 ? $totalTanya['parentEntered'] : '') . "</td> 
						<td class='middle'><input type='text' size='5' class='mishna learn mlearn' value='" . $totalMishna['learned'] . "' /></td>
						<td><button class='sync'>Confirm & Sync</button></tr>";
					
					//echo "<tr><td>" . $classNames[$class] . "</td><td>" . $userNames[$user] . 
					//	"<input type='hidden' class='userID' value='" . $user . "' />
					//	<input type='hidden' class='classID' value='" . $class . "' /> 
					//	<input type='hidden' class='schoolID' value='" . $id . "' /></td>
					//	<td class='middle'><input type='text' size='5' class='tanya pledge' value='" . $totalTanya['pledged'] . "' /></td>
					//	<td class='middle'><input type='text' size='5' class='tanya learn tlearn' value='" . $totalTanya['learned'] . "' /></td>
					//	<td class='parentEntered'>" . ($totalTanya['parentEntered'] > 0 ? $totalTanya['parentEntered'] : '') . "</td> 
					//	<td class='middle'><input type='text' size='5' class='mishna pledge' value='" . $totalMishna['pledged'] . "' /></td>
					//	<td class='middle'><input type='text' size='5' class='mishna learn mlearn' value='" . $totalMishna['learned'] . "' /></td>
					//	<td><button class='sync'>Confirm & Sync</button></tr>";
				 }
			}
			//echo "<input type='hidden' name='numUsers' value='" . $numUsers . "' />";
			?>
			<script>
				var tlearned = <?=$grandTotals['tanya']['learned']?>;
				var mlearned = <?=$grandTotals['mishna']['learned']?>;
				
				var id = <?=$id?>;
				var school_id = "#school_" + id;
				$(school_id + " .tlearned").text(tlearned);
				$(school_id + " .mlearned").text(mlearned);
			</script>
		</table>
	</div>
<? } ?>		
	</body>
	
	<script>
		function calcTotal( school, type ) {
			if (type == 'tanya') {
				var total = 0;
				var table = $("#" + school);
				$(table).find('.tlearn').each( function() {
                    var val = parseInt($(this).val());
                    if (val > 0) total += parseInt($(this).val());
				});
				$("#tlearned").text(total);
			} else if (type == 'mishna') {
				var total = 0;
				var table = $("#" + school);
				$(table).find('.mlearn').each( function() {
					var val = parseInt($(this).val());
                    if (val > 0) total += parseInt($(this).val());
				});
				$("#mlearned").text(total);
			}
		}
			
		$( function() {
			var tanyaCampaign = <?=$tanyaCampaign?>;
			var mishnaCampaign = <?=$mishnaCampaign?>;
			
			$(".confirmAll").click( function() {
				var schoolID = $(this).parent().find('.schoolID').val();
				$.post('ajax/updateBpSchoolSummary.php', { school : schoolID, campaigns : [tanyaCampaign, mishnaCampaign] }, function(error) {
					if (parseInt(error) == 1) {
						alert('Error Updating.');
					} else {
						alert('Updated.');
					}
				});
			});
			
			$(".sync").click( function() {
				var id = $(this).parent().parent().find('.userID').val();
				$.post('ajax/updateBpUserSummary.php', { user : id, campaigns : [tanyaCampaign, mishnaCampaign] }, function(error) {
					if (parseInt(error) == 0) {
						alert('updated.');
						location.href = 'editSoldierLines2.php';
					}
				});
			});
			
			//$(".loading").load('ajax/getSoldierBP.php', function() {
				$(".tanya").keyup( function(event) {
					if (event.keyCode == 9) {return false} // do not run if the key is a TAB
					var id = $(this).parent().parent().find('.userID').val();
					var grade = $(this).parent().parent().find('.classID').val();
					var school = $(this).parent().parent().find('.schoolID').val();
					var num = $(this).val().trim();
					var num = num.replace(/\,/g,'');
		        	if (isNaN(num)) {
		        		alert("You must enter a number.");
		        		return;
		        	}
		        	
					var table;
		        	var str = $(this).attr('class');
					if (str.indexOf('pledge') != -1) {
						table = 'lines_pledged';
					} else if (str.indexOf('learn') != -1) {
						table = 'lines_learned';
						calcTotal(school, 'tanya');
					}
					
		        	$.post('ajax/updateBalPehCampaign.php', {
		        		id : tanyaCampaign, 
		        		val : num, 
		        		user : id, 
		        		grade: grade, 
		        		school: school, 
		        		table : table
		        	}, function( data ) {
		        		if (data == 1) {
		        			//alert("Updated.");
							/*
		        			$.post('ajax/updateBpSummary.php', {
		        				campaign : tanyaCampaign, 
		        				user : id, 
		        				grade : grade, 
		        				school : school 
		        			});
		        			*/
		        		} else if (data == 0) {
		        			alert("Error updating.");
		        		}
		        	});
				});
				
				$(".mishna").keyup( function(event) {
					if (event.keyCode == 9) {return false} // do not run if the key is a TAB
					var id = $(this).parent().parent().find('.userID').val();
					var grade = $(this).parent().parent().find('.classID').val();
					var school = $(this).parent().parent().find('.schoolID').val();
					var num = $(this).val().trim();
					var num = num.replace(/\,/g,'');
		        	if (isNaN(num)) {
		        		alert("You must enter a number.");
		        		return;
		        	}
		        	
					var table;
		        	var str = $(this).attr('class');
					if (str.indexOf('pledge') != -1) {
						table = 'lines_pledged';
					} else if (str.indexOf('learn') != -1) {
						table = 'lines_learned';
						calcTotal(school, 'mishna');
					}
					
		        	$.post('ajax/updateBalPehCampaign.php', {
		        		id : mishnaCampaign, 
		        		val : num, 
		        		user : id, 
		        		grade : grade,
		        		school : school, 
		        		table : table
		        	}, function( data ) {
		        		if (data == 1) {
		        			//alert("Updated.");
							/*
		        			$.post('ajax/updateBpSummary.php', {
		        				campaign : mishnaCampaign, 
		        				user : id, 
		        				grade : grade, 
		        				school : school 
		        			});
		        			*/
		        		} else if (data == 0) {
							console.log({
								id : mishnaCampaign, 
								val : num, 
								user : id, 
								grade : grade,
								school : school, 
								table : table
							});
		        			alert("Error updating.");
		        		}
		        	});
				});
				//
				//$(".submit").click( function() {
				//	var grade = $(this).parent().find('.grade').val();
				//	var school = $(this).parent().find('.schoolID').val();
				//	var type = $(this).parent().find('.type').val();
				//	var val = $(this).parent().find('.change').val();
				//	
				//	$.post('ajax/changeLines.php', {
				//		grade : grade, 
				//		school : school, 
				//		type : type, 
				//		val : val
				//	}, function( success ) {
				//		if (success == 1) {
				//			//alert(success); 
				//			window.location = 'editSoldierLines2.php'; 
				//		} else {
				//			//alert(success);
				//			alert("Error.");
				//		}
				//	});
				//});
			//});
			$(".copy").click( function() {
				var confirm = window.confirm("Are you sure you want to copy all numbers (this will overwrite what has been entered by you until now)?");
				if (confirm) {
					var id = $(this).parent().parent().find('table').attr('id');
					$("#" + id).find("tr").each(function() {
						var num = $(this).find('.parentEntered').text();
						if (parseInt(num) > 0) {
							$(this).find('.tlearn').val(num);
							$(this).find('.tanya').trigger('keyup');
						}
					});
				}
			});
		});
	</script>
</html>