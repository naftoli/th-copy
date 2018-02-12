    <!-- ****************************** WEEKLY TASKS ****************************** --> 
    <? //////////$today = gregoriantojd(date("n"), date("j"), date("Y")); // int $month , int $day , int $year ?>
    
    <DIV name="weekly_div" id="weekly_div">
    
        <? if (count($user->weekly_labels) > 0) : ?>
        
            <div class="marking module">
            
                <? foreach ($user->sorted_weekly_labels as $label_name1) : ?>
            
                    <? for ($lno = 0; $lno < count($user->weekly_labels); $lno++) : ?>
                    
                        <? if ($label_name1 == $user->weekly_labels[$lno]) : ?>
                        
                            <div class="row top_row">
                                <div class="days">
                                    <div class="cell">&nbsp;</div>
                                </div>
                            
                                <div class="cell">
                                    <?=$user->weekly_labels[$lno];?>
                                </div>
                            </div>      
                        
                            <? for ($wtno = 0; $wtno < count($user->weekly_tasks); $wtno++) : ?>
                                    <? $label_name = $user->weekly_tasks[$wtno]->label_name; ?>
                                    
                                    <? if ($label_name == $user->weekly_labels[$lno]) : ?>
                                        <? $weekly_task = $user->weekly_tasks[$wtno]; ?>
                                        
                                        <? if ($weekly_task->mandatory_qty > 0) : ?>
                            <div class="row tasks mission"> 
                                        <? else :?>
                            <div class="row tasks bonus">
                                        <? endif; ?>

                                    
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

                                    <? if ($weekly_task->focus_task == 1) : ?>
                                    <div class="icon focus">
                                        <img alt="" width="20" height="20" src="images/Charge-card-with-burst-teeny.png">
                                    </div>
                                    <? endif; ?>

                                    <div class="campaign_logo">
                                        <img alt="" src="/images/stickers/Sticker-<?=$weekly_task->subject_image_id;?>.gif">
                                    </div>
                                    
                                    <div class="cell">
                                        <? if ($weekly_task->mandatory_qty > 0) : ?>
                                            <img alt="" width="16" height="16" src="images/icon_star.png">
                                        <? endif; ?>
                                        <?=$weekly_task->task_name;?>
                                    </div>
                                                    
                            </div>
                                                    
                                    <? endif; ?>
                                    
                            <? endfor; ?>
                        
                        <? endif; ?> <!-- if ($label_name1 == $user->weekly_labels[$lno]) : -->
                        
                    <? endfor; ?> <!-- for ($lno = 0; $lno < count($user->weekly_labels); $lno++) : -->
                    
                <? endforeach; ?> <!-- foreach ($user->sorted_shabbos_labels as $label_name1) : -->
                
            </div>
            
        <? endif; ?> <!-- if (count($user->weekly_labels) > 0) : -->
        
    </DIV>
    <!-- ****************************** WEEKLY TASKS ****************************** -->     
