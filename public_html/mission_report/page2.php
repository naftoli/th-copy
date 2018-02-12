<?
require '../db.php';
include 'getTasks2.php';
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
	<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<title>Mission Sheets</title>
	<link href="style.css" rel="stylesheet">
	<title>Mission Sheets</title>
	
	<script src="js/jquery.js"></script>
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
                	var str = "";
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
                    $("#footer").append(str);
                }
             });
		});
	</script>
</head>

<body>

<div class="wrapper">

	<div class="header">
		<? if (isset($user->user_photo_id)) { ?>
			<div style="float: right; padding-left: 5px;">
				<img src="../file_view.php?id=<?=$user->user_photo_id;?>" width="60" alt=""/>
			</div>
		<?
		}
		$sql = "select logo from schools where school_id = " . $user->school_class->school_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		?>
		<div style="float: left">
			<img src="../schoolLogos/<?=empty($row['logo']) ? 'TH-Blank Logo.gif' : $row['logo']?>" width="80" alt=""/>
		</div>
    	<table>
    		<tr>
    			<td class="headtitle">
					<span class="sb">Platoon:</span> 
					<?=$user->school_class->class_grade;?>-<?=$user->school_class->class_sub;?><br>
					<span class="sb">Commander:</span><br>
					<?=$user->school_class->class_teacher;?>
    			</td>
    			<td>
    				<div class="headerImage">
    					<img src="image/missionHeader.gif" width="350" />
    				</div>
    			</td>
    			<td class="headtitle">
					<?=$user->rank_name?><br />
					<span class="b"><?=$user->first . ' ' . $user->last?></span>
    			</td>
    		</tr>
    		<tr>
    			<td colspan="3" class="line"><span class="hebrew-text">פרשת <?=$parsha?> &#10022; <?=$heDates[0]?> - <?=$heDates[6]?></span></td>
    		</tr>
	  </table>
	
    </div><!-- .header-->
      		
	<div class="left-sidebar"> 
<?
$firstRow = true;
$totalRendered = 0;
$page = 1;
$labelAdded = false;

$numDaily = count($user->daily_labels);
if ($numDaily) {
	foreach ($user->sorted_daily_labels as $key0 => $value) {
		for ($dlno = 0; $dlno < $numDaily; $dlno++) {
			$key1 = $user->daily_labels[$dlno]; 
            $info = explode(":", $key1); 
            $label = $info[0]; 
            if ($value == $user->daily_labels[$dlno]) {
            	$totalRendered++;
				$labelAdded = true;
			?>
		 		<div class="label"><?=$label?></div>
				<? 
				$numTasks = count($user->daily_tasks);
				for ($dtno = 0; $dtno < $numTasks; $dtno++) {
					if ($user->daily_tasks[$dtno]->label_name == $label) {
						$daily_task = $user->daily_tasks[$dtno]; 
						?>
			            <div class="row">
							<div class="rowImg">
								<img src="image/<?=$subjectIcons[$daily_task->subject_id]?>" width="50" height="52" alt=""/>
							</div>
							<? if ($daily_task->focus_task) { ?>
								<div class="focus">
									<img src="image/31204.png" width="60" height="17" alt="" />
								</div>
							<? } ?>
							<div class="short"><?=($daily_task->short_name == '' ? '<br />' : $daily_task->short_name)?></div>
							<div class="task"><?=$daily_task->task_name?></div>
					    </div>
					    
					    <div>
							<table>
								<tr>
									<? foreach ($days_of_week as $index => $day) { ?>
									  	<td>
											<div class="checkboxDaily">
										 		<? if ($firstRow) { ?>
											 		<div style="line-height: 1">
												 		<span class="hebrew"><?=$heDatesDisp[$index]?></span><br />
												 		<strong><?=$day?></strong>
												 	</div>
										 		<? } ?>
										 	</div>
										</td>
									<? } ?>
									<? $firstRow = false; ?>
								</tr>
							</table>
						 </div>
						<?
						$totalRendered++;
						//echo $totalRendered;
						$num = $totalRendered;
						if ($labelAdded) {
							$num--;
							$labelAdded = false;
						}
						$split = pager($page, $num);
						if ($split == 2) {
							$page++;
							$totalRendered = 0;
							?>
							</div>
 							
							<div style="clear: both"></div>
							<div class="footer">
								<img src="image/footer.png" width="100%" alt=""/>
								<strong><div class="boxer">
								<div class="box-row task cell">
									<div class="box">
									  <p><b>1.</b> A sticker near a task means it’s a mission needed to earn your medals. </p>
									</div>
									<div class="box"><p><b>2.</b> Daily missions are completed when the task is done five out of seven times a week.</p> 
							</div>
									<div class="box"><p><b>3.</b> A <img src="image/31204.png" width="60" height="17" alt=""/> icon near a task means you earn a charge card for completing the mission.</p> 
							</div>
									<div class="box"><p><b>4.</b> A “quota” means your assigned goal, decided upon with your commanders.</p>
							</div>
									<div class="box"><p><b>5.</b> If circumstances prevent you from completing a task, speak to your Base Commander.</p></div>
								</div>
								
							</div></strong>
							</div><!-- .footer -->
							
							</div>
							<div style="clear: both"></div>
							<div style="page-break-after: always"></div>
							<hr class="noPrint" />
							
							<div class="wrapper">
							<div class='left-sidebar'>
							<?
						}
						else if ($split == 1) {
							$firstRow = true;
							?>
							</div><!-- .left-sidebar -->
							<div class="right-sidebar">
							<?
						}
					}
				}
			}
		}
	}
}

