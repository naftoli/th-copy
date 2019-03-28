<?php 
require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

function dbResultToArray($result)
{
    $aresult = array();
    $n=0;
    while ($data = mysql_fetch_array($result, MYSQL_ASSOC))
    {
        $aresult[$n] = $data;
        $n++;
    }
    return $aresult; 
}

function createMissionsIDSet ()
{
    global $total_missions;
    
    if($total_missions)
    {
        global $missions;
        $set = '';
        foreach ($missions as $mission)
        {
            $sep = ($set) ? ',' : '(';
            $set .= $sep . $mission['date_tasks_mission_id'];
        }
        return  $set.')';
    }
    else
        return '(0)';
}

$subj_id = gri('subj', -1);
$medal_ord = gri('medal', -1);

if($subj_id>0 && $medal_ord>0)
{
$user_row = mysql_fetch_assoc(mq("SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
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
     LEFT JOIN (SELECT * FROM user_tracks WHERE user_id = {$user['user_id']} AND subject_id={$subj_id}) user_tracks USING (user_id)
WHERE user_id = {$user['user_id']}
"));

$medal_data = mysql_fetch_assoc(mq("SELECT profile_photo_id, medal_name, subject_name, missions_required, missions_required_total
FROM medals_subjects_totals, subjects, medals
WHERE
    medals_subjects_totals.subject_id = subjects.subject_id AND
    medals_subjects_totals.medal_ord = medals.medal_ord AND
    medals.medal_ord = {$medal_ord} AND
    subjects.subject_id = {$subj_id}
"));
$medal_data['missions_required'] = (int)$medal_data['missions_required'];
$medal_data['missions_required_total'] = (int)$medal_data['missions_required_total'];

$start_mission = $medal_data['missions_required_total'] - $medal_data['missions_required'];

if ($user_row['level'] && $user_row['track_id'])
{
    $missions_res = mq("
    SELECT 
        date_tasks_mission_id,
        start_date,
        mission_name,
        total_tasks
    FROM
        date_tasks_missions   
    LEFT JOIN (SELECT date_tasks_mission_id, COUNT(date_task_id) AS total_tasks FROM date_tasks GROUP BY date_tasks_mission_id) date_tasks USING (date_tasks_mission_id)
    WHERE
        school_type_id = {$user_row['school_type_id']} AND
        subject_id = {$subj_id} AND
        `level` = {$user_row['level']} AND
        track_id = {$user_row['track_id']}
    ORDER BY
        start_date, mission_number, date_tasks_mission_id
    LIMIT {$start_mission}, {$medal_data['missions_required']}
    ");
    $total_missions = mysql_num_rows ( $missions_res );
    $missions = dbResultToArray( $missions_res );
}
else
{
    $missions = null;
    $total_missions = 0;
}

if ($total_missions)
{
    $set = createMissionsIDSet ();
    
    $missions_execution = mysql_fetch_column(mq("
    SELECT 
        date_tasks_mission_marks.date_tasks_mission_id, 
        COUNT(DISTINCT date_tasks_marks.date_task_id) AS completed_tasks 
    FROM date_tasks_marks, date_tasks, date_tasks_missions, date_tasks_mission_marks
    WHERE
        date_tasks_marks.user_id = {$user['user_id']} AND
        date_tasks_marks.mark_inactive = 0 AND
        date_tasks_mission_marks.date_tasks_mission_id IN {$set} AND    
        date_tasks_mission_marks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND
        date_tasks_missions.date_tasks_mission_id = date_tasks.date_tasks_mission_id AND
        date_tasks.date_task_id = date_tasks_marks.date_task_id
    GROUP BY date_tasks_mission_marks.date_tasks_mission_id
    "));
}

$startdate = ($total_missions) ? $missions[0]['start_date'] : unixtojd();
$startdateh_arr = cal_from_jd($startdate, CAL_JEWISH);
?>
<?php include("includes/header.php"); ?>

<?php include("includes/slider.php"); ?>

<script type="text/javascript">
	$(document).ready(function(){	
		$("#slider_inside").easySlider({
			vertical: true,
			controlsBefore:	'<div class="button_box">',
			controlsAfter:	'</div>',
			prevId: 		'upBtn',
			nextId: 		'dnBtn'
			});
	});	
</script>


<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Missions</div>
            <div class="three_column padding_top">
              <div class="content">
                    <div class="slider_box">
                      <ul class="missions">
                            <li>
                                <div class="medalImage"><?=linkImgFile($medal_data['profile_photo_id'], 96, 100);?><span class="badge"><?=$medal_data['missions_required'];?></span></div>
                                <div class="slider_title"><?=$medal_data['subject_name'];?> - <?=$medal_data['medal_name'];?></div>
                                <div id="slider_inside" class="mission_boxes">
                                <ul>
                                    <li>
					<?php $f=100;?>
                                    
                                    <div class="mission empty"></div>
                                <?php
                                $completed = 0;
                                for ($n=0; $n<$medal_data['missions_required']; $n++)
                                {
                                    //TODO: Here must come calculations of mission results
                                    $success = (
                                            $n<$total_missions && 
                                            isset($missions_execution[$missions[$n]['date_tasks_mission_id']]) && 
                                            $missions[$n]['total_tasks']<=$missions_execution[$missions[$n]['date_tasks_mission_id']]
                                    ) ? true : false;
                                    
                                    //////////////////////////////////////////////////////
                                    $c_month = (($startdateh_arr['month']+$n)%13) ? ($startdateh_arr['month']+$n)%13 : 13;
                                    $c_year = $startdateh_arr['year']+((int)(($startdateh_arr['month']+$n)/13));
                                    $c_jd = jewishtojd ( $c_month , 1 , $c_year );
                                    
                                    $hdate_arr = dateToHebrewSplitNoGr($c_jd);
                                    $hdate = $hdate_arr[1].' '.$hdate_arr[2];
                                    
                                        //$hdate = $c_month.' / '.$c_year;
           
                                    
                                    if ($n<$total_missions)
                                    {
                                        $num = ($success) ? ++$completed : "&nbsp;";
                                        if ( isset($missions_execution[$missions[$n]['date_tasks_mission_id']]) )
                                        {                                        
                                            $badge_str = ($success) ? '<div class="check_on"></div>' : '<div class="check_off"></div>';
                                            $uncomplete = (int)((($missions[$n]['total_tasks']-$missions_execution[$missions[$n]['date_tasks_mission_id']])/$missions[$n]['total_tasks'])*100);
                                        }
                                        else
                                        {
                                            $badge_str = '';
                                            $uncomplete = 100;
                                        }
                                ?>
                                    
                                    <div class="mission">
                                        <div class="number"><?=$num;?></div>
                                        <div class="date">Week <?=$n*4+1;?>-<?=($n+1)*4;?></div>
                                        <div class="date"><?=$hdate;?></div>
                                        <div class="meter" style="background-position:<?=$uncomplete;?>% 0;"></div>
                                        <?=$badge_str;?>
                                        <a href="javascript:document.getElementById('mission<?=$missions[$n]['date_tasks_mission_id'];?>').submit();"></a>
                                        <form id="mission<?=$missions[$n]['date_tasks_mission_id'];?>" action="prof_medal_mission.php" method="post">
                                            <input type="hidden" name="hdate" value="<?=htmlspecialchars($hdate, ENT_QUOTES);?>">
                                            <input type="hidden" name="mission" value="<?=$missions[$n]['date_tasks_mission_id'];?>">
                                        </form>
                                    </div>
                                <?php
                                    }
                                    else
                                    {
                                ?>                                    
                                    <div class="mission inactive">
                                        <div class="number">&nbsp;</div>
                                        <div class="date">Week <?=$n*4+1;?>-<?=($n+1)*4;?></div>
                                        <div class="dateh">אדר תשעו</div>
                                    </div>
                                <?php
                                    }
                                }
                                ?>                                    
                                    <div class="mission button_back"><a href="prof_medals.php">Back to Medals</a></div>
                                </li>
                                    </ul>
                               </div>
                            </li>
                      </ul>
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
            <div class="footer_logo"></div>
            <div class="footer_logout"></div>
        </div>
    </div>
</body>
</html>
<?php
}
else
{
    header('Location: prof_medals.php');
    exit("<script>window.location = 'prof_medals.php';</script>");
}
?>