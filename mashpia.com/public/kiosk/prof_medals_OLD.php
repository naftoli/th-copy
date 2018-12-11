<?php 
include_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

session_start();

$counted_ladders = array();
$current_mission = NULL;
$previous_mission = NULL;
$current_ladder = array();
$current_ladder['level']=null;
$current_ladder['track_id']=null;
$previous_ladder = array();
define('MIVZAIM', 12);
define('TANIYA', 27);
define('PAGRA', 40);

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

/**********************************************************/
/* check if mission belongs to right ladder (level/track) */
/* @parameters: $mission                                  */
/* @return: boolean                                       */
/**********************************************************/
function isLadderCurrent($mission) {
    global $current_ladder;
	global $current_miss_num;
	global $missions;
	
    $current_date = unixtojd();
	
    if ($mission['mark_date']) {
        if ($mission['start_date'] <= $current_date) {
            $current_ladder['level'] = $mission['level'];
            $current_ladder['track_id'] = $mission['track_id'];
        }
		
        return true;
    }
    elseif ($mission['tasks_marks'] && $mission['tasks_marks'] > 0) {
        if($mission['start_date'] > $current_date && !isMissionInLastLadder($mission))
             return false;
			 
        $current_ladder['level'] = $mission['level'];
        $current_ladder['track_id'] = $mission['track_id'];
		
        return true;
    }
    else {
        for($n = $current_miss_num; $n < $current_miss_num + 20 && $n < count($missions); $n++) {
            if ($missions[$n]['start_date'] != $mission['start_date'])
                break;
				
            if ($missions[$n]['mark_date'] || ($missions[$n]['tasks_marks'] && $missions[$n]['tasks_marks'] > 0))
                return false;
        }
		
        if (isLadderValidByDB($mission) && $mission['level'] == $current_ladder['level'] && $mission['track_id'] == $current_ladder['track_id'])
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

function dbMissionsToArray($result, $total)
{
    $aresults = array();
    $missions = array();
    $n = 0;
    $y = 0;
    
    while ($data = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $aresults[$n] = $data;
        $n++;
        //print_r($data);
    }
	
    $z = $aresults[0]["missions_required"];
	
    for ($i=0;$i<$total;$i++) {     
        $missions[$i] = $aresults[$y]["medal_ord"];
		
        if ($i+1 == $z && $i+1<$total) {
            $y++;
            $z += $aresults[$y]["missions_required"];
        }
        
    }
    return $missions; 
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


function isLadderValid($mission) {
    global $counted_ladders;
	
    if(!isset($counted_ladders[$mission["level"]][$mission["track_id"]])) {
        return false;
    }

    return true;
}

function isMissionInLastLadder($mission) {
    global $user_row;
    return($mission["level"] == $user_row["level"] && $mission["track_id"] == $user_row["track_id"]);
}

function isMissionRendered($a_mission)
{
    global $previous_mission;
	global $current_mission;
	
    $previous_mission = $current_mission;
    $current_mission = $a_mission;
	
    return(isLadderValid($a_mission) && isLadderCurrent($a_mission));
}


$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state,dob,dob_he_offset, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
       school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, school_id,
       rank_name, rank_image_id, rank_color 
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));
function getHebrewYear($jdDate)
{
    //$gregorianMonth = date(n);
    //$gregorianDay = date(j);
    //$gregorianYear = date(Y);
    //$jdDate = gregoriantojd($gregorianMonth,$gregorianDay,$gregorianYear);
    //$hebrewMonthName = jdmonthname($jdDate,4);
    //$hebrewDate = jdtojewish($jdDate);
    //list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
    $startdateh_arr = cal_from_jd($jdDate, CAL_JEWISH);
    return $startdateh_arr["year"];
}

if(!isset($user_row['dob']) || $user_row['dob']==NULL || $user_row['dob']=='' || $user_row['dob']=='NULL')
{
    $title = "Medals";
    include("includes/header.php");
    ?>
    <body class="blue">
    	<div id="wrapper">
        	<div id="header">
              <?php include("includes/topbar.php"); ?>
      		</div>
        	<div id="main">
            	<div id="page_title">Medals</div>
            	<div class="three_column padding_top">
              		<div class="content">
                    	<div id="slider">
                    		<ul>
                    			<li style="height:100%;vertical-align:middle;">
    								<P style="width:100%;vertical-align:middle;height:100%;text-align:center;margin-top:100px;"><?=T_('Please update your date of birth for access to this page.')?></P>
    							</li>
    						</ul>
    					</div>
    				</div>
    			</div>
    		</div>
    	</div>
    </body>
    </html>
    <?
    exit();
}
$age = calcAge(dateToJD($user_row['dob'])+$user_row['dob_he_offset']);
//echo('Age = '.$age);
$mizva_age = ($user_row['gender']=="M"?13:12);
$bar_mizva_year = getHebrewYear(dateToJD($user_row['dob'])+$user_row['dob_he_offset']) + $mizva_age;
$birth_date = jewishtojd ( 1, 1, $bar_mizva_year+1 );

//echo $birth_date;
function getMedalSum($subj_id)
{
    //if($subj_id == MIVZAIM || $subj_id == TANIYA || $subj_id == PAGRA)
    if($subj_id == TANIYA)
        return 10;
    global $user_row,$user,$counted_ladders,$birth_date;
    $current_date = unixtojd();
    $current_miss_date = 0;
	
	$sqlStatement = "SELECT SUM(missions_required) as total FROM medals_subjects_totals WHERE subject_id = " . $subj_id . " ";
    $total_data = mysql_fetch_assoc(mq($sqlStatement)); 
    $total = (int)($total_data['total']);
	
    $missions_res = mq("SELECT missions_required, medals.medal_ord as medal_ord
                                        FROM medals_subjects_totals, subjects, medals
                                        WHERE
                                            medals_subjects_totals.subject_id = subjects.subject_id AND
                                            medals_subjects_totals.medal_ord = medals.medal_ord AND subjects.subject_id = {$subj_id} 
                                        order by medal_ord");
										
    $medalsMissions = dbMissionsToArray($missions_res, $total);
	
    $prev_medal_ord = 0;
    $mission_num = 0;
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
    
    $counted_ladders_data = mq("
        SELECT distinct
            level,
            track_id
        FROM
            date_tasks_missions, date_tasks_mission_marks  
        WHERE
            school_type_id = {$user_row['school_type_id']} AND
            date_tasks_missions.subject_id = {$subj_id} AND 
            date_tasks_missions.subject_id = date_tasks_mission_marks.subject_id AND 
            mark_date IS NOT NULL AND 
            user_id = {$user_row['user_id']} AND 
            date_tasks_missions.date_tasks_mission_id=date_tasks_mission_marks.date_tasks_mission_id ". //AND //level = {$user_row['level']} AND //track_id = {$user_row['track_id']} 
        "ORDER BY
            level, track_id");
    $counted_ladders = laddersToArray( $counted_ladders_data );
		
    $missions = null;
    $missions_res = mq("
    SELECT distinct
        date_tasks_mission_id,       
        start_date,
        end_date,
        date_tasks_missions.mission_name,
        total_tasks,
        mission_number,
        mark_date,
        level,
        track_id,
        tasks_marks
    FROM
        date_tasks_missions  
	LEFT JOIN (SELECT mark_date, date_tasks_mission_id FROM date_tasks_mission_marks WHERE user_id = {$user_row['user_id']}  GROUP BY date_tasks_mission_id) date_tasks_mission_marks USING (date_tasks_mission_id) 
    LEFT JOIN (SELECT date_tasks.date_tasks_mission_id, COUNT(date_tasks_marks.mark_date) AS tasks_marks, COUNT(date_tasks.date_task_id) AS total_tasks FROM date_tasks, date_tasks_marks WHERE date_tasks.date_task_id=date_tasks_marks.date_task_id and date_tasks_marks.user_id={$user_row['user_id']} GROUP BY date_tasks_mission_id) date_tasks USING (date_tasks_mission_id)
    WHERE
        school_type_id = {$user_row['school_type_id']} AND
        date_tasks_missions.subject_id = {$subj_id} ". //AND //level = {$user_row['level']} AND //track_id = {$user_row['track_id']} 
    "ORDER BY
        start_date ASC, level DESC, track_id DESC ");
    //LIMIT {$start_mission}, {$medal_data['missions_required']}");
        //school_type_id = 2 AND date_tasks_missions.subject_id = 1 AND `level` = 9 AND track_id = 7 //user 38 has earned mission 591 but in track 6
    $total_missions = mysql_num_rows ( $missions_res );
    $missions = dbResultToArray( $missions_res );

    $missions_completed_data = mysql_fetch_assoc(mq("
    SELECT distinct
        date_tasks_mission_id,       
        COUNT(mark_date) as total_comp,
        level,
        track_id
    FROM
        date_tasks_missions  
	LEFT JOIN (SELECT mark_date, date_tasks_mission_id FROM date_tasks_mission_marks WHERE user_id = {$user_row['user_id']}  GROUP BY date_tasks_mission_id) date_tasks_mission_marks USING (date_tasks_mission_id) 
    LEFT JOIN (SELECT date_tasks.date_tasks_mission_id, COUNT(date_tasks_marks.mark_date) AS tasks_marks, COUNT(date_tasks.date_task_id) AS total_tasks FROM date_tasks, date_tasks_marks WHERE date_tasks.date_task_id=date_tasks_marks.date_task_id and date_tasks_marks.user_id={$user_row['user_id']} GROUP BY date_tasks_mission_id) date_tasks USING (date_tasks_mission_id)
    WHERE
        school_type_id = {$user_row['school_type_id']} AND
        date_tasks_missions.subject_id = {$subj_id} GROUP BY date_tasks_mission_id"));
    $total_comp_missions = $missions_completed_data['total_comp'];
    //echo("<div style='background-color:white;'>total_comp_missions={$total_comp_missions}</div>");
    
    
    $start_date = max($current_date - 75, intval($user_row['user_start_date']));
    
    $current_miss_num=0;
    $num_rendered_missions=0; 
    $required_missions_for_prev_medals = 0;
    $total_miss_num = 0;
    $failed = array();
    $afetr_last = false;
    //$required_missions_for_prev_medals = $prev_medal_data["number"]; 
    $start_count=false;               
    $cur_level = 0;
    $cur_track = 0;    
    $after_all_missions = true;
    $mission = NULL;           
    while ($num_rendered_missions < $total) 
    {  

        if(!isset($missions[$current_miss_num]))
        {
            //echo ($num_rendered_missions.' - '.$current_miss_num.' - '.$medalsMissions[$num_rendered_missions].' ');
            //echo ($medalsMissions[$num_rendered_missions]);
            return ($medalsMissions[$num_rendered_missions]);
        }
        else
        {
            $mission = $missions[$current_miss_num];           
            if(!isMissionRendered($mission))
            {
                $current_miss_num++;
                continue;   
            }    
            
        	$total_miss_num++;                       //current count of all counted missions
            if($mission['mark_date'])//user has completed mission
            {
                //echo("<div style='background-color:white;'>0 compl={$mission['mission_name']}; num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");
        	    $num_rendered_missions++;//current count of not failed missions
            }
            elseif($mission['end_date'] < $current_date)//this is old uncompleted mission
            {
                //echo("<div style='background-color:white;'>1 failed={$mission['mission_name']}; num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");
                $failed[] = $mission;  //failed missions for faded missions rendering
                $num_rendered_missions++;
            }
            elseif($mission['end_date'] >= $current_date)//this is old uncompleted mission
            {
                if(!isMissionInLastLadder($mission) || ($total_comp_missions > $num_rendered_missions))
                {//this is current ladder (if time was over - mission failed)           
                    //echo("<div style='background-color:white;'>2 failed={$mission['mission_name']}; num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");                     
                    $failed[] = $mission;  //failed missions for faded missions rendering
                    $num_rendered_missions++;
                }
                else
                {
                    //echo("<div style='background-color:white;'>3 num_completed_missions_in_prev_medals={$num_rendered_missions_in_prev_medals}; total_miss_num={$total_miss_num}; current_miss_num=$current_miss_num;</div>");                     
                    $num_rendered_missions++;//current count of not failed missions
                }
            }  						
			
            // GC March 15 1010 if($mission['end_date'] >= $birth_date || $num_rendered_missions == $total)
			if ($mission['end_date'] >= $birth_date) {
                //echo ($medalsMissions[$num_rendered_missions].' mission end date = '.$mission['end_date'].' birth_date = '.$birth_date);
                return ($medalsMissions[$num_rendered_missions]);
            }
        }
    	$current_miss_num++;
    }
}


function build3dArray($result)
{
    $aresult = array();
    $n=0;
    $prev_subj_id = -1;
    $current_subj = NULL;
    while ($data = mysql_fetch_array($result, MYSQL_ASSOC))
    {
        if($data['subject_id'] != $prev_subj_id)
        {
            $n=0;
            if($current_subj != NULL)
            {
                $aresult[$current_subj[0]['subject_id']]=$current_subj;
            }
            $current_subj = array();
            $prev_subj_id = $data['subject_id'];
        }
        $current_subj[$n] = $data;
        $n++;
    }
    $aresult[$current_subj[0]['subject_id']]=$current_subj;
    return $aresult; 
}

function userMedalsToSearchIndex($result)
{
    $aresult = array();
    while ($data = mysql_fetch_array($result, MYSQL_ASSOC))
    {
        $aresult[$data['subject_id']][$data['medal_ord']] = true;
    }
    return $aresult; 
}

function subjectsToSearchIndex($result)
{
    $aresult = array();
    while ($data = mysql_fetch_array($result, MYSQL_ASSOC))
    {
        $aresult[] = $data['subject_id'];
    }
    return $aresult; 
}

$user_medals_result = mq("
SELECT * 
FROM medal_marks
WHERE user_id = {$user['user_id']}
ORDER BY subject_id, medal_ord
");

$subjects_result = mq("SELECT subjects.subject_id, subject_name, subject_type, subjects.subject_black_image_id 
FROM subjects 
JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = {$user_row['school_id']}) 
WHERE subject_type NOT IN ('school_points', 'home_points') 
ORDER BY subject_id");

$right_subjects = subjectsToSearchIndex($subjects_result);

$user_medals = userMedalsToSearchIndex($user_medals_result);

$sqlSelect = "SELECT medals_subjects.subject_id, medals_subjects.medal_ord, profile_photo_id, medal_name, subject_name, subject_gold_image_id ";
$sqlFrom = " FROM medals_subjects, subjects, medals ";
$sqlWhere = " WHERE ";
$subject_id = gri('subject_id', -1);
//if ($subject_id > 0) 
//	$sqlWhere = $sqlWhere . " medals_subjects.subject_id = " . $subject_id . " AND subjects.subject_id = " . $subject_id . " AND ";
//else 
$sqlWhere = $sqlWhere . " medals_subjects.subject_id = subjects.subject_id AND ";
$sqlWhere = $sqlWhere . " medals_subjects.medal_ord = medals.medal_ord ";
$sqlOrderBy = " ORDER BY subject_id, medal_ord ";
$sqlStatement = $sqlSelect . $sqlFrom . $sqlWhere . $sqlOrderBy;
$all_medals_result = mq($sqlStatement);
$num_of_medals = mysql_num_rows($all_medals_result);
$subjects_medals = build3dArray($all_medals_result);

//$all_medals_result = mq("
//SELECT medals_subjects.subject_id, medals_subjects.medal_ord, profile_photo_id, medal_name, subject_name, subject_gold_image_id
//FROM medals_subjects, subjects, medals
//WHERE
 //   medals_subjects.subject_id = subjects.subject_id AND
 //   medals_subjects.medal_ord = medals.medal_ord
//ORDER BY subject_id, medal_ord
//");

//$all_medals = dbResultToArray($all_medals_result);
//$subjects_medals = build3dArray($all_medals_result);

$title = "Medals";
include("includes/header.php");
?>

	<body class="blue">
	
		<div id="wrapper">
		
			<div id="header">
				<?php include("includes/topbar.php"); ?>
			</div>
			
			<div id="main">
			
				<div id="page_title">
					Medals
				</div>
				
				<div class="three_column padding_top">
				
					<div class="content">
					
						<div id="slider">
						
							<ul>
<?                    
	foreach ($subjects_medals as $all_medals) {
		$subjectId = $all_medals[0]['subject_id'];
		
		if (!in_array($subjectId,$right_subjects))
			continue;
                        ?>
								<li>
									<div class="slider_title"><?=$all_medals[0]["subject_name"];?></div>
									<div class="medalImage"><?=linkImgFile($all_medals[0]['subject_gold_image_id'], 200, 210);?></div>
									<div class="medals"><?
		$medals_num = 0;
		$medal_sum = getMedalSum($subjectId);

		foreach ($all_medals as $medal) { 

			if($medal['medal_ord']>$medal_sum) { 
				break;
			}
			elseif((isset($user_medals[$medal['subject_id']][$medal['medal_ord']]) && $user_medals[$medal['subject_id']][$medal['medal_ord']])) {
                                
				if($subjectId == TANIYA) {
				?>
									<div class="active"><div class="check_on"></div><?=linkImgFile($medal['profile_photo_id'], 96, 100);?></div>
				<?
				}
				else {
				?>
									<div class="active">
										<div class="check_on"></div>
										<a href="prof_medal.php?subj=<?=$medal['subject_id'];?>&medal=<?=$medal['medal_ord'];?>&birth=<?=$birth_date;?>&last=<?=($medal['medal_ord']==$medal_sum);?>"><?=linkImgFile($medal['profile_photo_id'], 96, 100);?></a>
									</div>
				<?}
			}
			else {

				if($subjectId == TANIYA) {
				?>
									<div><?=linkImgFile($medal['profile_photo_id'], 96, 100);?></div>
				<?
				}
				else {
				?>
									<div>
										<a href="prof_medal.php?subj=<?=$medal['subject_id'];?>&medal=<?=$medal['medal_ord'];?>&birth=<?=$birth_date;?>&last=<?=($medal['medal_ord']==$medal_sum);?>"><?=linkImgFile($medal['profile_photo_id'], 96, 100);?></a>
									</div>
				<?}
			}

			$medals_num++;
			
		}?>
									</div>
									
								</li>
                      <? 
                    }?>
							</ul>
							
						</div> <!-- slider -->
						
					</div> <!-- content -->
					
				</div> <!-- three_column padding_top -->
				
			</div> <!-- main -->
		
			<div id="footer">
				<?php include("includes/bottombar.php"); ?>
			</div>
			
		</div> <!-- wrapper -->
	
		<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id?>">
	
	</body>

<?php include("includes/footer.php"); ?>
