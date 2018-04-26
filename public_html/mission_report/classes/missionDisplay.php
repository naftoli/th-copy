<?
abstract class MissionDisplay {
	protected $mission;
	protected $settings;
	protected $days_of_week;
	protected $campaignLogos;
	protected $stickerOutlines;
	protected $dailyStickers;
	protected $dateDisplay;
	protected $dblSided;
	protected $missionType;
	protected $heDates;
	protected $heDatesDisp;
	protected $parsha;
	protected $start;
	protected $end;
	public $user_id;
	public $lang_id;
	
	public function __construct( $mission ) {
		$this->mission = $mission;
		$this->user_id = $mission->user_id;
		$this->lang_id = $mission->lang_id;
		$this->dateDisplay = 1;
		$this->dblSided = 1;
		$this->missionType = 1;
		
		$this->start = $mission->user_tracks[0]->start_date;
		$this->end = $mission->user_tracks[0]->end_date;
		
		$this->heDates = array();
		$this->heDatesDisp = array();
		$temp = $this->start;
		do {
			$he = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($temp, true, CAL_JEWISH_ADD_GERESHAYIM));
			$heArr = explode(' ', $he);
			$this->heDates[] = $heArr[0] . ' ' . $heArr[1];
			$this->heDatesDisp[] = $heArr[0];
		} while (++$temp <= $this->end);
		
