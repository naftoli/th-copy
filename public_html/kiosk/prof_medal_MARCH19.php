<a href="../">back to home</a>
<?php
exit; //I disabled the page, it's messing up the server
require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

$counted_ladders = array();
$current_mission = NULL;
$previous_mission = NULL;

$current_ladder = array();
$current_ladder['level'] = null;
$current_ladder['track_id'] = null;
$current_ladder['school_type_id'] = null;

$previous_ladder = array();

/*
 * in future i also will check in DB the current ladders
 */
function isLadderValidByDB($mission)
{
    global $ladder_changes;
    if(!$ladder_changes || !isset($ladder_changes[0]))
        return true;
    if($mission['start_date']<$ladder_changes[0]['start_date'])
        return true;
    foreach ($ladder_changes as $ladder_change)
    {
        return($mission['start_date'] >= $ladder_change['start_date'] &&
                        ($mission['level'] == $ladder_change['level'] && $mission['track_id'] == $ladder_change['track_id']));
    }
    return false;
}

/**
 * check if mission belong to right ladder(level/track)
 * @param $mission
 * @return boolean
 */
function isLadderCurrent($mission) {
    global $current_ladder;
	global $current_miss_num;
	global $missions;
	
    $current_date = unixtojd();
	
    if ($mission['mark_date']) {
	
        if($mission['start_date'] <= $current_date) {
            $current_ladder['level'] = $mission['level'];
            $current_ladder['track_id'] = $mission['track_id'];
			$current_ladder['school_type_id'] = $mission['school_type_id'];
        }
		
        return true;
    }
    elseif ($mission['tasks_marks'] && $mission['tasks_marks'] > 0) {
	
        if($mission['start_date'] > $current_date && !isMissionInLastLadder($mission))
             return false;
			 
        $current_ladder['level'] = $mission['level'];
        $current_ladder['track_id'] = $mission['track_id'];
		$current_ladder['school_type_id'] = $mission['school_type_id'];
		
        return true;
    }
    else {
	
        for($n=$current_miss_num; $n < $current_miss_num + 10 && $n < count($missions); $n++) {
		
            if ($missions[$n]['start_date'] != $mission['start_date'])
                break;
				
            if ($missions[$n]['mark_date'] || ($missions[$n]['tasks_marks'] && $missions[$n]['tasks_marks'] > 0))
                return false;
        }
		
        if(isLadderValidByDB($mission) && $mission['level'] == $current_ladder['level'] && $mission['track_id'] == $current_ladder['track_id'] && $mission['school_type_id'] == $current_ladder['school_type_id'])
            return true;
    }
	
    return false;
}


function dbResultToArray($result)
{
    $aresult = array();
    $n=0;
    while ($data = mysql_fetch_array($result, MYSQL_ASSOC))
    {
        $aresult[$n] = $data;
        $n++;
        //print_r($data);
    }
    return $aresult; 
}

function laddersToArray($result)
{
    $aresult = array();
    while ($data = mysql_fetch_array($result, MYSQL_ASSOC))
    {
        $aresult[$data['level']][$data['track_id']] = 1;
        //print_r($data);
    }
    return $aresult; 
}

function missionsToArray($result)
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


function isLadderValid($mission)
{
    global $counted_ladders;
    if(!isset($counted_ladders[$mission["level"]][$mission["track_id"]]))
    {
        //echo('level '.$mission["level"].' and track '.$mission["track_id"].' are not in the range <br>');
        return false;
    }
    return true;
}

function isMissionInLastLadder($mission)
{
    global $user_row;
    return($mission["level"] == $user_row["level"] && $mission["track_id"] == $user_row["track_id"]);
}

function isMissionRendered($a_mission)
{
    global $previous_mission, $current_mission;
    $previous_mission = $current_mission;
    $current_mission = $a_mission;
    return(isLadderValid($a_mission) && isLadderCurrent($a_mission));
}

$current_date = unixtojd();

$subj_id = gri('subj', -1);
$medal_ord = gri('medal', -1);
$birth_day = gri('birth', 0);
$last_medal = gri('last', 0);

if($last_medal == 1)
    $last_medal = true;
else     
    $last_medal = false;

