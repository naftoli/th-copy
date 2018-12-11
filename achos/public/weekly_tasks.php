    <!-- ****************************** WEEKLY TASKS ****************************** --> 
    <? //////////$today = gregoriantojd(date("n"), date("j"), date("Y")); // int $month , int $day , int $year ?>
    
    <DIV name="weekly_div" id="weekly_div">
    
        <? if (count($user->weekly_labels) > 0) : ?>
        
			<? foreach ($user->sorted_weekly_labels as $label_name1) : ?>
			
				<div class="marking module dontsplit">
            
                    <? for ($lno = 0; $lno < count($user->weekly_labels); $lno++) : ?>
                    
                        <? if ($label_name1 == $user->weekly_labels[$lno]) : ?>
                        
                            <div class="row top_row">
                                <div class="days">
                                    <div class="cell">&nbsp;</div>
                                </div>
                            
                                <div class="cell">
                                    <?
                                    echo $user->weekly_labels[$lno];
									switch (strtolower($user->weekly_labels[$lno])) {
										case 'weekly':
											echo "<br /><span style='font-size: 12px'>2 points (3 mandatory)</span>";
											break;
										case 'monthly':
											echo "<br /><span style='font-size: 12px'>4 points (starred mandatory)</span>";
											break;
									}
									?>
                                </div>
                            </div>      
                        
                            <? for ($wtno = 0; $wtno < count($user->weekly_tasks); $wtno++) : ?>
                            
                            	<? //if (!$user->weekly_tasks[$wtno]->master_task_id) continue; ?>
                            
                                    <? $label_name = $user->weekly_tasks[$wtno]->label_name; ?>
                                    
                                    <? if ($label_name == $user->weekly_labels[$lno]) : ?>
                                        <? $weekly_task = $user->weekly_tasks[$wtno]; ?>
                                        
                                        <? //print_r($weekly_task); ?>
                                        
                                        <? if ($weekly_task->mandatory_qty > 0) : ?>
                            <div class="row tasks mission<?=($weekly_task->master_task_id)?'':' master'?>">
                                        <? else :?>
                            <div class="row tasks bonus<?=($weekly_task->master_task_id)?'':' master'?>">
                                        <? endif; ?>
									
									<? //if ($weekly_task->master_task_id) : ?>
                                    <div class="days">  
                                        <? $date_task_mark = $weekly_task->date_task_mark; ?>
                                                                                
                                        <? if ($date_task_mark->marked == true) $checked = "checked"; else $checked = "unchecked"; ?>
                                        
                                        <? //////////if ($date_task_mark->date_task_id > 0) $mark_date = $date_task_mark->mark_date; else $mark_date = $today; ?>
                                        
                                        <? if (is_null($weekly_task->quantity)) : ?>                        
                                        <div class="cell checkbox <?=$checked;?>">
                                            <span id="<?=$date_task_mark->date_task_id;?>:<?=$weekly_task->mark_date;?>" class="checkbox_span <?=$checked;?>" onclick="update_weekly_shabbos_date_task(this, <?=$weekly_task->date_task_id;?>, <?=$weekly_task->mark_date;?>);">
                                                <input type="checkbox">
                                            </span>
                                        </div>  
                                        <? else :?>
                                        <div class="cell text_input">
                                            <input value="<?=$date_task_mark->done_qty;?>" type="text" onblur="update_mark(this, <?=$date_task_mark->date_task_id;?>, <?=$weekly_task->mark_date;?>)" onkeypress="return number_validation(event);" maxlength="3">
                                        </div>
                                        <? endif; ?>
                                    </div>
                                    <? //endif; ?>
                                   
                                    <div class="cell">
                                        <?
                                    	echo $weekly_task->task_name;
										if ($weekly_task->mandatory_qty > 0)
											echo " <span style='color: red; font-weight: bold;'>*</span>";
										echo "<br /><span style='font-size: 9px;'>" . $weekly_task->description . "</span>";
										//if (!$weekly_task->master_task_id) {
										//	echo "<span style='font-size: 10px;'> " . $weekly_task->points . " point(s) </span>";
										//}
										?>
                                    </div>
                                                    
                            </div>
                                                    
                                    <? endif; ?>
                                    
                            <? endfor; ?>
                        
                        <? endif; ?> <!-- if ($label_name1 == $user->weekly_labels[$lno]) : -->
                        
                    <? endfor; ?> <!-- for ($lno = 0; $lno < count($user->weekly_labels); $lno++) : -->
					
				</div>
				
			<? endforeach; ?> <!-- foreach ($user->sorted_shabbos_labels as $label_name1) : -->
            
        <? endif; ?> <!-- if (count($user->weekly_labels) > 0) : -->
        
    </DIV>
    <!-- ****************************** WEEKLY TASKS ****************************** -->     
