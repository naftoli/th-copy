<?php 
include_once ("../header.php");
include_once('../file_save.php');
include_once('../calendar.php');

$mission = gri('mission', -1);
$hdate = gr('hdate');

if($mission>0 && $hdate)
{
$mission_data = mysql_fetch_assoc(mq("
SELECT date_tasks_missions.*, subjects.subject_name
FROM date_tasks_missions, subjects
WHERE
    date_tasks_missions.date_tasks_mission_id = {$mission} AND
    date_tasks_missions.subject_id = subjects.subject_id
"));
    
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
       school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, school_id,
       rank_name, rank_image_id, rank_color, track_id , level
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
     LEFT JOIN (SELECT * FROM user_tracks WHERE user_id = {$user['user_id']} AND subject_id={$mission_data['subject_id']}) user_tracks USING (user_id)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));

$missions_execution = mysql_fetch_column(mq("
    SELECT * 
    FROM date_tasks_marks, date_tasks, date_tasks_dates
    WHERE
        date_tasks_marks.user_id = {$user['user_id']} AND
        date_tasks.date_tasks_mission_id = $mission AND    
        date_tasks.date_task_id = date_tasks_marks.date_task_id AND
        date_tasks.date_task_id = date_tasks_dates.date_task_id
    GROUP BY date_tasks.ord
    "));
$tasks = mysql_fetch_column(mq("
SELECT * 
FROM date_tasks
WHERE
    date_tasks_mission_id = {$mission}
ORDER BY ord
"));

//echo '<p><pre style="font:normal 14px arial;color:#fff;">';print_r($mission_data);echo '</pre></p>';

$title = "Tasks";
include("includes/header.php"); 
include("includes/slider.php");
include("includes/scroll.php");
?>


<script type="text/javascript">
setCurrent(1)
</script>

<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Tasks</div>
            <div class="three_column padding_top">
              <div class="content">
                    <div id="slider">
                      <ul class="tasks">
                            <li>
                            	<div class="slider_title"><?=$mission_data['subject_name'];?> - Mission #<?=$mission;?> - <?=$hdate;?></div>
                            	<div class="task_boxes scroll-pane">
                                    <div class="task_group">
                                    	<div class="icon"></div>
                                    	<div class="title">Week 1 - 4</div>
                                        
                                    <?php
                                        foreach ($tasks as $task)
                                        {
                                    ?>
                                        <div class="task"><?=$task['name'];?>
                                            <!-- <div class="check_on"></div>
                                            <div class="miles">332 Miles</div> -->
                                        </div>
                                    <?php
                                        }
                                    ?>
                                        <!--
                                        <div class="task">I explained the niggun
                                            <div class="check_off"></div>
                                            <div class="miles">0 Miles</div>
                                        </div>
                                        <div class="task">I sung the niggun by the Shabbos Table
                                            <div class="check_on"></div>
                                            <div class="miles">2 Miles</div>
                                        </div>
                                        <div class="task">I explained the niggun
                                            <div class="check_off"></div>
                                            <div class="miles">0 Miles</div>
                                        </div>
                                    </div>
                                    <div class="task_group">
                                    	<div class="icon"></div>
                                    	<div class="title">Week 2 - פרשת לך לך</div>
                                        <div class="task">I sung the niggun by the Shabbos Table
                                            <div class="check_on"></div>
                                            <div class="miles">2 Miles</div>
                                        </div>
                                        <div class="task">I explained the niggun
                                            <div class="check_off"></div>
                                            <div class="miles">0 Miles</div>
                                        </div>
                                    </div>
                                    <div class="task_group">
                                    	<div class="icon"></div>
                                    	<div class="title">Week 3 - פרשת וירא</div>
                                        <div class="task">I sung the niggun by the Shabbos Table</div>
                                        <div class="task">I explained the niggun</div>
                                    </div>
                                    <div class="task_group">
                                    	<div class="icon"></div>
                                    	<div class="title">Week 4 - פרשת וירא</div>
                                        <div class="task">I sung the niggun by the Shabbos Table</div>
                                        <div class="task">I explained the niggun</div>
                                    </div>
                                    -->
                                    <!--<div class="task button_back"><a href="#" onClick="javascript:history.go(-1); return false">Back to Aleph Champs</a></div>-->
                                </div>
                            </li>
                      </ul>
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>
<?php include("includes/footer.php"); ?>
<?php
}
else
{
    header('Location: prof_medal.php');
    exit("<script>window.location = 'prof_medal.php';</script>");
}
?>