if ($subj_id > 0 && $medal_ord > 0) {
    
$user_row = mysql_fetch_assoc(mq("SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, user_start_date,
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

$medal_data = mysql_fetch_assoc(mq("SELECT profile_photo_id, medal_name, subject_name, 
											missions_required, missions_required_total, 
											medals_subjects_totals.subject_id
FROM medals_subjects_totals, subjects, medals
WHERE
    medals_subjects_totals.subject_id = subjects.subject_id AND
    medals_subjects_totals.medal_ord = medals.medal_ord AND
    medals.medal_ord = {$medal_ord} AND
    subjects.subject_id = {$subj_id}
"));
$prev_medal_ord = $medal_ord-1;
$mission_num = 0;

$sqlSelect = "SELECT distinct level, track_id ";
$sqlFrom = " FROM date_tasks_missions, date_tasks_mission_marks ";
$sqlWhere = " WHERE ";
$sqlWhere = $sqlWhere . " date_tasks_missions.subject_id = " . $subj_id . " AND "; 
// GC March 18 2010 $sqlWhere = $sqlWhere . " school_type_id = " . $user_row['school_type_id']. " AND ";
$sqlWhere = $sqlWhere . " date_tasks_missions.subject_id = date_tasks_mission_marks.subject_id AND ";
$sqlWhere = $sqlWhere . " mark_date IS NOT NULL AND ";
$sqlWhere = $sqlWhere . " user_id = " . $user['user_id'] . " AND ";
$sqlWhere = $sqlWhere . " date_tasks_missions.date_tasks_mission_id=date_tasks_mission_marks.date_tasks_mission_id ";
$sqlOrderBy = " ORDER BY level, track_id ";
$sqlStatement = $sqlSelect . $sqlFrom . $sqlWhere . $sqlOrderBy;
$counted_ladders_data = mq($sqlStatement);
$counted_ladders = laddersToArray($counted_ladders_data);

$medal_data['missions_required'] = (int)$medal_data['missions_required'];
$medal_data['missions_required_total'] = (int)$medal_data['missions_required_total'];

$start_mission = $medal_data['missions_required_total'] - $medal_data['missions_required'];
$missions = null;

if ($user_row['level'] && $user_row['track_id']) {
	global $sqlStatement2;
	global $total_rows2;
	
	$sqlSelect = "SELECT 	distinct
							date_tasks_mission_id,       
							start_date,
							end_date,
							date_tasks_missions.mission_name,
							total_tasks,
							mission_number,
							mark_date,
							level,
							track_id,
							tasks_marks, school_type_id ";							
	$sqlFrom = " FROM date_tasks_missions ";	
	$sqlJoin = " LEFT JOIN (SELECT mark_date, date_tasks_mission_id FROM date_tasks_mission_marks WHERE user_id = {$user['user_id']}  GROUP BY date_tasks_mission_id) date_tasks_mission_marks USING (date_tasks_mission_id) ";
	$sqlJoin = $sqlJoin . " LEFT JOIN (SELECT date_tasks.date_tasks_mission_id, COUNT(date_tasks_marks.mark_date) AS tasks_marks, COUNT(date_tasks.date_task_id) AS total_tasks FROM date_tasks, date_tasks_marks WHERE date_tasks.date_task_id=date_tasks_marks.date_task_id and date_tasks_marks.user_id={$user['user_id']} GROUP BY date_tasks_mission_id) date_tasks USING (date_tasks_mission_id) ";	
	// GC March 18 2010 $sqlWhere = " WHERE school_type_id = {$user_row['school_type_id']} AND date_tasks_missions.subject_id = {$subj_id} ";
	$sqlWhere = " WHERE date_tasks_missions.subject_id = {$subj_id} ";	
	$sqlOrderBy = " ORDER BY start_date ASC, level DESC, track_id DESC ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlOrderBy;	
    $missions_res = mq($sqlStatement);
	
    $total_missions = mysql_num_rows($missions_res);
    $missions = dbResultToArray($missions_res);
}
else {
    $total_missions = 0;
}

$sqlSelect = "SELECT distinct date_tasks_mission_id, COUNT(mark_date) as total_comp, level, track_id ";
$sqlFrom = " FROM date_tasks_missions ";
$sqlJoin = " LEFT JOIN (SELECT mark_date, date_tasks_mission_id FROM date_tasks_mission_marks WHERE user_id = " . $user['user_id'] . " GROUP BY date_tasks_mission_id) date_tasks_mission_marks USING (date_tasks_mission_id) ";
$sqlJoin = $sqlJoin . " LEFT JOIN (SELECT date_tasks.date_tasks_mission_id, COUNT(date_tasks_marks.mark_date) AS tasks_marks, COUNT(date_tasks.date_task_id) AS total_tasks FROM date_tasks, date_tasks_marks WHERE date_tasks.date_task_id=date_tasks_marks.date_task_id and date_tasks_marks.user_id= " . $user['user_id'] . " GROUP BY date_tasks_mission_id) date_tasks USING (date_tasks_mission_id) ";
$sqlWhere = " WHERE ";
// GC March 18 2010 $sqlWhere = $sqlWhere . " school_type_id = " . $user_row['school_type_id'] . " AND ";
$sqlWhere = $sqlWhere . " date_tasks_missions.subject_id = " . $subj_id . " ";
$sqlGroupBy = " GROUP BY date_tasks_mission_id ";
$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy;
$missions_completed_data = mysql_fetch_assoc(mq($sqlStatement));
$total_comp_missions = $missions_completed_data['total_comp'];

//echo("<div style='background-color:white;'>total_comp_missions={$total_comp_missions}</div>");

$total_data = mysql_fetch_assoc(mq("SELECT SUM(missions_required) as total FROM medals_subjects_totals WHERE subject_id = " . $subj_id . " ")); 
$total = (int)($total_data['total']);

$start_date = max($current_date - 75, intval($user_row['user_start_date']));

$current_miss_num=0;
$num_rendered_missions_in_prev_medals=0; 
$required_missions_for_prev_medals = 0;
$total_miss_num = 0;
$failed = array();
$afetr_last = false;

if($medal_ord > 1)
{
    $prev_medal_data = mysql_fetch_assoc(mq("SELECT SUM(missions_required) as number
                                        FROM medals_subjects_totals, subjects, medals
                                        WHERE
                                            medals_subjects_totals.subject_id = subjects.subject_id AND
                                            medals_subjects_totals.medal_ord = medals.medal_ord AND
                                            medals.medal_ord < {$medal_ord} AND
                                            subjects.subject_id = {$subj_id}
                                        "));
    $required_missions_for_prev_medals = $prev_medal_data["number"]; 
    $start_count=false;               
    $cur_level = 0;
    $cur_track = 0;    
    $after_all_missions = true;
    $mission = NULL;
    //echo('befor while:'.time().'<br/>');             
    while ($num_rendered_missions_in_prev_medals < $required_missions_for_prev_medals) 
    { 
        if(isset($missions[$current_miss_num]))
            $mission = $missions[$current_miss_num];
        if(!isset($missions[$current_miss_num]))
        {
            break;
            $num_rendered_missions_in_prev_medals++;//current count of not failed missions
    	    $total_miss_num++;                       //current count of all counted missions
        }
        else
        {
            if($missions[$current_miss_num]['end_date'] >= $birth_day)
                break;
            if(!isMissionRendered($mission))
            {
                $current_miss_num++;
                continue;   
            }         
        	$total_miss_num++;                       //current count of all counted missions
            if($mission['mark_date'])//user has completed mission
            {
                //echo("<div style='background-color:white;'>0 compl={$mission['mission_name']}; num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");
        	    $num_rendered_missions_in_prev_medals++;//current count of not failed missions
            }
            elseif($mission['end_date'] < $current_date)//this is old uncompleted mission
            {
                //echo("<div style='background-color:white;'>1 failed={$mission['mission_name']}; num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");
                $failed[] = $mission;  //failed missions for faded missions rendering
            }
            elseif($mission['end_date'] >= $current_date)//this is old uncompleted mission
            {
                if(!isMissionInLastLadder($mission) || ($total_comp_missions > $num_rendered_missions_in_prev_medals))
                {//this is current ladder (if time was over - mission failed)           
                    //echo("<div style='background-color:white;'>2 failed={$mission['mission_name']}; num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");                     
                    $failed[] = $mission;  //failed missions for faded missions rendering
                }
                else
                {
                    //echo("<div style='background-color:white;'>3 num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");                     
                    $num_rendered_missions_in_prev_medals++;//current count of not failed missions
                }
            }
        }
    	
        
        $current_miss_num++;
    }
    if($num_rendered_missions_in_prev_medals < $required_missions_for_prev_medals && count($failed) > 0)
    {
        for($i=0; $i<($required_missions_for_prev_medals - $num_rendered_missions_in_prev_medals); $i++)
        {
            array_pop($failed);
        }
    }
    //echo('after while '.time().'<br/>');
}
//echo("<div style='background-color:white;'>num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");
$startdate = ($total_missions) ? $missions[0]['start_date'] : unixtojd();
$startdateh_arr = cal_from_jd($startdate, CAL_JEWISH);

$title = "Missions";
include("includes/header.php");
?>

<script>
$(document).ready(function() {
	var itemHeight = 88;
	var itemCol = 7
	var currentTop = 0;
	var containerHeight = Math.ceil($("#slider_inside > div > div").length / itemCol) * itemHeight;
	$("a#button_up").click(function () {
		if (Math.abs(currentTop) > 0) {
			$("#slider_inside > div").animate({"top":currentTop + itemHeight},{queue:false});
			currentTop += itemHeight;
		} else {
			$("#slider_inside > div").animate({"top":currentTop + (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
		}
    });
	$("a#button_dn").click(function () {
		if (Math.abs(currentTop) < (containerHeight-itemHeight)) {
			$("#slider_inside > div").stop().animate({"top":currentTop - itemHeight},{queue:false});
			currentTop -= itemHeight;
		} else {
			$("#slider_inside > div").stop().animate({"top":currentTop - (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
		}
    });	
 });
</script>


<body class="blue">

    <div id="wrapper">
	
        <div id="header">
			<?php include("includes/topbar.php"); ?>
		</div>
		
        <div id="main">
            <div id="page_title">
				Missions
			</div>
			
            <div class="three_column padding_top">
			
				<div class="content">
				
                    <div class="slider_box">
					
						<ul class="missions">
						
							<li>
							
								<div class="slider_title">
									<?=$medal_data['subject_name'].' - '.$medal_data['medal_name'];?>
								</div>
															
								<!-- GC March 9 2010 -->
                                <div class="mission_side">
									<div class="medalImage" style='background: transparent url(<?='/file_view.php?id='.$medal_data['profile_photo_id']?>);'>
										<span class="badge"><?=$medal_data['missions_required'];?></span>
									</div>
									<a id="button_up">Up</a>
									<a id="button_dn">Down</a>
								</div>
								<!-- GC March 9 2010 -->
								
                                <div id="slider_inside" class="mission_boxes">
								
                                    <div id="missions_container">
										<?php
										
											$completed = 0;
											$completed_missions_num = 0;
											$miss_in_li = 0;
											$present_number = 0;                              
											$started = false;
											$last_page = false;                                									
											
											while ($completed_missions_num < $medal_data['missions_required']) {
											
												echo "<input type='hidden' name='missions' value='completed_missions_num:" . $completed_missions_num . " medal_data missions_required:" . $medal_data['missions_required'] . "'>\n";
												
												if (isset($missions[$current_miss_num]))
												
													if(!isMissionRendered($missions[$current_miss_num])) {
														$current_miss_num++;
														continue;
													}
													
													$present_number_str = '&nbsp;'; 
													
													if ($total_miss_num >= $total) {   
														if($miss_in_li > 0 )                                    
															$last_page = true;
														break;
													}
													
													if (isset($missions[$current_miss_num]) && $missions[$current_miss_num]['end_date'] >= $birth_day) {   
														$last_page = true;
														break;
													}
													
													if (!isset($missions[$current_miss_num])) {
														break;
														$miss_in_li++;
														$present_number++;
														$completed_missions_num++;
														$present_number_str = '#'.$present_number;                                        
                                        ?>
										
                                        <div class="mission">
                                            <div class="number"><?=$present_number_str?></div>                                           
                                            <div class="date">&nbsp;</div>                            
                                            <div class="meter" style="background-position:100% 0;"></div>
                                        </div> 
										
										<?
														$total_miss_num++;
													} //if (!isset($missions[$current_miss_num]))													
													elseif ($missions[$current_miss_num]['end_date'] <= $current_date) { 
														$done = isset($missions[$current_miss_num]['mark_date']);
														$date = dateToHebrewSplit($missions[$current_miss_num]['start_date']);
														
														if ($done) {                                        
															$badge_str = '<div class="check_on"></div>';
															$uncomplete = 0;
															$completed_missions_num++;
															$present_number++;
															$present_number_str = '#' . $present_number;
															$completed++;
														}
														else {                                                                          
															$badge_str = '<div class="check_off"></div>';
															$uncomplete = 100;
															$failed[] = $missions[$current_miss_num];
														}
														
														$total_miss_num++;
														$miss_in_li++;
                                        ?>
                                        
                                        <div class="mission">
											<div class="number"><?=$present_number_str?></div>
                                        <?
														$mission_name = '';
														
														if(false) { //$subj_id!=1 && $subj_id!=40)
															$mission_name='Week '.($current_miss_num*4+1).'-'.(($current_miss_num+1)*4);
														}
														else {
															$mission_name=es($missions[$current_miss_num]['mission_name']);
														}
                                        ?>
                                        <!--<div class="date"><?=$missions[$current_miss_num]['end_date']?></div><?=($missions[$current_miss_num]['level'].$missions[$current_miss_num]['track_id'])?></div>-->
											<div class="date"><?=$mission_name?></div>                                         
											<div class="date"><?=es($date[2])?></div> 
											<div class="meter" style="background-position:<?=$uncomplete;?>% 0;"></div>
											
                                        <?=$badge_str;?>
										
											<a href="javascript:document.getElementById('mission<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>').submit();"></a>
											<form id="mission<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>" action="prof_medal_mission.php" method="post">
												<input type="hidden" name="mission" value="<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>">
												<input type="hidden" name="mission_name" value="<?=$mission_name;?>">
											</form>
                                        </div>
                                        <?
													}
													// PAST MISSIONS
													elseif ($missions[$current_miss_num]['start_date'] <= $current_date && $missions[$current_miss_num]['end_date'] > $current_date) { 
														$done = isset($missions[$current_miss_num]['mark_date']);
														$date = dateToHebrewSplit($missions[$current_miss_num]['start_date']);
														
														if ($done) {                                        
															$badge_str = '<div class="check_on"></div>';
															$uncomplete = 0;
															$completed_missions_num++;
															$present_number++;
															$present_number_str = '#'.$present_number;
															$completed++;
														}
														else {
															$badge_str = '';
															$uncomplete = 100;
															$present_number++;
															$completed_missions_num++;
															$present_number_str = '#'.$present_number;                                          
														}
														
														$total_miss_num++; 
														$miss_in_li++;
                                        ?>              
										
                                        <div class="mission">                                        
                                        <div class="number"><?=$present_number_str?></div>
                                        <!--<div class="date"><?=($missions[$current_miss_num]['level'].$missions[$current_miss_num]['track_id'])?></div>    
                                        -->
										<?
														if(false)//$subj_id!=1 && $subj_id!=40)
														{
                                        ?>
										<div class="date">Week <?=$current_miss_num*4+1;?>-<?=($current_miss_num+1)*4;?></div>
										<?
														}
														else
														{
                                        ?>
										<div class="date"><?=es($missions[$current_miss_num]['mission_name'])?></div><?
														}
                                        ?>
                                        
                                        <div class="date"><?=es($date[2])?></div>
                                        <div class="meter" style="background-position:<?=$uncomplete;?>% 0;"></div>
										
                                        <?=$badge_str;?>
                                        
										<a href="javascript:document.getElementById('mission<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>').submit();"></a>
                                        <form id="mission<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>" action="prof_medal_mission.php" method="post">
                                            <input type="hidden" name="mission" value="<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>">
                                        </form>
                                        </div>
                                        <?
													}
													// FUTURE MISSIONS
													elseif ($missions[$current_miss_num]['start_date'] > $current_date) 
													{
														$date = dateToHebrewSplit($missions[$current_miss_num]['start_date']);
										
														if($missions[$current_miss_num]['level']!=$user_row['level'] || $missions[$current_miss_num]['track_id']!=$user_row['track_id']) {
															$current_miss_num++;
														continue;
													}
										
													$done = isset($missions[$current_miss_num]['mark_date']);
													
													if ($done) 
													{                                        
														$badge_str = '<div class="check_on"></div>';
														$uncomplete = 0;
														$completed_missions_num++;
														$present_number++;
														$present_number_str = '#' . $present_number;
														$completed++;
													}
													else 
													{
														if ($missions[$current_miss_num]['level']!=$user_row['level'] || $missions[$current_miss_num]['track_id']!=$user_row['track_id'])
															$badge_str = '';
															
														$uncomplete = 100;
														$present_number++;
														$completed_missions_num++;
														$present_number_str = '#' . $present_number;                                            
													} 
										
													$total_miss_num++;
													$miss_in_li++;
                                        ?>
										
                                        <div class="mission">
											<div class="number">
												<?=$present_number_str?>
											</div>
											
											<!--<div class="date"><?//=($missions[$current_miss_num]['level'].$missions[$current_miss_num]['track_id'])?></div> 
											-->
										<?
										
										// GC March 10 2010 
										if ($subj_id != 12 && $subj_id != 40) {
										
											if (false) //$subj_id!=1 && $subj_id!=40) 
											{
												?><div class="date">Week <?=$current_miss_num*4+1;?>-<?=($current_miss_num+1)*4;?></div><?
											}
											else
											{
												?><div class="date"><?=es($missions[$current_miss_num]['mission_name'])?></div><?
											}
											
										}
										// GC March 10 2010 
										
                                        ?>
                                        
											<!-- GC March 10 2010 -->
											<? if ($subj_id != 12 && $subj_id != 40) { ?>
											<div class="date">
												<?=es($date[2])?>
											</div>
											
											<div class="meter" style="background-position:100% 0;">
											</div>
											
											<a href="javascript:document.getElementById('mission<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>').submit();"></a>
											
											<form id="mission<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>" action="prof_medal_mission.php" method="post">
												<input type="hidden" name="mission" value="<?=$missions[$current_miss_num]['date_tasks_mission_id'];?>">
											</form>
											<? } ?>
											<!-- GC March 10 2010 -->
											
                                        </div><!-- mission -->
                                        <?
                                    }
                                    $current_miss_num++;
                                }
                                $inactive_mission_num = $completed_missions_num;
                                if($last_medal || $last_page || ($completed_missions_num>=$medal_data['missions_required'] && $total_miss_num >= $total))
                                {
                                    $failed = array_reverse($failed);
                                    //echo count($failed);
                                    foreach ($failed as $failure)
                                    {
                                        if($inactive_mission_num == $medal_data['missions_required'])
                                            break;
                                        $date = dateToHebrewSplit($failure['start_date']);
                                        $miss_in_li++;
                                        $present_number++;
                                        $present_number_str = '#'.$present_number;
                                        ?>                                    
                                        <div class="mission inactive">
                                        <div class="number"><?=$present_number_str?></div>
                                        	<?if(false){//$subj_id!=1 && $subj_id!=40){?>
                                            <div class="date">Week <?=$inactive_mission_num*4+1;?>-<?=($inactive_mission_num+1)*4;?></div>
                                            <?}else{?>
                                            <div class="date"><?=es($failure['mission_name'])?></div>
                                            <?}?>
                                            <div class="check_off"></div>
                                            <div class="date"><?=es($date[2])?></div>
                                        </div>
                                        <?
                                        $inactive_mission_num++;
                                    }
                                }
                                        
                                ?>                                    
                                    <div class="mission button_back">
										<a href="prof_medals.php?subject_id=<?=$subj_id;?>">Back to Medals</a></div>
									</div>
								</div>
								
							</li>
							
						</ul>
						
                    </div>
					
				</div>
				
            </div> <!--three_column padding_top -->
			
        </div> <!-- main -->
		
        <div id="footer">
            <div class="footer_logo"></div>
            <div class="footer_logout"></div>
        </div> <!-- footer -->
		
    </div> <!-- wrapper -->
	
	</body>

</html>
<?php
}
else
{
		$headerLocation = "prof_medals.php?subject_id=" . $subj_id; 
		header($headerLocation);
		exit("<script>window.location = " . $headerLocation . ";</script>");
}
?>
