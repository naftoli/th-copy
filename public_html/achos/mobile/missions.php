<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
	header("Location: index.php");
	exit;
}
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
			$user_id = $_SESSION['user_id'];
			
			//find out start and end dates of mission
			if (!isset($_GET['d'])) {
				//get todays day
				$jd = floor(unixtojd());
				$today = intval(date('w', jdtounix($jd))); //sunday starts 0
				//echo $today; exit;
				$start = $jd - $today;
				$end = $start + 6;
				
				// check if we need to change start / end
				// there seems to be some type of discrepancy between the unixtojd and date('w') return values
				$sql = "select * from parshos where start = " . $start;
				$result = mysql_query($sql);
				if (mysql_num_rows($result) == 0) {
					$start++;
					$end++;
					$today--;
				}
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
			}
						
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
			
			$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$user = new user($row);
			$user->get_user_tracks(-1, $start, $end); 
			//echo "<pre>"; print_r($user); echo "</pre>"; exit;
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
							$hWeek = $user->user_tracks[0]->date_tasks_missions[0]->mission_name;
						?>
                    		<div class="item" data-date="<?=date('y-m-d', $timestamp)?>">
                                <div class="parsha"><span class="hebrew"><?=$hWeek?></span></div>
                                <div class="day">
									<?
									$d = date('l',$timestamp-1);
									//if (date('w', $timestamp) == 6) $d = "shabbos";
									if ($d == 'Saturday') $d = "Shabbos";
									echo $d;
									?>
								</div>
                                <div class="date"><?=date('F j',$timestamp-1)?> / <span class="hebrew"><?=$jdate?></span></div>
                            </div>
                		<? 
							if (unixtojd($timestamp) >= floor(unixtojd())) {
								break;
							}
						} 
						?>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="container">
            <div class="content">

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
										<!--
                                        <div class="text-right">
                                            <input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" />
                                        </div>
										-->
										<ul class="list-unstyled">
											<br />
											<?
											$numDaily = count($user->daily_tasks);
											for ($j = 0; $j < $numDaily; $j++) {
												if ($user->daily_tasks[$j]->label_name == $label) {
													$daily_task = $user->daily_tasks[$j];
													//if (!$daily_task->master_task_id) {
													?>
															<!--<li class="task-header"><h4><?=$daily_task->task_name?></h4></li>-->
													<? 
													//} else {
														$date_task_mark = $daily_task->date_task_marks[date('w', $timestamp)]; 
														if ($date_task_mark->marked == true) 
															$checked = true; 
														else 
															$checked = false;
														?>
															<li class="task">
																<label class="checkbox">
																	<div class="actions">
																		<?php if ($daily_task->quantity) : ?>
																		<input type="text" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" 
																			size="2" maxlength="3" style="float: right;	font-size: 12px;" 
																			<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
																		<?php else : ?>
																		<input type="checkbox" class="box-check daily" 
																			value="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" 
																			<? if ($checked) echo "checked" ?> />
																		<span class="circle"></span>
																		<span class="check"></span>
																		<span class="box"></span>
																		<?php endif; ?>
																	</div>
																	<?
																	$str = $daily_task->task_name;
																	if (!empty($daily_task->description)) 
																		$str .= " - <span class='desc'>" . $daily_task->description . "</span>";
																	echo "<div>" . $str . "</div>";
																	?>
																</label>
															</li>
															
													<?
													//} 
												}
											}
											?>
										</ul>
										<?php if ($daily_task->label_id == 17) : ?>
										<br />
										<div class="pull-right">
											<button class='btn btn-danger btn-sm save' style='background-color : #f47a6d; border-color: #ffe9ca;'>Save</button>
										</div>
										<?php endif; ?>
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
                                            <input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" />
                                        </div>
										<ul class="list-unstyled">
											<br />
											<?
											$numWeekly = count($user->weekly_tasks);
											for ($j = 0; $j < $numWeekly; $j++) {
												if ($user->weekly_tasks[$j]->label_name == $label) {
													$weekly_task = $user->weekly_tasks[$j];
													//if (!$weekly_task->master_task_id) {
													?>
															<!--<li class="task-header"><h4><?=$weekly_task->task_name?></h4></li>-->
													<? 
													//} else {
														$date_task_mark = $weekly_task->date_task_mark; 
														if ($date_task_mark->marked == true) 
															$checked = true; 
														else 
															$checked = false;
														?>
															<li class="task">
																<label class="checkbox">
																	<div class="actions">
																		<input type="checkbox" class="box-check weekly" id="<?=$weekly_task->date_task_id?>" 
																			value="<?=$date_task_mark->date_task_id;?>:<?=$weekly_task->mark_date;?>"
																			<? if ($checked) echo "checked" ?> />
																		<span class="circle"></span>
																		<span class="check"></span>
																		<span class="box"></span>
																	</div>
																	<?
																	$str = $weekly_task->task_name;
																	if (!empty($weekly_task->description)) 
																		$str .= " - <span class='desc'>" . $weekly_task->description . "</span>";
																	echo "<div>" . $str . "</div>";
																	?>
																</label>
															</li>
															
													<?
													//} 
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
					if (floor(unixtojd($timestamp)) == floor(unixtojd())) {
						break;
					}
				}        
                ?>

            </div>
        </div>

    	<? include 'inc/footer.php' ?>

    	<? include 'inc/foot.php' ?>

        <script>
			var currentSlide = <?=$today?>;
			var bSlid = localStorage.getItem('achos-missions-slid');
		
        	$( function() {
        		//$(".checkAll").hide();
				$(".collapse").eq(currentSlide).collapse('toggle');
        		
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
				
				//$(".textInput").blur( function() {
				$(".save").click( function() {
					var elem = $(this).parent().parent().find('.textInput');
					var info = $(elem).attr('id');
					var split = info.indexOf(':');
					var task = info.substring(0,split);
					var date = info.substring(++split);
					var user_id = <?=$user_id?>;
					var url = '';
					//var div = this;
					
					//var val = $(this).val();
					var val = $(elem).val();
					if (val == '') {
						val = 0;
					}
					//if (val > 0) {
						var parameters = [user_id, task, date, val];
						url = "../add_functions.php?function_name=add_mark&parameters=" + parameters;
						$.getJSON(url, function(success) {
							if (success == false) {
								alert("Update not performed.");
							} else {
								location.href = 'home.php';
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
					initialSlide: <?=$today?>,
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
								//reload page with mission dates of the previous week						
								var url = "missions.php?d=" + <?=--$start?>;
								window.location.href = url;							
							} else if (currentSlide == 6 && d.currentSlide == 6) {
								//reload page with mission dates of the next week					
								var url = "missions.php?d=" + <?=++$end?> + "&s=1";
								window.location.href = url;
							}
						}
					}
				});
        	});
        </script>

    </body>
</html>		