$numWeekly = count($user->weekly_labels);
if ($numWeekly) {
	foreach ($user->sorted_weekly_labels as $label_name1) {
		for ($lno = 0; $lno < $numWeekly; $lno++) {
			if ($label_name1 == $user->weekly_labels[$lno]) {
				$totalRendered++;
				$labelAdded = true;
				?>
		 		<div class="label"><?=$label_name1?></div>
		 		<?
		 		$numTasks = count($user->weekly_tasks);
		 		for ($wtno = 0; $wtno < $numTasks; $wtno++) {
		 			$label_name = $user->weekly_tasks[$wtno]->label_name;
					if ($label_name == $user->weekly_labels[$lno]) {
						$weekly_task = $user->weekly_tasks[$wtno];
						?>
						<div class="row">
							<div class="checkbox"></div>
							<div class="rowImg">
								<img src="image/<?=$subjectIcons[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
							</div>
							<div class="short"><?=($weekly_task->short_name == '' ? '<br />' : $weekly_task->short_name)?>
							<? if ($weekly_task->focus_task) { ?>
								<img src="image/31204.png" width="60" height="17" alt="" class="focus" />
							<? } ?>
							</div>
							<div class="task"><?=$weekly_task->task_name?></div>
						</div>
						<div style='clear:both'></div>
					    <?
					    $totalRendered++;
						//echo $totalRendered;
						$num = $totalRendered;
						if ($labelAdded) {
							$num--;
							$labelAdded = false;
						}
						$split = pager($page, $num); 
						if ($split == 2) {
							$page++;
							$totalRendered = 0;
							?>
							</div>
						</div>
						
						<div style="clear: both"></div>
						<div style="page-break-after: always"></div>
						<hr class="noPrint" />
						
						<div class="wrapper">
						<div class='left-sidebar'>
						<?
						}
						else if ($split == 1) {
							?>
							</div><!-- .left-sidebar -->
							<div class="right-sidebar">
							<?
						}
					}
		 		}
			}
		}
	}
}

$numYD = count($user->no_label_subjects);
if ($numYD) {
	for ($nlno = 0; $nlno < $numYD; $nlno++) {
		$key1 = $user->no_label_subjects[$nlno];
        $info = explode(":", $key1); 
        $subject_name = $info[0]; 
        $mission_name = $info[1];
		$totalRendered++;
		$labelAdded = true;
		?>
 		<div class="label"><?=$subject_name;?> - <?=$mission_name;?></div>
 		<?
		$numTasks = count($user->no_label_tasks);
		for ($nltno = 0; $nltno < $numTasks; $nltno++) {
			$no_label_task = $user->no_label_tasks[$nltno];         
            $subject_name = $no_label_task->subject_name;
            $mission_name = $no_label_task->mission_name;
            $key2 = $subject_name . ":" . $mission_name;
			if ($key1 == $key2) {				
				?>
				<div class="row">
					<div class="checkbox"></div>
					<div class="rowImg">
						<img src="image/<?=$subjectIcons[$no_label_task->subject_id]?>" width="50" height="52" alt=""/>
					</div>
					<div class="short"><?=($no_label_task->short_name == '' ? '<br />' : $no_label_task->short_name)?>
					<? if ($no_label_task->focus_task) { ?>
						<img src="image/31204.png" width="60" height="17" alt="" class="focus" />
					<? } ?>
					</div>
					<div class="task"><?=$no_label_task->task_name?></div>
				</div>
				<div style='clear:both'></div>
			    <br />
				<?
				$totalRendered++;
				//echo $totalRendered;
				$num = $totalRendered;
				if ($labelAdded) {
					$num--;
					$labelAdded = false;
				}
				$split = pager($page, $num); 
				if ($split == 2) {
					$page++;
					$totalRendered = 0;
					?>
					</div>
				</div>
				
				<div style="clear: both"></div>
				<div style="page-break-after: always"></div>
				<hr class="noPrint" />			
				
				<div class="wrapper">
				<div class='left-sidebar'>
				<?
				}
				else if ($split == 1) {
					?>
					</div><!-- .left-sidebar -->
					<div class="right-sidebar">
					<?
				}
			}
 		}
	}
}

