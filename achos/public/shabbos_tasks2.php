    <!-- ****************************** SHABBOS TASKS ****************************** -->    
    <DIV name="shabbos_div" id="shabbos_div">
    
        <? if (count($user->shabbos_labels) > 0) : ?>
            
                <? foreach ($user->sorted_shabbos_labels as $label_name1) : ?>
                    
                    <? for ($lno = 0; $lno < count($user->shabbos_labels); $lno++) : ?>
                    
                        <? if ($label_name1 == $user->shabbos_labels[$lno]) : ?>
                        
            <div class="marking module">            
                
                        <div class="row top_row">
                            <div class="days">
                                <div class="cell">&nbsp;</div>
                            </div>
                        
                            <div class="cell">
                                <?=$user->shabbos_labels[$lno];?>
                            </div>
                        </div>      
                    
                            <? for ($stno = 0; $stno < count($user->shabbos_tasks); $stno++) : ?>
                                <? $label_name = $user->shabbos_tasks[$stno]->label_name; ?>
                                
                                <? if ($label_name == $user->shabbos_labels[$lno]) : ?>
                                        <? $shabbos_task = $user->shabbos_tasks[$stno]; ?>
                                    
                                        <? if ($shabbos_task->mandatory_qty > 0) : ?>
                        <div class="row tasks mission"> 
                                        <? else :?>
                        <div class="row tasks bonus">
                                        <? endif; ?>

                                
                                <div class="days">  
                                        <? $date_task_mark = $shabbos_task->date_task_mark; ?>
                                        
                                        <? if ($date_task_mark->marked == true) $checked = "checked"; else $checked = "unchecked"; ?>
                                        <? if ($date_task_mark->date_task_id > 0) $mark_date = $date_task_mark->mark_date; else $mark_date = 0; ?>
                                    
                                        <? if (is_null($shabbos_task->quantity)) : ?>                       
                                    <div class="cell checkbox <?=$checked;?>">
                                        <span id="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" class="checkbox_span <?=$checked;?>" onclick="update_weekly_shabbos_date_task(this, <?=$shabbos_task->date_task_id;?>, <?=$mark_date;?>);">
                                            <input type="checkbox">
                                        </span>
                                    </div>  
                                        <? else :?>
                                    <div class="cell text_input">
                                        <input value="<?=$date_task_mark->done_qty;?>" type="text" onblur="update_mark(this, <?=$date_task_mark->date_task_id;?>, <?=$date_task_mark->mark_date;?>)" onkeypress="return number_validation(event, this, <?=$date_task_mark->date_task_id;?>, <?=$date_task_mark->mark_date;?>);" maxlength="3">
                                    </div>
                                        <? endif; ?>
                                </div>
                                
                                    <? if ($shabbos_task->focus_task == 1) : ?>
                                <div class="icon focus">
                                    <img alt="" width="20" height="20" src="images/Charge-card-with-burst-teeny.png">
                                </div>
                                    <? endif; ?>

                                <div class="campaign_logo">
                                    <img alt="Task Sticker" src="/images/stickers/Sticker-<?=$shabbos_task->subject_image_id;?>.gif">
                                </div>
                                
                                <div class="cell">
                                    <? if ($shabbos_task->mandatory_qty > 0) : ?>
                                        <img alt="" width="16" height="16" src="images/icon_star.png">
                                    <? endif; ?>
                                    <? if ($label_name == 'Shabbos Mevorchim' and $shabbos_task->mandatory_qty > 0) 
                                        echo "<i>Your quota is: Kapital $shabbos_task->desc</i><br />";
                                       if ($label_name == 'Shabbos Mevorchim' and $shabbos_task->mandatory_qty == 0 and $shabbos_task->quantity > 0)
                                        echo "<i>Your quota is: $shabbos_task->desc of tehillim</i><br/>";
                                    ?>
                                    <?=$shabbos_task->task_name;?>
                                </div>
                                                
                            </div>
                    
                                <? endif; ?> <!-- if ($label_name == $user->shabbos_labels[$lno]) : -->
                            
                            <? endfor; ?> <!-- for ($stno = 0; $stno < count($user->shabbos_tasks); $stno++) : -->
                        
            </div>          
            
                        <? endif; ?> <!-- if ($label_name == $user->shabbos_tasks[$stno]->label_name) : -->
                        
                    <? endfor; ?> <!-- for ($lno = 0; $lno < count($user->shabbos_labels); $lno++) : -->                    
                                
                <? endforeach; ?> <!-- foreach ($user->sorted_shabbos_labels as $key0) : -->
                    
        <? endif; ?> <!-- if (count($user->shabbos_labels) > 0) : -->
    </DIV>  
    
    <!-- ****************************** SHABBOS TASKS ****************************** -->

    <?
    //add dedication
        if ($start_date == 2455977) {
            ?>
            <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
            <div class="print_footer">
                <div class="dedication">
                    <p>This weeks missions are dedicated in memory of<br /> 
                        <b>Mrs. Freida Zissel Bas Aaron Yechezkel Vogel OBM</b><br />
                        on the occasion of her first yahrzeit ב' אדר</p>
                    <p>Please learn the following Mishna on her yahrzeit in her merit.</p>
                    <p>פירות חוצה לארץ שנכנסו לארץ, חייבין בחלה ובמעשרות <br />יצאו מכאן לשם רבי אליעזר מחייב, ורבי עקיבה פוטר</p>
                    <p>Dedicated by Nuchi Vogel and family. <br />London UK.</p>
                </div>
            </div>
            <?
        }
    ?>