		$sql = "select name from parshos where start = " . $this->start . " and end = " . $this->end;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$this->parsha = $row['name'];
		} else {
			$this->parsha = '';
		}

		$this->days_of_week = array("F", "ש", "S", "M", "T", "W", "T");
		
		$this->campaignLogos = array(
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
			92	=>	'Niggunim.gif',
			93	=>	'Mivtzoim.gif',
			94	=>	'Yom-Dipagra.gif',
			100	=>	'Brias-Haguf.gif'
		);
		
		$this->stickerOutlines = array(
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
			92	=>	'Niggunim.gif',
			93	=>	'Mivtzoim.gif',
			94	=>	'Yomei Dipagra.gif',
			100	=>	'Sticker - Brias Haguf_outline bw.png'
		);
		
		$this->dailyStickers = array(
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
			92	=>	'niggunim 5 of 7.png',
			93	=>	'mivtzoyim 5 of 7.png',
			94	=>	'yoma dipagra 5 of 7.png',
			100	=>	'brias haguf 5 of 7.png'
		);
	}
	
	public function setDateDisplay( $val ) {	
		$this->dateDisplay = $val;
	}
	
	public function setDblSided( $val ) {
		$this->dblSided = $val;
	}

	public function setMissionType( $type ) {
		$this->missionType = $type;
	}
	
	public function getMissionType() {
		return $this->missionType;
	}
	
	public static function getInstance( $type, $mission ) {
		switch ($type) {
			case 1:
				$m = new NoPicMission( $mission );
				break;
			case 2:
				$m = new PicMission( $mission );
				break;
			case 3:
				$m = new LargePicMission( $mission );
				break;
		}
		$m->setMissionType( $type );
		return $m;
	}

	public function getMission() {
		return $this->mission;
	}

	public function createFooter() { 
	?>
		<div class="footer">
			<? if ($this->lang_id == 1) : ?>
				<img class="footerImg" src="image/missionInstructions.gif" width="100%" alt=""/>
			<? elseif ($this->lang_id == 2) : ?>
				<img class="footerImg" src="image/mission-instructions-yiddish2.gif" width="55%" alt=""/>
			<? endif; ?>
			<div style="direction: ltr;">
				<div class="boxer">
					<div class="task cell">
						<div class="box">
						  <p><b>1.</b> A sticker near a task means it’s a mission needed to earn your medals.</p>
						</div>
						<div class="box">
							<p><b>2.</b> Daily missions are completed when the task is done five out of seven times a week.</p> 
						</div>
						<div class="box">
							<p><b>3.</b> An <img src="image/31204.png" width="40" height="10" alt=""/> icon near a task means you earn a charge card for completing the mission.</p> 
						</div>
						<div class="box">
							<p><b>4.</b> A “quota” means your assigned goal, decided upon with your commanders.</p>
						</div>
						<div class="box"><p><b>5.</b> If circumstances prevent you from completing a task, speak to your Base Commander.</p></div>
					</div>
				</div>
			</div>
		</div><!-- .footer -->
	<? 
	}

	public function createPager( $user, $page ) {
		//return;
		?>
		<div style="clear: both"></div>
		<div class="pageFooter">
			<div class="userName">
				<?
				if ($this->lang_id == 1) {
					echo $user->first . ' ' . $user->last;
				} else if ($this->lang_id == 2) {
					if (!empty($user->first_he) || !empty($user->last_he)) {
						echo $user->first_he . ' ' . $user->last_he;
					} else {
						echo $user->first . ' ' . $user->last;
					}
				}
				echo " (" . $user->user_id . ")";
				?>
			</div>
			<div class="pageNum">
				<? if ($this->lang_id == 1) echo "Page "; ?>
				<?=$page?>
			</div>
			<div class="parsha">פרשת <?=$this->parsha?></div>
		</div>
		<?
	}

	public function printMission($debug = false) {
		chdir("../");
		$user = $this->mission;
		$numLabels = count($user->daily_labels) + count($user->weekly_labels) + count($user->shabbos_labels) + count($user->no_label_subjects);
		$tracks = $user->user_tracks;
		
		$numTasks = 0;
		$totalDaily = 0;
		$rendered = 0;
		$types = array('daily_tasks', 'weekly_tasks', 'shabbos_tasks', 'no_label_tasks');
		foreach ($tracks as $track) {
			foreach ($types as $type) {
				if ($type == 'daily_tasks') $totalDaily += count($track->$type);
				$numTasks += count($track->$type);
			}
		}
		$totalRows = (floor($numLabels / 2)) + $numTasks;
		
		if ($this->missionType == 2) {
			$taskClass = "mediumPicTask";
		} else {
			$taskClass = "task";
		}
		?> 
		
		<div class="firstContainer">
			<div class="header">
				<div class="userImg">
					<? if (isset($user->mobile_pic)) : ?>
						<img src="../mobile/reg/<?=$user->mobile_pic?>" width="60" alt=""/>
					<? elseif (isset($user->user_photo_id)) : ?>
						<img src="../file_view2.php?id=<?=$user->user_photo_id?>" width="60" alt=""/>
					<? else : ?>
						<img src="" width="60" alt=""/> 
					<? endif; ?>
				</div>
				<?
				$sql = "select school_number from schools where school_id = " . $user->school_class->school_id;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				?>
				<div class="schoolLogo">
					<img src="schools/<?=$row['school_number']?>.png" width="80" alt=""/>
				</div>
		    	<table>
		    		<tr>
		    			<td class="title">
							<span class="sb">Platoon:</span> 
							<?
							$grade = $user->school_class->class_grade . (isset($user->school_class->class_sub) ? '-' . $user->school_class->class_sub : '');
							echo $grade;
							?>
							<br />
							<span class="sb">Commander:</span><br>
							<?=$user->school_class->class_teacher;?>
		    			</td>
		    			<td>
		    				<div class="headerImg">
		    					<? if ($this->lang_id == 1) : ?>
		    						<img src="image/mission report logo.png" width="350" />
		    					<? elseif ($this->lang_id == 2) : ?>
		    						<img src="image/mission-report-yiddish.png" width="350" />
		    					<? endif; ?>
		    				</div>
		    			</td>
		    			<td class="title">
							<?=$user->rank_name?><br />
							<span class="b">
								<?
								if ($this->lang_id == 1) {
									echo $user->first . ' ' . $user->last;
								} else if ($this->lang_id == 2) {
									if (!empty($user->first_he) || !empty($user->last_he)) {
										echo $user->first_he . ' ' . $user->last_he;
									} else {
										echo $user->first . ' ' . $user->last;
									}
								}
								echo " (" . $user->user_id . ")";
								?>
							</span>
		    			</td>
		    		</tr>
		    		<tr> 
		    			<td colspan="3" class="line">
						<? 
						if ($this->dateDisplay > 0) {
							if ($this->dateDisplay == 1) { 
								?>
								<span class="hebrew-text">פרשת <?=$this->parsha?> 
									<span style='font-size: 16px;'>and following week</span> &#10022; 
									<?=$this->heDates[0]?> - <?=$this->heDates[6]?></span>
							<? } else if ($this->dateDisplay == 2) { ?>
								<b><?=date('M j', (jdtounix($this->start))) . ' - ' . date('M j, Y', (jdtounix($this->end)))?></b>
								<span class="hebrew-text"> &#10022; פרשת <?=$this->parsha?> &#10022; <?=$this->heDates[0]?> - <?=$this->heDates[6]?></span>
							<? } ?>
							</span></td>
						<? } else { ?>
							<span class="hebrew-text">פרשת <?=$this->parsha?></span>
						<? } ?>
						</td>
		    		</tr>
			  </table>
			</div>
			
			<div class="left">
				<?
				$totalRendered = 0;
				$page = 1;
				$labelAdded = 0;
				$firstColumn = true;
				
				$numDaily = count($user->daily_labels);
				if ($numDaily) {
					foreach ($user->sorted_daily_labels as $key0 => $value) {
						for ($dlno = 0; $dlno < $numDaily; $dlno++) {
							$key1 = $user->daily_labels[$dlno]; 
							$info = explode(":", $key1); 
							$label = $info[0]; 
							if ($value == $user->daily_labels[$dlno]) {
								$labelAdded++;
								$firstRow = true;
							?>
						 		<div class="label"><?=$label?></div>
								<? 
								$numTasks = count($user->daily_tasks);
								for ($dtno = 0; $dtno < $numTasks; $dtno++) {
									if ($user->daily_tasks[$dtno]->label_name == $label) {
										$daily_task = $user->daily_tasks[$dtno];
										$rendered++;
										?>
										<div class="taskRow">
							            <div class="row">
											<div class="rowImg">
												<img src="campaignLogos/<?=$this->campaignLogos[$daily_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$daily_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<? if ($daily_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											<div class="short"><?=($daily_task->short_name == '' ? '<br />' : $daily_task->short_name)?></div>
											<div class="<?=$taskClass?>"><?=$daily_task->task_name?></div>
									    </div>
									    
									    <?
									    if ($daily_task->mandatory_qty) {
									    	echo "<div class='mandatoryImg";
											if ($firstColumn) 
												echo " firstColumn";
											else 
												echo " secondColumn";
									    	echo "'><img src=\"5of7stickers/" . $this->dailyStickers[$daily_task->subject_id] . "\" /></div>";
									    }
									    ?>
										
										<?php
										// find out the marks dates to know if this task is only on specific dates
										$dates = array();
										foreach ($daily_task->date_task_marks as $mark) {
											$dates[] = $mark->mark_date;									     
										}
										?>
									    <div class="dailyBoxes" style="padding-top: 1px;">
											<table>
												<tr>
													<? foreach ($this->days_of_week as $index => $day) { ?>
													  	<td>
															<div class="checkboxDaily">
														 		<? //if ($firstRow) { ?>
																<? if (in_array(($this->start + $index), $dates)) : ?>
															 		<div style="color: grey; line-height: 0.85;">
																		<? if ($this->lang_id == 2) : ?>
																			<span style="font-size: 13px; font-weight: bold; vertical-align: text-bottom;"><?=$this->heDatesDisp[$index]?></span>
																		<? else : ?>
																			<span class="hebrew"><?=$this->heDatesDisp[$index]?><br /><?=$day?></span>
																		<? endif; ?>
																 	</div>
																<? else : ?>
																	<div style='font-size: 20px; vertical-align: bottom; color: black; line-height: 1'>X</div>
																<? endif; ?>
														 		<? //} ?>
														 	</div>
														</td>
													<? } ?>
													<? //$firstRow = false; ?>
												</tr>
											</table>
										 </div>
										</div>
										<div style="clear: both"></div>
										<?
										$totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered;
										if ($debug) {
											echo "Page: " . $page . "<br />";
											echo "Total Rendered: " . $totalRendered . "<br />";
											echo "Total Rows: " . $totalRows . "<br />";
											echo "Total Daily: " . $totalDaily . "<br />";
											echo "Add Label: " . $addLabel . "<br /><br />";
										}
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel, $totalDaily, $rendered); 
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
											</div>
											<div style="clear: both"></div>
											<hr />
											
											<div class="container">
											<div class="left">
											<?
										}
										else if ($split == 1) {
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											$firstColumn = false;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
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
								$labelAdded++;
								?>
						 		<div class="label"><?=$label_name1?></div>
						 		<?
						 		$numTasks = count($user->weekly_tasks);
						 		for ($wtno = 0; $wtno < $numTasks; $wtno++) {
						 			$label_name = $user->weekly_tasks[$wtno]->label_name;
									if ($label_name == $user->weekly_labels[$lno]) {
										$weekly_task = $user->weekly_tasks[$wtno];
										$rendered++;
										?>
										<div class="taskRow">
										<div class="row">
											<div class='mandatoryImg'>&nbsp;
											<?
										    if ($weekly_task->mandatory_qty) {
										    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$weekly_task->subject_id] . "' />";
										    } else {
										    	echo "<img src='' alt='' />";
										    }
										    ?>
										    </div>
											<div class="checkbox<?php if ($weekly_task->quantity) echo " textInput";?>"></div>
											<div class="rowImg">
												<img src="campaignLogos/<?=$this->campaignLogos[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$weekly_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<div class="short"><?=($weekly_task->short_name == '' ? '<br />' : $weekly_task->short_name)?>
											<? if ($weekly_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											</div>
											<?
											$add = '';
											if ($this->missionType == 2) {
												$add = " extraSpace";
											}
											?>
											<div class="<?=$taskClass . $add?>"><?=$weekly_task->task_name?></div>
										</div>
										</div>
										<div style="clear: both"></div>
									    <?
									    $totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered + $addLabel;
										if ($debug) {
											echo "Page: " . $page . "<br />";
											echo "Total Rendered: " . $totalRendered . "<br />";
											echo "Total Rows: " . $totalRows . "<br />";
											echo "Add Label: " . $addLabel . "<br /><br />";
										}
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel, $totalDaily, $rendered); 
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
										</div>
										<div style="clear: both"></div>
										<hr />
										
										<div class="container">
										<div class="left">
										<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
											<?
										}
									}
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
								$labelAdded++;
								?>
						 		<div class="label"><?=$label_name1?></div>
						 		<?
						 		$numTasks = count($user->shabbos_tasks);
						 		for ($stno = 0; $stno < $numTasks; $stno++) {
						 			$label_name = $user->shabbos_tasks[$stno]->label_name;
									if ($label_name == $user->shabbos_labels[$lno]) {
										$shabbos_task = $user->shabbos_tasks[$stno];
										$rendered++;
										?>
										<div class="taskRow">
										<div class="row">
											<div class='mandatoryImg'>&nbsp;
											<?
										    if ($shabbos_task->mandatory_qty) {
										    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$shabbos_task->subject_id] . "' />";
										    } else {
										    	echo "<img src='' alt='' />";
										    }
										    ?>
										    </div>
											<div class="checkbox<?php if ($shabbos_task->quantity) echo " textInput";?>"></div>
											<div class="rowImg">
												<img src="campaignLogos/<?=$this->campaignLogos[$shabbos_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$shabbos_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<div class="short"><?=($shabbos_task->short_name == '' ? '<br />' : $shabbos_task->short_name)?>
											<? if ($shabbos_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											</div>
											<div class="<?=$taskClass?>">
												<?=$shabbos_task->task_name?><br />
												<div style="font-size: 12px;">
												<?
												if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty > 0) 
				                                    echo "<i>My Quota: $shabbos_task->desc kapitelach.</i><br />";
				                                if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty == 0 && $shabbos_task->quantity > 0)
				                                    echo "<i>My Quota: $shabbos_task->desc minutes.</i><br/>";
												if ($label_name == 'שבת מברכים' && $shabbos_task->mandatory_qty > 0) 
				                                    echo "<i>מיינע קוואטע: $shabbos_task->desc קאפיטלעך.</i><br />";
				                                if ($label_name == 'שבת מברכים' && $shabbos_task->mandatory_qty == 0 && $shabbos_task->quantity > 0)
				                                    echo "<i>מיינע קוואטע: $shabbos_task->desc מינוטן.</i><br/>";
												?>
												</div>
											</div>
										</div>
										</div>
										<div style="clear: both"></div>
										<?
										$totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered;
										if ($debug) {
											echo "Page: " . $page . "<br />";
											echo "Total Rendered: " . $totalRendered . "<br />";
											echo "Total Rows: " . $totalRows . "<br />";
											echo "Add Label: " . $addLabel . "<br /><br />";
										}
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel, $totalDaily, $rendered);  
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
										</div>
										<div style="clear: both"></div>
										<hr />
										
										<div class="container">
										<div class="left">
										<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
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
						$labelAdded++;
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
								$rendered++;
								?>
								<div class="taskRow">
								<div class="row">
									<div class='mandatoryImg'>&nbsp;
									<?
								    if ($no_label_task->mandatory_qty) {
								    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$no_label_task->subject_id] . "' />";
								    } else {
								    	echo "<img src='' alt='' />";
								    }
								    ?>
								    </div>
									<div class="checkbox<?php if ($no_label_task->quantity) echo " textInput";?>"></div>
									<div class="rowImg">
										<img src="campaignLogos/<?=$this->campaignLogos[$no_label_task->subject_id]?>" width="50" height="52" alt=""/>
									</div>
									<? if ($this->missionType == 2) { ?>
										<div class="mediumPic"><img src="color/<?=$no_label_task->medium_pic?>.jpg" /></div>
									<? } ?>
									<div class="short"><?=($no_label_task->short_name == '' ? '<br />' : $no_label_task->short_name)?>
									<? if ($no_label_task->focus_task) { ?>
										<div class="focus">
											<img src="image/31204.png" alt="" />
										</div>
									<? } ?>
									</div>
									<div class="<?=$taskClass?>"><?=$no_label_task->task_name?></div>
								</div>
								</div>
								<div style="clear: both"></div>
								<?
								$totalRendered++;
								$addLabel = floor($labelAdded / 2);
								//echo $totalRendered;
								if ($debug) {
									echo "Page: " . $page . "<br />";
									echo "Total Rendered: " . $totalRendered . "<br />";
									echo "Total Rows: " . $totalRows . "<br />";
									echo "Add Label: " . $addLabel . "<br /><br />";
								}
								$split = $this->pager($page, $totalRendered, $totalRows, $addLabel, $totalDaily, $rendered);
								if ($split == 2) {
									echo "</div>";
									if ($page == 1)
										$this->createFooter();
									else {
										$this->createPager( $user, $page );
									}
									$page++;
									$totalRendered = 0;
									$labelAdded = 0;
									?>
								</div>
								<div style="clear: both"></div>
								<hr />			
								
								<div class="container">
								<div class="left">
								<?
								}
								else if ($split == 1) {
									$firstRow = true;
									$totalRendered += floor($labelAdded / 2);
									$labelAdded = 0;
									?>
									</div><!-- .left-sidebar -->
									<div class="right">
									<?
								}
							}
				 		}
					}
				}
			?>
			</div>
			<?php
			if ($debug) {
				echo "Page: " . $page . "<br />";
				echo "Total Rendered: " . $totalRendered . "<br />";
				echo "Total Rows: " . $totalRows . "<br />";
				echo "Add Label: " . $addLabel . "<br /><br />";
			}
			?>
			<div id="<?=$user->user_id?>" class="bottomFooter" 
			<?php if ($totalRendered == 0) echo "style='bottom: 0 !important;' "; ?>
			align="center" dir="ltr">
				<!--
				<div class="hakdosho">
					<? if ($this->start == 2457445) : ?>
						לעילוי נשמת נחמה בת חנא ע"ה
					<? else : ?>
						לעילוי נשמת הלאמטערנעשטיק השליח החסיד הרב אהרן אליעזר בן הרב יהושע העשל ע"ה
					<? endif; ?>
				</div>
				-->
				<input type="hidden" class="pages" value="<?=$page?>" />
				<? if ($this->lang_id == 1) : ?>
					<img class="rankLogo" src="image/rank report logo.png" />
					<img class="rank" src="../rankPics/<?=$user->rank_ord;?>BW.png" height="70" />
					<table class="footerInfo">
						<tr style="vertical-align: bottom">
							<td align="left" class="border"><span class="sb">
								<?=$user->first . ' ' . $user->last;?>
								<?=" (" . $user->user_id . ")";?>
							</span></td>
							<td width="48%" class="i review">
								&#10004; I reviewed my child's progress as a chayol in Hashem's army.
							</td>
						</tr>
						<tr>
							<td align="left"><span class="i"><?=$user->getRankInfo(true)?></span></td>
							<td><span class="i">Parent's Signature</span></td>
						</tr>
					</table> 
				<? elseif ($this->lang_id == 2) : ?>
					<img class="rankLogo" src="image/rank report yiddish.png" />
					<img class="rank" src="../rankPics/<?=$user->rank_ord;?>BW.png" height="70" />
					<table class="footerInfo">
						<tr style="vertical-align: bottom">
							<td align="right" class="border"><span class="sb">
								<?
								if (!empty($user->first_he) || !empty($user->last_he)) {
									echo $user->first_he . ' ' . $user->last_he;
								} else {
									echo $user->first . ' ' . $user->last;
								}
								echo " (" . $user->user_id . ")";
								?>
							</span></td>
							<td width="48%" class="i review">
								&#10004; I reviewed my child's progress as a chayol in Hashem's army.
							</td>
						</tr>
						<tr>
							<td align="right" dir="ltr"><span class="i"><?=$user->getRankInfo(true)?></span></td>
							<td><span class="i">Parent's Signature</span></td>
						</tr>
					</table> 
				<? endif; ?>
				<div style="clear: both"></div>
			</div> 
		</div>
		<? 
		if ($page % 2 != 0 && $this->dblSided == 1) {
			echo "<div style='page-break-after: always'>&nbsp;</div>";
		}
		chdir("classes");
	}

	public function markMission() {
		chdir("../");
		$user = $this->mission;
		$numLabels = count($user->daily_labels) + count($user->weekly_labels) + count($user->shabbos_labels) + count($user->no_label_subjects);
		$tracks = $user->user_tracks;
		$numTasks = 0;
		$types = array('daily_tasks', 'weekly_tasks', 'shabbos_tasks', 'no_label_tasks');
		foreach ($tracks as $track) {
			foreach ($types as $type) {
				$numTasks += count($track->$type);
			}
		}
		$totalRows = (floor($numLabels / 2)) + $numTasks;
		
		if ($this->missionType == 2) {
			$taskClass = "mediumPicTask";
		} else {
			$taskClass = "task";
		}
		?> 
		
		<div class="firstContainer">
			<div class="header">
				<div class="userImg">
					<? if (isset($user->mobile_pic)) : ?>
						<img src="../mobile/reg/<?=$user->mobile_pic?>" width="60" alt=""/>
					<? elseif (isset($user->user_photo_id)) : ?>
						<img src="../file_view2.php?id=<?=$user->user_photo_id?>" width="60" alt=""/>
					<? else : ?>
						<img src="" width="60" alt=""/> 
					<? endif; ?>
				</div>
				<?
				$sql = "select school_number from schools where school_id = " . $user->school_class->school_id;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				?>
				<div class="schoolLogo">
					<img src="schools/<?=$row['school_number']?>.png" width="80" alt=""/>
				</div>
		    	<table>
		    		<tr>
		    			<td class="title">
							<span class="sb">Platoon:</span> 
							<?
							$grade = $user->school_class->class_grade . (isset($user->school_class->class_sub) ? '-' . $user->school_class->class_sub : '');
							echo $grade;
							?>
							<br />
							<span class="sb">Commander:</span><br>
							<?=$user->school_class->class_teacher;?>
		    			</td>
		    			<td>
		    				<div class="headerImg">
		    					<? if ($this->lang_id == 1) : ?>
		    						<img src="image/mission report logo.png" width="350" />
		    					<? elseif ($this->lang_id == 2) : ?>
		    						<img src="image/mission-report-yiddish.png" width="350" />
		    					<? endif; ?>
		    				</div>
		    			</td>
		    			<td class="title">
							<?=$user->rank_name?><br />
							<span class="b">
								<?
								if ($this->lang_id == 1) {
									echo $user->first . ' ' . $user->last;
								} else if ($this->lang_id == 2) {
									if (!empty($user->first_he) || !empty($user->last_he)) {
										echo $user->first_he . ' ' . $user->last_he;
									} else {
										echo $user->first . ' ' . $user->last;
									}
								}
								?>
							</span>
		    			</td>
		    		</tr>
		    		<tr> 
		    			<td colspan="3" class="line">
						<? 
						if ($this->dateDisplay > 0) {
							if ($this->dateDisplay == 1) { 
								?>
								<span class="hebrew-text">פרשת <?=$this->parsha?> &#10022; <?=$this->heDates[0]?> - <?=$this->heDates[6]?></span>
							<? } else if ($this->dateDisplay == 2) { ?>
								<b><?=date('M j', (jdtounix($start_date+1))) . ' - ' . date('M j, Y', (jdtounix($end_date+1)))?></b>
								<span class="hebrew-text"> &#10022; פרשת <?=$this->parsha?> &#10022; <?=$this->heDates[0]?> - <?=$this->heDates[6]?></span>
							<? } ?>
							</span></td>
						<? } else { ?>
							<span class="hebrew-text">פרשת <?=$this->parsha?></span>
						<? } ?>
						</td>
		    		</tr>
			  </table>
			</div>
			
			<div align="center" style="padding-bottom: 10px;">
				<button id="checkAll">Check All</button>
				<button id="uncheckAll">Uncheck All</button>
				<button id="twoCols">Change to two Columns</button>
			</div>
			
			<div id="loading" style="display: none;"><img src="../images/loading2.gif" /></div>
			
			<div class="marking">
				<?
				$totalRendered = 0;
				$page = 1;
				$labelAdded = 0;
				$firstColumn = true;
				
				$numDaily = count($user->daily_labels);
				if ($numDaily) {
					foreach ($user->sorted_daily_labels as $key0 => $value) {
						for ($dlno = 0; $dlno < $numDaily; $dlno++) {
							$key1 = $user->daily_labels[$dlno]; 
				            $info = explode(":", $key1); 
				            $label = $info[0]; 
				            if ($value == $user->daily_labels[$dlno]) {
								$labelAdded++;
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
												<img src="campaignLogos/<?=$this->campaignLogos[$daily_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$daily_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<? if ($daily_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											<div class="short"><?=($daily_task->short_name == '' ? '<br />' : $daily_task->short_name)?></div>
											<div class="<?=$taskClass?>"><?=$daily_task->task_name?></div>
									    </div>
									    
									    <?
									    if ($daily_task->mandatory_qty) {
									    	echo "<div class='mandatoryImg";
											if ($firstColumn) 
												echo " firstColumn";
											else 
												echo " secondColumn";
									    	echo "'><img src=\"5of7stickers/" . $this->dailyStickers[$daily_task->subject_id] . "\" /></div>";
									    }
									    ?>
									    
									    <? if ($this->lang_id == 2) : ?>
									    	<div style="float: right; margin-right: -20px; margin-top: 14px;">
									    <? else : ?>
									   		<div style="float: left; margin-left: -20px; margin-top: 14px;">
									   	<? endif; ?>
									    	<input type="checkbox" class="dailyRow" />
									    </div>
											
										<?php
										// find out the marks dates to know if this task is only on specific dates
										$dates = array();
										foreach ($daily_task->date_task_marks as $mark) {
											$dates[] = $mark->mark_date;									     
										}
										?>
									    <div class="dailyBoxes">
											<table>
												<tr>
													<? foreach ($this->days_of_week as $index => $day) : ?>
														<td>
															<? if (in_array(($this->start + $index), $dates)) : ?>
																<?php
																// make sure there's a relevant task for this day
																// (possibly the task is only for part of the week so it won't be applicable for the other part of the week)
																$k = array_search($this->start + $index, $dates);
																$mark = $daily_task->date_task_marks[$k];
																$identifier = $daily_task->date_task_id . ':' . $mark->mark_date;
																$marked = false;
																if ($mark->marked) {
																	$marked = true;
																}
																?>
																<div class="checkboxDaily <?=$marked ? 'marked' : 'unmarked'?>" id="<?=$identifier?>">
																	<? 
																	if ($marked) 
																		echo "<span class='checkmark'>&#10004;</span>";
																	?>
																</div>
															<? else : ?>
																<div class="checkboxDaily">
																	<span style='font-size: 20px; vertical-align: bottom; color: black; line-height: 1'>X</span>
																</div>
															<? endif; ?>
														</td>
													<? endforeach; ?>
												</tr>
											</table>
										 </div>
										 
										 <div style="clear: both"></div>
										<?
										/*
										$totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered;
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel); 
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
											</div>
											<div style="clear: both"></div>
											<hr />
											
											<div class="container">
											<div class="left">
											<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											$firstColumn = false;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
											<?
										}
										 * 
										 */
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
								$labelAdded++;
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
											<div class='mandatoryImg'>&nbsp;
											<?
										    if ($weekly_task->mandatory_qty) {
										    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$weekly_task->subject_id] . "' />";
										    } else {
										    	echo "<img src='' alt='' />";
										    }
										    ?>
										    </div>
										    <?
										    $class = '';
											$mark = $weekly_task->date_task_mark;
											$identifier = $weekly_task->date_task_id . ':' . $weekly_task->mark_date;
											$marked = false;
											if ($mark->marked) {
												$marked = true;
												$class = "marked";
											} else {
												$class = "unmarked";
											}
											$input = false;
											if (!is_null($weekly_task->quantity)) {
												$input = true;
												$class .= " textInput";
											}
											?>
											<div class="checkbox <?=$class?>" id="<?=$identifier?>">
												<?
												if ($input) {
													echo "<input value='" . $mark->done_qty . "' type='text' " . 
														"onkeypress='return number_validation(event);' size='1' maxlength='6' />";
												} else {
													if ($marked) 
											 			echo "<span class='checkmark'>&#10004;</span>";
												}
												?>
											</div>
											<div class="rowImg">
												<img src="campaignLogos/<?=$this->campaignLogos[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$weekly_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<div class="short"><?=($weekly_task->short_name == '' ? '<br />' : $weekly_task->short_name)?>
											<? if ($weekly_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											</div>
											<div class="<?=$taskClass?>"><?=$weekly_task->task_name?></div>
										</div>
										<div style="clear: both"></div>
									    <?
									    /*
									    $totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered + $addLabel;
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel); 
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
										</div>
										<div style="clear: both"></div>
										<hr />
										
										<div class="container">
										<div class="left">
										<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
											<?
										}
										 * 
										 */
									}
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
								$labelAdded++;
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
											<div class='mandatoryImg'>&nbsp;
											<?
										    if ($shabbos_task->mandatory_qty) {
										    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$shabbos_task->subject_id] . "' />";
										    } else {
										    	echo "<img src='' alt='' />";
										    }
										    ?>
										    </div>
											<?
										    $class = '';
											$mark = $shabbos_task->date_task_mark;
											$identifier = $shabbos_task->date_task_id . ':' . $shabbos_task->mark_date;
											$marked = false;
											if ($mark->marked) {
												$marked = true;
												$class = "marked";
											} else {
												$class = "unmarked";
											}
											$input = false;
											if (!is_null($shabbos_task->quantity)) {
												$input = true;
												$class .= " textInput";
											}
											?>
											<div class="checkbox <?=$class?>" id="<?=$identifier?>">
												<?
												if ($input) {
													echo "<input value='" . $mark->done_qty . "' type='text' " . 
														"onkeypress='return number_validation(event);' size='1' maxlength='6' />";
												} else {
													if ($marked) 
											 			echo "<span class='checkmark'>&#10004;</span>";
												}
												?>
											</div>
											<div class="rowImg">
												<img src="campaignLogos/<?=$this->campaignLogos[$shabbos_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$shabbos_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<div class="short"><?=($shabbos_task->short_name == '' ? '<br />' : $shabbos_task->short_name)?>
											<? if ($shabbos_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											</div>
											<div class="<?=$taskClass?>">
												<?
												if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty > 0) 
				                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc kapitelach.</i><br />";
				                                if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty == 0 && $shabbos_task->quantity > 0)
				                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc minutes.</i><br/>";
												if ($label_name == 'שבת מברכים' && $shabbos_task->mandatory_qty > 0) 
				                                    echo "<i>מיינע קוואטע: $shabbos_task->desc קאפיטלעך.</i><br />";
				                                if ($label_name == 'שבת מברכים' && $shabbos_task->mandatory_qty == 0 && $shabbos_task->quantity > 0)
				                                    echo "<i>מיינע קוואטע: $shabbos_task->desc מינוטן.</i><br/>";
												?>
												<?=$shabbos_task->task_name?>
											</div>
										</div>
										<div style="clear: both"></div>
										<?
										/*
										$totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered;
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel);  
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
										</div>
										<div style="clear: both"></div>
										<hr />
										
										<div class="container">
										<div class="left">
										<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
											<?
										}
										 * 
										 */						
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
						$labelAdded++;
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
									<div class='mandatoryImg'>&nbsp;
									<?
								    if ($no_label_task->mandatory_qty) {
								    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$no_label_task->subject_id] . "' />";
								    } else {
								    	echo "<img src='' alt='' />";
								    }
								    ?>
								    </div>
									<?
								    $class = '';
									$mark = $no_label_task->date_task_mark;
									$identifier = $no_label_task->date_task_id . ':' . $no_label_task->mark_date;
									$marked = false;
									if ($mark->marked) {
										$marked = true;
										$class = "marked";
									} else {
										$class = "unmarked";
									}
									$input = false;
									if (!is_null($no_label_task->quantity)) {
										$input = true;
										$class .= " textInput";
									}
									?>
									<div class="checkbox <?=$class?>" id="<?=$identifier?>">
										<?
										if ($input) {
											echo "<input value='" . $mark->done_qty . "' type='text' " . 
												"onkeypress='return number_validation(event);' size='1' maxlength='6' />";
										} else {
											if ($marked) 
									 			echo "<span class='checkmark'>&#10004;</span>";
										}
										?>
									</div>
									<div class="rowImg">
										<img src="campaignLogos/<?=$this->campaignLogos[$no_label_task->subject_id]?>" width="50" height="52" alt=""/>
									</div>
									<? if ($this->missionType == 2) { ?>
										<div class="mediumPic"><img src="color/<?=$no_label_task->medium_pic?>.jpg" /></div>
									<? } ?>
									<div class="short"><?=($no_label_task->short_name == '' ? '<br />' : $no_label_task->short_name)?>
									<? if ($no_label_task->focus_task) { ?>
										<div class="focus">
											<img src="image/31204.png" alt="" />
										</div>
									<? } ?>
									</div>
									<div class="<?=$taskClass?>"><?=$no_label_task->task_name?></div>
								</div>
								<div style="clear: both"></div>
								<?
								/*
								$totalRendered++;
								$addLabel = floor($labelAdded / 2);
								//echo $totalRendered;
								$split = $this->pager($page, $totalRendered, $totalRows, $addLabel);
								if ($split == 2) {
									echo "</div>";
									if ($page == 1)
										$this->createFooter();
									else {
										$this->createPager( $user, $page );
									}
									$page++;
									$totalRendered = 0;
									$labelAdded = 0;
									?>
								</div>
								<div style="clear: both"></div>
								<hr />			
								
								<div class="container">
								<div class="left">
								<?
								}
								else if ($split == 1) {
									$firstRow = true;
									$totalRendered += floor($labelAdded / 2);
									$labelAdded = 0;
									?>
									</div><!-- .left-sidebar -->
									<div class="right">
									<?
								}
								 * 
								 */
							}
				 		}
					}
				}
			?>
			</div>
			
			<div id="<?=$user->user_id?>" class="bottomFooter" align="center" dir="ltr">
				<input type="hidden" class="pages" value="<?=$page?>" />
				<? if ($this->lang_id == 1) : ?>
					<img class="rankLogo" src="image/rank report logo.png" />
					<img class="rank" src="../rankPics/<?=$user->rank_ord;?>BW.png" height="70" />
					<table class="footerInfo">
						<tr style="vertical-align: bottom">
							<td align="left" class="border"><span class="sb">
								<?=$user->first . ' ' . $user->last;?>
							</span></td>
							<td width="48%" class="i review">
								&#10004; I reviewed my child's progress as a chayol in Hashem's army.
							</td>
						</tr>
						<tr>
							<td align="left"><span class="i"><?=$user->getRankInfo(true)?></span></td>
							<td><span class="i">Parent's Signature</span></td>
						</tr>
					</table> 
				<? elseif ($this->lang_id == 2) : ?>
					<img class="rankLogo" src="image/rank report yiddish.png" />
					<img class="rank" src="../rankPics/<?=$user->rank_ord;?>BW.png" height="70" />
					<table class="footerInfo">
						<tr style="vertical-align: bottom">
							<td align="right" class="border"><span class="sb">
								<?
								if (!empty($user->first_he) || !empty($user->last_he)) {
									echo $user->first_he . ' ' . $user->last_he;
								} else {
									echo $user->first . ' ' . $user->last;
								}
								?>
							</span></td>
							<td width="48%" class="i review">
								&#10004; I reviewed my child's progress as a chayol in Hashem's army.
							</td>
						</tr>
						<tr>
							<td align="right" dir="ltr"><span class="i"><?=$user->getRankInfo(true)?></span></td>
							<td><span class="i">Parent's Signature</span></td>
						</tr>
					</table> 
				<? endif; ?>
				<div style="clear: both"></div>
			</div> 
		</div>
		<?
		chdir("classes");
	}

	public function markMissionCol() {
		chdir("../");
		$user = $this->mission;
		$numLabels = count($user->daily_labels) + count($user->weekly_labels) + count($user->shabbos_labels) + count($user->no_label_subjects);
		$tracks = $user->user_tracks;
		$numTasks = 0;
		$types = array('daily_tasks', 'weekly_tasks', 'shabbos_tasks', 'no_label_tasks');
		foreach ($tracks as $track) {
			foreach ($types as $type) {
				$numTasks += count($track->$type);
			}
		}
		$totalRows = (floor($numLabels / 2)) + $numTasks;
		
		if ($this->missionType == 2) {
			$taskClass = "mediumPicTask";
		} else {
			$taskClass = "task";
		}
		?> 
		
		<div class="firstContainer">
			<div class="header">
				<div class="userImg">
					<? if (isset($user->mobile_pic)) : ?>
						<img src="../mobile/reg/<?=$user->mobile_pic?>" width="60" alt=""/>
					<? elseif (isset($user->user_photo_id)) : ?>
						<img src="../file_view2.php?id=<?=$user->user_photo_id?>" width="60" alt=""/>
					<? else : ?>
						<img src="" width="60" alt=""/> 
					<? endif; ?>
				</div>
				<?
				$sql = "select school_number from schools where school_id = " . $user->school_class->school_id;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				?>
				<div class="schoolLogo">
					<img src="schools/<?=$row['school_number']?>.png" width="80" alt=""/>
				</div>
		    	<table>
		    		<tr>
		    			<td class="title">
							<span class="sb">Platoon:</span> 
							<?
							$grade = $user->school_class->class_grade . (isset($user->school_class->class_sub) ? '-' . $user->school_class->class_sub : '');
							echo $grade;
							?>
							<br />
							<span class="sb">Commander:</span><br>
							<?=$user->school_class->class_teacher;?>
		    			</td>
		    			<td>
		    				<div class="headerImg">
		    					<? if ($this->lang_id == 1) : ?>
		    						<img src="image/mission report logo.png" width="350" />
		    					<? elseif ($this->lang_id == 2) : ?>
		    						<img src="image/mission-report-yiddish.png" width="350" />
		    					<? endif; ?>
		    				</div>
		    			</td>
		    			<td class="title">
							<?=$user->rank_name?><br />
							<span class="b">
								<?
								if ($this->lang_id == 1) {
									echo $user->first . ' ' . $user->last;
								} else if ($this->lang_id == 2) {
									if (!empty($user->first_he) || !empty($user->last_he)) {
										echo $user->first_he . ' ' . $user->last_he;
									} else {
										echo $user->first . ' ' . $user->last;
									}
								}
								?>
							</span>
		    			</td>
		    		</tr>
		    		<tr> 
		    			<td colspan="3" class="line">
						<? 
						if ($this->dateDisplay > 0) {
							if ($this->dateDisplay == 1) { 
								?>
								<span class="hebrew-text">פרשת <?=$this->parsha?> &#10022; <?=$this->heDates[0]?> - <?=$this->heDates[6]?></span>
							<? } else if ($this->dateDisplay == 2) { ?>
								<b><?=date('M j', (jdtounix($start_date+1))) . ' - ' . date('M j, Y', (jdtounix($end_date+1)))?></b>
								<span class="hebrew-text"> &#10022; פרשת <?=$this->parsha?> &#10022; <?=$this->heDates[0]?> - <?=$this->heDates[6]?></span>
							<? } ?>
							</span></td>
						<? } else { ?>
							<span class="hebrew-text">פרשת <?=$this->parsha?></span>
						<? } ?>
						</td>
		    		</tr>
			  </table>
			</div>
			
			<div align="center" style="padding-bottom: 10px;">
				<button id="checkAll">Check All</button>
				<button id="uncheckAll">Uncheck All</button>
				<button id="oneCol">Change to one Column</button>
			</div>
			
			<div id="loading" style="display: none;"><img src="../images/loading2.gif" /></div>
			
			<div class="left">
				<?
				$totalRendered = 0;
				$page = 1;
				$labelAdded = 0;
				$firstColumn = true;
				
				$numDaily = count($user->daily_labels);
				if ($numDaily) {
					foreach ($user->sorted_daily_labels as $key0 => $value) {
						for ($dlno = 0; $dlno < $numDaily; $dlno++) {
							$key1 = $user->daily_labels[$dlno]; 
				            $info = explode(":", $key1); 
				            $label = $info[0]; 
				            if ($value == $user->daily_labels[$dlno]) {
								$labelAdded++;
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
												<img src="campaignLogos/<?=$this->campaignLogos[$daily_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$daily_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<? if ($daily_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											<div class="short"><?=($daily_task->short_name == '' ? '<br />' : $daily_task->short_name)?></div>
											<div class="<?=$taskClass?>"><?=$daily_task->task_name?></div>
									    </div>
									    
									    <?
									    if ($daily_task->mandatory_qty) {
									    	echo "<div class='mandatoryImg";
											if ($firstColumn) 
												echo " firstColumn";
											else 
												echo " secondColumn";
									    	echo "'><img src=\"5of7stickers/" . $this->dailyStickers[$daily_task->subject_id] . "\" /></div>";
									    }
									    ?>
									    
									    <? if ($this->lang_id == 2) : ?>
									    	<div style="float: right; margin-right: -20px; margin-top: 14px;">
									    <? else : ?>
									   		<div style="float: left; margin-left: -20px; margin-top: 14px;">
									   	<? endif; ?>
									    	<input type="checkbox" class="dailyRow" />
									    </div>
											
										<?php
										// find out the marks dates to know if this task is only on specific dates
										$dates = array();
										foreach ($daily_task->date_task_marks as $mark) {
											$dates[] = $mark->mark_date;									     
										}
										?>
									    <div class="dailyBoxes">
											<table>
												<tr>
													<? foreach ($this->days_of_week as $index => $day) : ?>
														<td>
															<? if (in_array(($this->start + $index), $dates)) : ?>
																<?php
																// make sure there's a relevant task for this day
																// (possibly the task is only for part of the week so it won't be applicable for the other part of the week)
																$k = array_search($this->start + $index, $dates);
																$mark = $daily_task->date_task_marks[$k];
																$identifier = $daily_task->date_task_id . ':' . $mark->mark_date;
																$marked = false;
																if ($mark->marked) {
																	$marked = true;
																}
																?>
																<div class="checkboxDaily <?=$marked ? 'marked' : 'unmarked'?>" id="<?=$identifier?>">
																	<? 
																	if ($marked) 
																		echo "<span class='checkmark'>&#10004;</span>";
																	?>
																</div>
															<? else : ?>
																<div class="checkboxDaily">
																	<span style='font-size: 20px; vertical-align: bottom; color: black; line-height: 1'>X</span>
																</div>
															<? endif; ?>
														</td>
													<? endforeach; ?>
												</tr>
											</table>
										 </div>
										 
										 <div style="clear: both"></div>
										<?
										$totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered;
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel); 
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
											</div>
											<div style="clear: both"></div>
											<hr />
											
											<div class="container">
											<div class="left">
											<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											$firstColumn = false;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
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
								$labelAdded++;
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
											<div class='mandatoryImg'>&nbsp;
											<?
										    if ($weekly_task->mandatory_qty) {
										    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$weekly_task->subject_id] . "' />";
										    } else {
										    	echo "<img src='' alt='' />";
										    }
										    ?>
										    </div>
										    <?
										    $class = '';
											$mark = $weekly_task->date_task_mark;
											$identifier = $weekly_task->date_task_id . ':' . $weekly_task->mark_date;
											$marked = false;
											if ($mark->marked) {
												$marked = true;
												$class = "marked";
											} else {
												$class = "unmarked";
											}
											$input = false;
											if (!is_null($weekly_task->quantity)) {
												$input = true;
												$class .= " textInput";
											}
											?>
											<div class="checkbox <?=$class?>" id="<?=$identifier?>">
												<?
												if ($input) {
													echo "<input value='" . $mark->done_qty . "' type='text' " . 
														"onkeypress='return number_validation(event);' size='1' maxlength='6' />";
												} else {
													if ($marked) 
											 			echo "<span class='checkmark'>&#10004;</span>";
												}
												?>
											</div>
											<div class="rowImg">
												<img src="campaignLogos/<?=$this->campaignLogos[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$weekly_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<div class="short"><?=($weekly_task->short_name == '' ? '<br />' : $weekly_task->short_name)?>
											<? if ($weekly_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											</div>
											<div class="<?=$taskClass?>"><?=$weekly_task->task_name?></div>
										</div>
										<div style="clear: both"></div>
									    <?
									    $totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered + $addLabel;
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel); 
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
										</div>
										<div style="clear: both"></div>
										<hr />
										
										<div class="container">
										<div class="left">
										<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
											<?
										}
									}
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
								$labelAdded++;
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
											<div class='mandatoryImg'>&nbsp;
											<?
										    if ($shabbos_task->mandatory_qty) {
										    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$shabbos_task->subject_id] . "' />";
										    } else {
										    	echo "<img src='' alt='' />";
										    }
										    ?>
										    </div>
											<?
										    $class = '';
											$mark = $shabbos_task->date_task_mark;
											$identifier = $shabbos_task->date_task_id . ':' . $shabbos_task->mark_date;
											$marked = false;
											if ($mark->marked) {
												$marked = true;
												$class = "marked";
											} else {
												$class = "unmarked";
											}
											$input = false;
											if (!is_null($shabbos_task->quantity)) {
												$input = true;
												$class .= " textInput";
											}
											?>
											<div class="checkbox <?=$class?>" id="<?=$identifier?>">
												<?
												if ($input) {
													echo "<input value='" . $mark->done_qty . "' type='text' " . 
														"onkeypress='return number_validation(event);' size='1' maxlength='6' />";
												} else {
													if ($marked) 
											 			echo "<span class='checkmark'>&#10004;</span>";
												}
												?>
											</div>
											<div class="rowImg">
												<img src="campaignLogos/<?=$this->campaignLogos[$shabbos_task->subject_id]?>" width="50" height="52" alt=""/>
											</div>
											<? if ($this->missionType == 2) { ?>
												<div class="mediumPic"><img src="color/<?=$shabbos_task->medium_pic?>.jpg" /></div>
											<? } ?>
											<div class="short"><?=($shabbos_task->short_name == '' ? '<br />' : $shabbos_task->short_name)?>
											<? if ($shabbos_task->focus_task) { ?>
												<div class="focus">
													<img src="image/31204.png" alt="" />
												</div>
											<? } ?>
											</div>
											<div class="<?=$taskClass?>">
												<?
												if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty > 0) 
				                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc kapitelach.</i><br />";
				                                if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty == 0 && $shabbos_task->quantity > 0)
				                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc minutes.</i><br/>";
												if ($label_name == 'שבת מברכים' && $shabbos_task->mandatory_qty > 0) 
				                                    echo "<i>מיינע קוואטע: $shabbos_task->desc קאפיטלעך.</i><br />";
				                                if ($label_name == 'שבת מברכים' && $shabbos_task->mandatory_qty == 0 && $shabbos_task->quantity > 0)
				                                    echo "<i>מיינע קוואטע: $shabbos_task->desc מינוטן.</i><br/>";
												?>
												<?=$shabbos_task->task_name?>
											</div>
										</div>
										<div style="clear: both"></div>
										<?
										$totalRendered++;
										$addLabel = floor($labelAdded / 2);
										//echo $totalRendered;
										$split = $this->pager($page, $totalRendered, $totalRows, $addLabel);  
										if ($split == 2) {
											echo "</div>";
											if ($page == 1)
												$this->createFooter();
											else {
												$this->createPager( $user, $page );
											}
											$page++;
											$totalRendered = 0;
											$labelAdded = 0;
											?>
										</div>
										<div style="clear: both"></div>
										<hr />
										
										<div class="container">
										<div class="left">
										<?
										}
										else if ($split == 1) {
											$firstRow = true;
											$totalRendered += floor($labelAdded / 2);
											$labelAdded = 0;
											?>
											</div><!-- .left-sidebar -->
											<div class="right">
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
						$labelAdded++;
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
									<div class='mandatoryImg'>&nbsp;
									<?
								    if ($no_label_task->mandatory_qty) {
								    	echo "<img src='stickerOutlines/" . $this->stickerOutlines[$no_label_task->subject_id] . "' />";
								    } else {
								    	echo "<img src='' alt='' />";
								    }
								    ?>
								    </div>
									<?
								    $class = '';
									$mark = $no_label_task->date_task_mark;
									$identifier = $no_label_task->date_task_id . ':' . $no_label_task->mark_date;
									$marked = false;
									if ($mark->marked) {
										$marked = true;
										$class = "marked";
									} else {
										$class = "unmarked";
									}
									$input = false;
									if (!is_null($no_label_task->quantity)) {
										$input = true;
										$class .= " textInput";
									}
									?>
									<div class="checkbox <?=$class?>" id="<?=$identifier?>">
										<?
										if ($input) {
											echo "<input value='" . $mark->done_qty . "' type='text' " . 
												"onkeypress='return number_validation(event);' size='1' maxlength='6' />";
										} else {
											if ($marked) 
									 			echo "<span class='checkmark'>&#10004;</span>";
										}
										?>
									</div>
									<div class="rowImg">
										<img src="campaignLogos/<?=$this->campaignLogos[$no_label_task->subject_id]?>" width="50" height="52" alt=""/>
									</div>
									<? if ($this->missionType == 2) { ?>
										<div class="mediumPic"><img src="color/<?=$no_label_task->medium_pic?>.jpg" /></div>
									<? } ?>
									<div class="short"><?=($no_label_task->short_name == '' ? '<br />' : $no_label_task->short_name)?>
									<? if ($no_label_task->focus_task) { ?>
										<div class="focus">
											<img src="image/31204.png" alt="" />
										</div>
									<? } ?>
									</div>
									<div class="<?=$taskClass?>"><?=$no_label_task->task_name?></div>
								</div>
								<div style="clear: both"></div>
								<?
								$totalRendered++;
								$addLabel = floor($labelAdded / 2);
								//echo $totalRendered;
								$split = $this->pager($page, $totalRendered, $totalRows, $addLabel);
								if ($split == 2) {
									echo "</div>";
									if ($page == 1)
										$this->createFooter();
									else {
										$this->createPager( $user, $page );
									}
									$page++;
									$totalRendered = 0;
									$labelAdded = 0;
									?>
								</div>
								<div style="clear: both"></div>
								<hr />			
								
								<div class="container">
								<div class="left">
								<?
								}
								else if ($split == 1) {
									$firstRow = true;
									$totalRendered += floor($labelAdded / 2);
									$labelAdded = 0;
									?>
									</div><!-- .left-sidebar -->
									<div class="right">
									<?
								}
							}
				 		}
					}
				}
			?>
			</div>
			
			<div id="<?=$user->user_id?>" class="bottomFooter" align="center" dir="ltr">
				<input type="hidden" class="pages" value="<?=$page?>" />
				<? if ($this->lang_id == 1) : ?>
					<img class="rankLogo" src="image/rank report logo.png" />
					<img class="rank" src="../rankPics/<?=$user->rank_ord;?>BW.png" height="70" />
					<table class="footerInfo">
						<tr style="vertical-align: bottom">
							<td align="left" class="border"><span class="sb">
								<?=$user->first . ' ' . $user->last;?>
							</span></td>
							<td width="48%" class="i review">
								&#10004; I reviewed my child's progress as a chayol in Hashem's army.
							</td>
						</tr>
						<tr>
							<td align="left"><span class="i"><?=$user->getRankInfo(true)?></span></td>
							<td><span class="i">Parent's Signature</span></td>
						</tr>
					</table> 
				<? elseif ($this->lang_id == 2) : ?>
					<img class="rankLogo" src="image/rank report yiddish.png" />
					<img class="rank" src="../rankPics/<?=$user->rank_ord;?>BW.png" height="70" />
					<table class="footerInfo">
						<tr style="vertical-align: bottom">
							<td align="right" class="border"><span class="sb">
								<?
								if (!empty($user->first_he) || !empty($user->last_he)) {
									echo $user->first_he . ' ' . $user->last_he;
								} else {
									echo $user->first . ' ' . $user->last;
								}
								?>
							</span></td>
							<td width="48%" class="i review">
								&#10004; I reviewed my child's progress as a chayol in Hashem's army.
							</td>
						</tr>
						<tr>
							<td align="right" dir="ltr"><span class="i"><?=$user->getRankInfo(true)?></span></td>
							<td><span class="i">Parent's Signature</span></td>
						</tr>
					</table> 
				<? endif; ?>
				<div style="clear: both"></div>
			</div> 
		</div>
		<?
		chdir("classes");
	}

	abstract public function pager( $page, $totalRendered, $totalRows, $addLabel = 0 );	
}
?>