$numShabbos = count($user->shabbos_labels);
if ($numShabbos) {
	foreach ($user->sorted_shabbos_labels as $label_name1) {
		for ($lno = 0; $lno < $numShabbos; $lno++) {
			if ($label_name1 == $user->shabbos_labels[$lno]) {
				$totalRendered++;
				$labelAdded = true;
				?>
		 		<div class="label"><?=$label_name1?></div>
		 		<?
		 		$numTasks = count($user->shabbos_tasks);
		 		for ($stno = 0; $stno < $numTasks; $stno++) {
		 			$label_name = $user->shabbos_tasks[$stno]->label_name;
					if ($label_name == $user->shabbos_labels[$lno]) {
						$shabbos_task = $user->shabbos_tasks[$stno];
						?>
						<div class="row">
							<div class="checkbox"></div>
							<div class="rowImg">
								<img src="image/<?=$subjectIcons[$shabbos_task->subject_id]?>" width="50" height="52" alt=""/>
							</div>
							<div class="short"><?=($shabbos_task->short_name == '' ? '<br />' : $shabbos_task->short_name)?>
							<? if ($shabbos_task->focus_task) { ?>
								<img src="image/31204.png" width="60" height="17" alt="" class="focus" />
							<? } ?>
							</div>
							<div class="task">
								<?
								if ($label_name == 'Shabbos Mevorchim' and $shabbos_task->mandatory_qty > 0) 
                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc kapitelach.</i><br />";
                                if ($label_name == 'Shabbos Mevorchim' and $shabbos_task->mandatory_qty == 0 and $shabbos_task->quantity > 0)
                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc minutes.</i><br/>";
								?>
								<?=$shabbos_task->task_name?>
							</div>
						</div>
						<div style='clear:both'></div>
					    <br />
						<?
						$totalRendered++;
						//echo $totalRendered;
						$num = $totalRendered;
						if ($labelAdded) {
							$num--;
							$labelAdded = false;
						}
						$split = pager($page, $num); 
						if ($split == 2) {
							$page++;
							$totalRendered = 0;
							?>
							</div>
						</div>
						
						<div style="clear: both"></div>
						<div style="page-break-after: always"></div>
						<hr class="noPrint" />
						
						<div class="wrapper">
						<div class='left-sidebar'>
						<?
						}
						else if ($split == 1) {
							?>
							</div><!-- .left-sidebar -->
							<div class="right-sidebar">
							<?
						}						
					}
		 		}
			}
		}
	}
}
?>		 
	</div><!-- .right-sidebar -->
	<div style="clear: both"></div>
	<div id="footer" align="center">
		<table class="footerInfo">
			<tr>
				<td></td>
				<td width="65%" class="i review">
					&#10004; I reviewed my child's progress as a chayol in Hashem's army.
				</td>
			</tr>
			<tr>
				<td align="left" class="border"><span class="sb"><?=$user->first . ' ' . $user->last?></span></td>
				<td width="65%" class="signature"></td>
			</tr>
			<tr>
				<td align="left"><span class="i"><?=$user->getRankInfo()?></span></td>
				<td><span class="i">Parent's Signature</span></td>
			</tr>
		</table>
		<table>
			<tr>
				<td width="250"></td>
				<td><img class="rank" src="../file_view.php?id=<?=$user->rank_image_id;?>" height="70" /></td>
			</tr>
		</table>
	</div>

</div><!-- .wrapper -->

</body>
</html>