    <!-- ****************************** DAILY TASKS ****************************** -->      
    <DIV name="daily_div" id="daily_div">
        
        <? if (count($user->daily_labels) > 0) : ?>
        
            <? foreach ($user->sorted_daily_labels as $key0 => $value) : ?>
                
                
                <? echo "<input type='hidden' name='SORTED DAILY LABEL VALUE' value='" . $value . "'>\n"; ?>

                            
                <? for ($dlno = 0; $dlno < count($user->daily_labels); $dlno++) : ?>
                
                    <? 
                    $key1 = $user->daily_labels[$dlno]; 
                    $info = explode(":", $key1); 
                    $label = $info[0]; 
                    $start_date = $info[1]; 
                    $end_date = $info[2]; 
                    ?>
                
                    <? 
                        if ($label == 'Davening like a Chayol')
                            echo "<input type='hidden' name='5) DAILY LABEL INFO' value='" . $value . "=" . $user->daily_labels[$dlno] . "'>\n"; ?>
                    
                    <? if ($value == $user->daily_labels[$dlno]) : ?>
                    
                        
                        <? if ($label == 'Davening like a Chayol')
                            echo "<input type='hidden' name='IS EQUAL' value='IS EQUAL'>\n"; ?>
                        
                        <div class="marking module">
                            <div class="row top_row">
                                <div class="days">
                                    <? for ($dno = $start_date; $dno <= $end_date; $dno++) : ?>
                                    <? if ($dno % 7 == 5) $special = " special"; else $special = ""; ?>                 
                                    <div class="cell<?=$special;?>"><?=$days_of_the_week[$dno % 7];?></div>
                                    <? endfor; ?>               
                                </div>
                                
                                <div class="cell">
                                    <?=$label; ?>
                                </div>
                            </div>
                            

                            <? for ($dtno = 0; $dtno < count($user->daily_tasks); $dtno++) : ?>
                                <? $key2 = str_replace(":", " ", $user->daily_tasks[$dtno]->label_name) . ":" . $user->daily_tasks[$dtno]->start_date . ":" . $user->daily_tasks[$dtno]->end_date; ?>
                                    
                                <? if ($key1 == $key2) : ?>
                                    <? $daily_task = $user->daily_tasks[$dtno]; ?>
                                    
                                    <? if ($daily_task->mandatory_qty > 0) : ?>
                            <div class="row tasks mission"> 
                                    <? else : ?>
                            <div class="row tasks bonus">                   
                                    <? endif; ?>
                                    
                                <!-- ***** DATE TASKS MARKS ***** -->
                                <div class="days">
                                <? for ($dtmno = 0; $dtmno < count($daily_task->date_task_marks); $dtmno++) : ?>
                                    <? $date_task_mark = $daily_task->date_task_marks[$dtmno]; ?>
                                    
                                    <? if ($date_task_mark->marked == true) $checked = "checked"; else $checked = "unchecked";?>
                                    <? //if ($date_task_mark->mark_points > 0) $checked = "checked"; else $checked = "unchecked";?>
                                    
                                    
                                    <? if (is_null($daily_task->quantity)) : ?>                     
                                    <div class="cell checkbox <?=$checked;?>">
                                        <span id="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" class="checkbox_span <?=$checked;?>" onclick="update_daily_date_task(this, <?=$date_task_mark->date_task_id;?>, <?=$date_task_mark->mark_date;?>);">
                                            <input type="checkbox">
                                            <input type="hidden" name="">
                                        </span>
                                    </div>  
                                    <? else :?>
                                    <div class="cell text_input">
                                        <input value="<?=$date_task_mark->done_qty;?>" name="textbox_cell"id="textbox_cell" type="text" onblur="update_mark(this, <?=$date_task_mark->date_task_id;?>, <?=$date_task_mark->mark_date;?>)" onkeypress="return number_validation(event, this, <?=$date_task_mark->date_task_id;?>, <?=$date_task_mark->mark_date;?>);" maxlength="3">
                                    </div>
                                    <? endif; ?>
                                    
                                <? endfor; ?>
                                </div>
                                <!-- ***** DATE TASKS MARKS ***** -->               
                                    
                                    
                                <? if ($daily_task->focus_task == 1) : ?>
                                <div class="icon focus">
                                    <img alt="" width="20" height="20" src="images/Charge-card-with-burst-teeny.png">
                                </div>
                                <? endif; ?>

                                <div class="campaign_logo">
                                    <img alt="" src="/images/stickers/Sticker-<?=$daily_task->subject_image_id;?>.gif">
                                </div>
                            
                                <div class="cell">                                     
                                    <? if ($daily_task->mandatory_qty > 0) : ?>
                                        <img alt="" width="16" height="16" src="images/icon_star.png">
                                    <? endif; ?>
                                    <?=$daily_task->task_name;?>
                                </div>                                                  
                            </div>
                                <? endif; ?>
                                
                            <? endfor; ?>
                        </div>
                    
                    <? endif; ?> <!-- if ($key0 == $user->daily_labels[$dlno]) : -->
                    
                <? endfor; ?> <!-- for ($dlno = 0; $dlno < count($user->daily_labels); $dlno++) -->
            
            <? endforeach; ?> <!-- foreach ($user->sorted_daily_labels as $key0) : -->
            
        <? endif; ?> <!-- if (count($user->daily_labels) > 0) -->
                        
        <div class="print_footer">
            <div class="marking module clearfix">
                <p>1. Complete any mission with a <img alt="" width="16" height="16" src="images/icon_star.png"> next to it and you get a sticker. If a mission has more then one <img alt="" width="16" height="16" src="images/icon_star.png"> you need to complete all the those tasks to get the sticker. All other tasks may be equally important, but do not affect the completion of the mission. The part of the task within brackets is not mandatory to complete your mission.</p>
                <p>2. Complete any mission task with a <img alt="" width="12" height="12" src="images/Charge-card-with-burst-teeny.png">
 and you will earn a Focus Card .</p>
                <p>3. Every daily task must be done 5 out of 7 times to complete your mission.</p>
                <p>4. The <b>amount</b> you need to say or do is called a <b>quota</b>. Your commander decides with you how much your quota will be.</p>
                <p>5. If it was not possible for you to do a task, bring a note to your base commander.</p>
            </div>
        </div>
        
    </DIV>  
    <!-- ****************************** DAILY TASKS ****************************** -->      
