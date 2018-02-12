<? 
$days_of_week = array("F", "ש", "S", "M", "T", "W", "T");
		
$campaignLogos = array(
	1	=>	'Tehillim.gif',
	4	=>	'Tefilla.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'hiskashrus.gif',
	21	=>	'sefer-hamitzvos.gif',
	27	=>	'tanya.gif',
	40	=>	'Yom-Dipagra.gif',
	41	=>	'Father-Son.gif',
	42	=>	'Footsteps.gif',
	45	=>	'Cheshbon-Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Brias-Haguf.gif'
);

$stickerOutlines = array(
	1	=>	'Shabbos Mevorchim Tehillim.gif', 
	4	=>	'Tefillah.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'Sticker - Hiskashrus outline.png', 
	21	=>	'sefer hamitzvos bw.png',
	27	=>	'Tanya.gif',
	40	=>	'Yomei Dipagra.gif',
	41	=>	'Avos Ubonim.gif',
	42	=>	'Vihalachta Bidrachov.gif',
	45	=>	'Cheshbon Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Sticker - Brias Haguf_outline bw.png'
);

$dailyStickers = array(
	1	=>	'tehillim 5 of 7.png', 
	4	=>	'tefilah 5 of 7.png',
	12	=>	'mivtzoyim 5 of 7.png',
	13	=>	'niggunim 5 of 7.png',
	16	=>	'hiskashrus 5 of 7.png', 
	21	=>	'sefer hamitzvos 5 of 7.png',
	27	=>	'tanya 5 of 7.png',
	40	=>	'yoma dipagra 5 of 7.png',
	41	=>	'avos ubanim 5 of 7.png',
	42	=>	'halachta bdrachav5 of 7.png',
	45	=>	'cheshbon hanefesh 5 of 7.png',
	90	=>	'chitas 5 of 7.png',
	100	=>	'brias haguf 5 of 7.png'
);
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
    	<? include 'inc/head.php' ?>
        <title></title>
    </head>
		
    <body class="page-missions">
		<?  
			require_once '../db.php';
			$user_id = mysql_real_escape_string( $_GET['id'] );
			
			$sql = "select user_photo_id from users where user_id = " . $user_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$photo = $row['user_photo_id'];
			
			//find out start and end dates of mission
			if (!isset($_GET['d'])) {
				//get todays day
				$jd = unixtojd();
				$today = intval(date('w')); //sunday starts 0
				switch ($today) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$diff = $today + 2;
						break;
					case 5:
						$diff = 0;
						break;
					case 6:
						$diff = 1;
						break;
				}
				$start = $jd - $diff;
				$end = $start + 6;
			} else {
				$jd = intval($_GET['d']);
				$today = intval(date('w', jdtounix($jd+1)));
				if (isset($_GET['s']) && $_GET['s'] == 1) {
            		$start = $jd;
					$end = $start + 6;
            	} else {
            		$end = $jd;
					$start = $end - 6;
            	}
				//echo $start . "-" . $end; exit;
			}
			
			//echo $start . "-" . $end; exit;
			$temp = $start;
			do {
				$he = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($temp, true, CAL_JEWISH_ADD_GERESHAYIM));
				$heArr = explode(' ', $he);
				$heDates[] = $heArr[0] . ' ' . $heArr[1];
				$heDatesDisp[] = $heArr[0];
			} while (++$temp <= $end);
						
			include("../classes/user.php");
			include("../classes/user_track.php");
			include("../classes/school_class.php");
			include("../class.taskExceptions.php");
			include("../classes/date_tasks_mission.php");
			include("../classes/daily_task.php");
			include("../classes/weekly_task.php");
			include("../classes/shabbos_task.php");
			include("../classes/no_label_task.php");
			include("../classes/task.php");
			include("../classes/date_tasks_mark.php");
			
			$sql = "SELECT * FROM users WHERE user_id = " . $user_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$user = new user($row);
			$user->get_rank();
		    $user->get_school_class();
			chdir('../');
		    $user->get_user_tracks( -1, $start, $end, array(), $user->lang_id );
			chdir('mobile');
			
			$sql = "select name from parshos where start = " . $start . " and end = " . $end;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$hWeek = $row['name'];
			echo "<pre>"; print_r($user); echo "</pre>"; exit;
        ?>
        
        <header class="navbar" id="top" role="banner">
            <div class="container">
                <div class="navbar-header">
                    <div class="slick-slider">
                    	<?
						for ($from = 1; $from < 8; $from++) {
							//get hebrew date
							$timestamp = jdtounix($start + $from);							
                            $jdate = substr(jdtojewish(($start + ($from-1)), true, CAL_JEWISH_ADD_GERESHAYIM),0,-7);
                            $jdate = iconv ('windows-1255', 'utf-8', $jdate);
							?>
                    		<div class="item" data-date="<?=date('y-m-d', $timestamp)?>">
                                <div class="parsha"><span class="hebrew"><?=$hWeek?></span></div>
                                <div class="day">
                                	<?
                                	$d = date('l',$timestamp);
									if (date('w', $timestamp) == 6) $d = "shabbos";
									echo $d;
                                	?>
                                </div>
                                <div class="date"><?=date('F j',$timestamp)?> / <span class="hebrew"><?=$jdate?></span></div>
                            </div>
                			<? 
							if (unixtojd($timestamp) >= unixtojd()) {
								break;
							}
						} 
						?>
                    </div>
                </div>
            </div>
        </header>
               
        <div class="personalImg">
        	<? if (!empty($photo)) : ?>
        	<img id="userImg" src="../../../file_view.php?id=<?=$photo?>">
        	<? endif; ?>
        </div>
        
        <div class="container">
            <div class="content">
            	
            	<div class="text-right" style="margin-bottom: 20px;">
					<input type="button" id="expandAll" class="btn btn-danger btn-sm" value="Expand All" style="background-color: #5e1c77;border-color:#834999;" /> 
					<!--<input type="button" id="print" class="btn btn-danger btn-sm" value="Print" style="background-color: #5e1c77;border-color:#834999;" />-->
				</div>

				<?
				for ($from = 1; $from < 8; $from++) {
					$timestamp = jdtounix($start + $from);
					?>
                    <div class="tasks-day" data-date="<?=date('y-m-d', $timestamp)?>">
                	<?
					if (count($user->daily_labels) > 0) {
						$i = 1;
						foreach ($user->sorted_daily_labels as $value) {
							$info = explode(":", $value); 
							$label = $info[0]; 
							?>
		  
							<div class="panel panel-default">
								<div class="panel-heading">
									<i class="glyphicon glyphicon-chevron-right"></i><?=$label?>
                                </div>
								<div class="collapse">
									<div class="panel-body">
										<div class="text-left" style="float: left;">
											<input type="button" class="showProgress btn btn-danger btn-xs" value="Show Progress" style="background-color: #5e1c77;border-color:#834999;" />
										</div>
                                        <div class="text-right">
                                            <input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999;"/>
                                        </div>
                                        <br />
										<ul class="list-unstyled">
											<?
											$numDaily = count($user->daily_tasks);
											for ($j = 0; $j < $numDaily; $j++) {
												if ($user->daily_tasks[$j]->label_name == $label) {
													$daily_task = $user->daily_tasks[$j];
													$date_task_mark = $daily_task->date_task_marks[$jd-$start]; 
													if ($date_task_mark->marked == true) 
														$checked = true; 
													else 
														$checked = false;
													?>
														<li class="task">
															<div class="row">
																<div class="rowImg"> 
																	<img src="../mission_report/campaignLogos/<?=$campaignLogos[$daily_task->subject_id]?>" width="50" height="52" alt=""/>
																</div>
																<div class="mediumPic">
																	<img src="../mission_report/color/<?=$daily_task->medium_pic?>.jpg" />
																</div>																
																<label class="checkbox">
																	<div class="actions">
																		<input type="checkbox" class="box-check daily" 
																			value="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" 
																			<? if ($checked) echo "checked" ?> />
																		<span class="circle"></span>
																		<span class="check"></span>
																		<span class="box"></span>
																	</div>
																	<? if ($daily_task->focus_task) { ?>
																		<div class="focus">
																			<img src="images/31204.png" alt="" />
																		</div>
																	<? } ?>
																	<div class="short"><?=($daily_task->short_name == '' ? '<br />' : $daily_task->short_name)?></div>
																	<?=$daily_task->task_name?>
																</label>
																
																<div class="dailyBoxes" style="padding-left: 70px;">
																	<table>
																		<tr>
																			<? 
																			foreach ($days_of_week as $index => $day) {
																				$until = $jd-$start;
																				$task_mark = $daily_task->date_task_marks[$index]; 
																				if ($task_mark->marked == true) 
																					$done = true; 
																				else 
																					$done = false; 
																				?>
																			  	<td>
																					<div class="checkboxDaily">
																 						<div style="line-height: 1; color: grey; padding-top: 3px;">
																					 		<?
																					 		echo "<span class='dMark'>";
																							if ($index <= $until) {
																						 		if ($done) echo "<span style='color: green'>&#x2713;</span>";
																								else echo "<span style='color: red'>&#x2717;</span>";
																							} else {
																								if ($user->lang_id == 1)
																									echo $days_of_week[$index];
																								else if ($user->lang_id == 2) 
																									echo $heDatesDisp[$index];
																							}
																							echo "</span>";
																					 		?>
																					 	</div>
																				 	</div>
																				</td>
																			<? } ?>
																			<?
																		    if ($daily_task->mandatory_qty) {
																		    	echo "<td class='mandatoryImg'><img src=\"../mission_report/5of7stickers/" . $dailyStickers[$daily_task->subject_id] . "\" /></td>";
																		    }
																		    ?>
																		</tr>
																	</table>
															 	</div>
															</div>
														</li>
													<?
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
							<?
							$i++;
						}
					}        
	                ?>
	                
	                <?
					if (count($user->weekly_labels) > 0) {
						$i = 1;
						foreach ($user->sorted_weekly_labels as $value) {
							$info = explode(":", $value); 
							$label = $info[0]; 
							?>
		  
							<div class="panel panel-default">
								<div class="panel-heading"><i class="glyphicon glyphicon-chevron-right"></i> <?=$label?>
                                </div>
								<div class="collapse">
									<div class="panel-body">
                                        <div class="text-right">
                                            <input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999;" />
                                        </div>
                                        <br />
										<ul class="list-unstyled">
											<?
											$numWeekly = count($user->weekly_tasks);
											for ($j = 0; $j < $numWeekly; $j++) {
												if ($user->weekly_tasks[$j]->label_name == $label) {
													$weekly_task = $user->weekly_tasks[$j];
													$date_task_mark = $weekly_task->date_task_mark; 
													if ($date_task_mark->marked == true) 
														$checked = true; 
													else 
														$checked = false;
													?>
														<li class="task">
															<div class="row">
																<div class="rowImg"> 
																	<img src="../mission_report/campaignLogos/<?=$campaignLogos[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
																</div>
																<label class="checkbox">
																	<div class="actions">
																		<? if ($weekly_task->quantity > 1) : ?>
																		<input type="text" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$weekly_task->mark_date;?>" 
																			size="2" maxlength="3" style="float: right; font-size: 12px;" 
																			<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
																		<? else : ?>
																		<input type="checkbox" class="box-check weekly" id="<?=$weekly_task->date_task_id?>" 
																			value="<?=$date_task_mark->date_task_id;?>:<?=$weekly_task->mark_date;?>"
																			<? if ($checked) echo "checked" ?> />
																		<span class="circle"></span>
																		<span class="check"></span>
																		<span class="box"></span>
																		<? endif; ?>
																	</div>
																	<div class="short"><?=($weekly_task->short_name == '' ? '<br />' : $weekly_task->short_name)?></div>
																	<?=$weekly_task->task_name?>
																</label>
															</div>
														</li>
													<?
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
							<?
							$i++;
						}
					}        
					?>
					
					<?
					if (count($user->shabbos_labels) > 0) {
						$i = 1;
						foreach ($user->sorted_shabbos_labels as $value) {
							$info = explode(":", $value); 
							$label = $info[0]; 
							?>
		  
							<div class="panel panel-default">
								<div class="panel-heading"><i class="glyphicon glyphicon-chevron-right"></i> <?=$label?>
                                </div>
								<div class="collapse">
									<div class="panel-body">
										<? if (strtolower($label) == 'shabbos mevorchim') : ?>
										<? else : ?>
                                        <div class="text-right">
                                            <input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999;" />
                                        </div>
                                        <br />
                                        <? endif; ?>
										<ul class="list-unstyled">
											<?
											$numShabbos = count($user->shabbos_tasks);
											for ($j = 0; $j < $numShabbos; $j++) {
												if ($user->shabbos_tasks[$j]->label_name == $label) {
													$shabbos_task = $user->shabbos_tasks[$j];
													$date_task_mark = $shabbos_task->date_task_mark; 
													if ($date_task_mark->marked == true) 
														$checked = true; 
													else 
														$checked = false;
													?>
														<li class="task">
															<div class="row">
																<div class="rowImg"> 
																	<img src="../mission_report/campaignLogos/<?=$campaignLogos[$shabbos_task->subject_id]?>" width="50" height="52" alt=""/>
																</div>
																<label class="checkbox">
																	<div class="actions">
																		<? if ($shabbos_task->quantity > 1) : ?>
																		<input type="text" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$shabbos_task->mark_date;?>" 
																			size="2" maxlength="3" style="float: right; font-size: 12px;" 
																			<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
																		<? else : ?>
																		<input type="checkbox" class="box-check weekly" id="<?=$shabbos_task->date_task_id?>" 
																			value="<?=$date_task_mark->date_task_id;?>:<?=$shabbos_task->mark_date;?>"
																			<? if ($checked) echo "checked" ?> />
																		<span class="circle"></span>
																		<span class="check"></span>
																		<span class="box"></span>
																		<? endif; ?>
																	</div>
																	<div class="short"><?=($shabbos_task->short_name == '' ? '<br />' : $shabbos_task->short_name)?></div>
																	<?=$shabbos_task->task_name?>
																</label>
															</div>
														</li>
														
													<?
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
							<?
							$i++;
						}
					}        
					?>
					
					<?
					if (count($user->no_label_subjects) > 0) {
						$i = 1;
						foreach ($user->no_label_subjects as $value) {
							$info = explode(":", $value); 
							$label = $info[1]; 
							?>
		  
							<div class="panel panel-default">
								<div class="panel-heading"><i class="glyphicon glyphicon-chevron-right"></i> <?=$label?>
                                </div>
								<div class="collapse">
									<div class="panel-body">
                                        <div class="text-right">
                                            <input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999;"/>
                                        </div>
                                        <br />
										<ul class="list-unstyled">
											<?
											$numTasks = count($user->no_label_tasks);
											for ($j = 0; $j < $numTasks; $j++) {
												if ($user->no_label_tasks[$j]->mission_name == $label) {
													$no_label_task = $user->no_label_tasks[$j];
													$date_task_mark = $no_label_task->date_task_mark; 
													if ($date_task_mark->marked == true) 
														$checked = true; 
													else 
														$checked = false;
													?>
														<li class="task">
															<div class="row">
																<div class="rowImg"> 
																	<img src="../mission_report/campaignLogos/<?=$campaignLogos[$no_label_task->subject_id]?>" width="50" height="52" alt=""/>
																</div>
																<label class="checkbox">
																	<div class="actions">
																		<? if ($no_label_task->quantity > 1) : ?>
																		<input type="text" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$no_label_task->mark_date;?>" 
																			size="2" maxlength="3" style="float: right; font-size: 12px;" 
																			<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
																		<? else : ?>
																		<input type="checkbox" class="box-check weekly" id="<?=$no_label_task->date_task_id?>" 
																			value="<?=$date_task_mark->date_task_id;?>:<?=$no_label_task->mark_date;?>"
																			<? if ($checked) echo "checked" ?> />
																		<span class="circle"></span>
																		<span class="check"></span>
																		<span class="box"></span>
																		<? endif; ?>
																	</div>
																	<div class="short"><?=($no_label_task->short_name == '' ? '<br />' : $no_label_task->short_name)?></div>
																	<?=$no_label_task->task_name?>
																</label>
															</div>
														</li>
													<?
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
							<?
							$i++;
						}
					}        
					?>
					
                    </div>
				<?
					if (unixtojd($timestamp) == unixtojd()) {
						break;
					}
				}        
                ?>

            </div>
        </div>

    	<? include 'inc/footer.php' ?>

    	<? include 'inc/foot.php' ?>
		
		<script type="text/javascript" src="reg/js/js.cookie.js"></script>
        <script>
			var currentSlide = <?=$jd-$start?>;
			var bSlid = localStorage.getItem('achos-missions-slid');
		
        	$( function() {
        		//$(".checkAll").hide();
        		var url = location.toString();
				var pos = url.indexOf('='); 
				var id = url.substring( pos+1 );
				
				var d = id.indexOf('&');
				if (d > 0) {
					id = id.substring( 0, d );
				}
				
        		$.post('reg/ajax/checkAuth.php', { user_id : id, admin_id : Cookies.get('admin') }, function( success ) {
					if (success == 0) {
						window.location = "/mobile";
					}
				});
				
        		$("#missionsLink").attr('href', '/mobile/missions.php?id=' + id);
				$("#goalsLink").attr('href', '/mobile/goals.php?id=' + id);
				$("#rankLink").attr('href', '/mobile/reg/rank.html?id=' + id);
	
        		$(".dailyBoxes").hide();
        		
        		$("#expandAll").click( function() {
        			$(this).parent().parent().parent().find('.panel-heading').trigger('click');
        		});
        		
        		$(".checkAll").click( function() {
        			var inputs = $(this).parent().parent().find('.box-check');
        			$.each(inputs, function() {
        				var input = $(this);
        				if (!input.is(":checked")) {
        					input.trigger('click');
        				}
        			});
        		});
        		
        		$(".box-check").click( function() {
        			var daily = false;
        			var weekly = false;
        			
        			var function_name, url;
        			
        			var className = $(this).attr('class');
        			if (className.indexOf('daily') > 0) {
        				daily = true;
        			} else if (className.indexOf('weekly') > 0) {
        				weekly = true;
        			}
        			
        			var checked = $(this).is(":checked");
        			if (checked) {
        				if (daily) {
        					function_name = "add_daily_task_mark";
        				} else if (weekly) {
        					function_name = "add_task_mark";
        					//need to make sure all weekly checkboxes in other slides are updated as well
        					var id = $(this).attr('id');
        					$(".tasks-day").find("#" + id).attr('checked', true);
                    	}
                    	url = "../add_functions.php";
        			} else {
        				if (daily) {
        					function_name = "delete_daily_task_mark";
        				} else if (weekly) {
        					function_name = "delete_task_mark";
        					//need to make sure all weekly checkboxes in other slides are updated as well
        					var id = $(this).attr('id');
        					$(".tasks-day").find("#" + id).attr('checked', false);
                    	}
                    	url = "../delete_functions.php";
        			}
        			
        			var user_id = <?=$user_id?>;
        			var value = $(this).val();
        			var pos = value.indexOf(':');
        			var date_task_id = value.substring(0,pos);
        			var mark_date = value.substring(++pos, value.length);
        			        			
        			var parameters = [user_id, date_task_id, mark_date];
                    url += "?function_name=" + function_name + "&parameters=" + parameters;

                    $.getJSON(url, function(success) {  
                        if (success == false) {
                            alert("Update not performed.");
                        }
                    });
        		});
        		
        		$(".textInput").blur( function() {
					var info = $(this).attr('id');
					var split = info.indexOf(':');
					var task = info.substring(0,split);
					var date = info.substring(++split);
					var user_id = <?=$user_id?>;
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
							if (success == false) {
								alert("Update not performed.");
							}
						});
					//}
				});
				
				if (!bSlid) {
					$('.navbar-header').addClass('hint');
					setTimeout(function(){
						$('.navbar-header').removeClass('hint')
					},2500);
				}
				
				$('.slick-slider').slick({
					arrows: false,
					infinite: false,
					responsive: [{
					breakpoint: 2000,
					settings: {
					slidesToShow: 1,
					centerMode: false,
					slidesToScroll: 1
					}}],
					mobileFirst: true,
					initialSlide: <?=$jd-$start?>,
					onInit: function() {
						var date = $('.slick-slider').find('.item').eq(currentSlide).attr('data-date');
						$('.content .tasks-day').fadeOut('fast').filter('[data-date='+date+']').fadeIn('fast');
					},
					onAfterChange: function (d) {
						if (!bSlid) {
							localStorage.setItem('achos-missions-slid',1);
						}
						if (d.currentSlide!=currentSlide) {
							currentSlide = d.currentSlide;
							var date = $('.slick-slider').find('.item').eq(currentSlide).attr('data-date');
							$('.content .tasks-day').fadeOut('fast').filter('[data-date=' + date + ']').fadeIn('fast');
						} else { 
							if (currentSlide == 0 && d.currentSlide == 0) {
								//only allow going back up to two weeks
								var today = <?=unixtojd()?>;
								var start = <?=$start?>;
								if ((today - start) < 21) {
									//reload page with mission dates of the previous week
									var url = "missions.php?id=" + id + "&d=" + <?=--$start?>;
									window.location.href = url;
								}						
							} else if (currentSlide == 6 && d.currentSlide == 6) {
								//reload page with mission dates of the next week					
								var url = "missions.php?id=" + id + "&d=" + <?=++$end?> + "&s=1";
								window.location.href = url;
							}
						}
					}
				});
				
				$(".showProgress").click( function() {
					$(this).parent().parent().find('.dailyBoxes').toggle();
					//$(this).parent().parent().find('.dInfo').toggle();
					//$(this).parent().parent().find('.dMark').toggle();
				});
        	});
        	
        	$("#print").click( function() {
        		var admin = Cookies.get('admin_id');
        		window.location = "/mission_report/newParentPrint.php?bypass=1&admin=" + admin;
        	});
        	
        	$(window).load(function(){        		
	            $(".slider img").click(function(){
	                var item_id = $(this).attr("id");
	                $(".slider img").each(function(){
	                    $(this).removeClass("active");
	                });
	                $("#item"+item_id).addClass("active");
	                item_id = item_id.replace("item","");
	                if(item_id == "0"){
	                    carousel.select(0, parseInt(item_id)+1);
	                }else{
	                    carousel.select(parseInt(item_id), parseInt(item_id)+1);
	                }
	                console.log(parseInt(item_id)+1);
	            });
        	});
        </script>

    </body>
</html>		
