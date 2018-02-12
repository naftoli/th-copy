    <!-- ****************************** NO LABEL TASKS ****************************** -->
    <DIV name="no_labels_div" id="no_labels_div">
    <? if (count($user->no_label_subjects) > 0) : ?>
        <? for ($nlno = 0; $nlno < count($user->no_label_subjects); $nlno++) : ?>
<? 
            $key1 = $user->no_label_subjects[$nlno];
            $info = explode(":", $key1); 
            $subject_name = $info[0]; 
            $mission_name = $info[1];
?>
        <div class="dontsplit">
        <div class="marking module">
            <div class="row top_row">
                <div class="days">
                    <div class="cell">
                    </div>
                </div>
                <div class="cell">
                    <?=$subject_name;?> - <?=$mission_name;?>
                </div>
            </div>
            
            <? for ($nltno = 0; $nltno < count($user->no_label_tasks); $nltno++) : ?>
            
            <?
            /*
            print_r($user->no_label_tasks[$nltno]);
            if ($nltno == 1) {
                exit;
            }
            */
            ?>

                <?
                $no_label_task = $user->no_label_tasks[$nltno];         
                $subject_name = $no_label_task->subject_name;
                $mission_name = $no_label_task->mission_name;
                $key2 = $subject_name . ":" . $mission_name;
                ?>          
            
                <? if ($key1 == $key2) : ?>
                                
                    <? if ($no_label_task->mandatory_qty > 0) : ?>
            <div class="row tasks mission"> 
                    <? else : ?>
            <div class="row tasks bonus">
                    <? endif; ?>
                <div class="days">
                    <? $date_task_mark = $no_label_task->date_task_mark; ?> 
                    <? if ($date_task_mark->marked == true) $mark_date = $date_task_mark->mark_date; else $mark_date = $no_label_task->end_date; ?>

                    <? if ($date_task_mark->marked == true) $checked = "checked"; else $checked = "unchecked";?>
                        
                    <? if (is_null($no_label_task->quantity)) : ?>                      
                    <div class="cell checkbox <?=$checked;?>">
                        <span class="checkbox_span <?=$checked;?>" onclick="update_weekly_shabbos_date_task(this, <?=$date_task_mark->date_task_id;?>, <?=$mark_date;?>);">
                            <input type="checkbox">
                        </span>
                    </div>  
                    <? else :?>
                    <div class="cell text_input">
                        <input value="<?=$date_task_mark->done_qty;?>" type="text" onblur="update_mark(this, <?=$date_task_mark->date_task_id;?>, <?=$mark_date;?>)"  onkeypress="return number_validation(event, this, <?=$date_task_mark->date_task_id;?>, <?=$mark_date;?>);" maxlength="3">
                    </div>
                    <? endif; ?>
                </div>

                <? if ($no_label_task->focus_task == 1) : ?>
                <div class="icon focus">
                    <img alt="" width="20" height="20" src="images/Charge-card-with-burst-teeny.png">
                </div>
                <? endif; ?>
                    
                <div class="campaign_logo">
                    <? if ( isset( $no_label_task->subject_image_id ) ) { ?>
                    <img alt="" src="/images/stickers/Sticker-<?=$no_label_task->subject_image_id;?>.gif">
                    <? } ?>
                </div>
                
                <div class="cell">
                    <? if ($no_label_task->mandatory_qty > 0) : ?>
                        <img alt="" width="16" height="16" src="images/icon_star.png">
                    <? endif; ?>
                    <?=$no_label_task->task_name;?>
                </div>
            </div>
            
                <? endif; ?>
                
            <? endfor; ?>
            
        </div> <!-- <div class="marking module"> -->
        </div>
        <? endfor; ?>
        
    <? endif; ?>
    </DIV>
    <!-- ****************************** NO LABEL TASKS ****************************** -->
