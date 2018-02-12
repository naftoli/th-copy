<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

function getMissionID() {
	$check = "
		SELECT date_tasks_mission_id
		FROM `date_tasks_missions` 
		ORDER BY date_tasks_mission_id DESC
		LIMIT 1";
	$res = mysql_query($check);
	$row = mysql_fetch_row($res);
	$number = $row[0];
	
	$check2 = "
		SELECT date_tasks_mission_id
		FROM `date_tasks_mission_marks` 
		ORDER BY date_tasks_mission_id DESC
		LIMIT 1";
	$res2 = mysql_query($check2);
	$row2 = mysql_fetch_row($res2);
	$number2 = $row2[0];
	
	//return greater number
	return $number > $number2 ? ++$number : ++$number2;
}

$msg = '';

if (isset($_POST['submit'])) {
    $old = trim(mysql_real_escape_string($_POST['old']));
    $new = trim(mysql_real_escape_string($_POST['new']));
    
    if ($old > 0 && $new > 0) {
        //find user id
        $user = array();
        $sql = "select user_id, user_serial from users where user_serial in (" . $old . "," . $new . ")";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $user[$row['user_serial']] = $row['user_id'];
        }
        
        // figure out highest number of existing missions for old user
        // compare missions marked to medals given
        $subjects = array();
        
        $missionMarks = array();
        $sql = "select subject_id, count(*) as total 
                from date_tasks_mission_marks 
                where user_id = " . $user[$old] . "  
                group by subject_id
                order by subject_id";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            if (!in_array($row['subject_id'], $subjects)) $subjects[] = $row['subject_id'];
            $missionMarks[$row['subject_id']] = $row['total'];
        }
        
        $medalMarks = array();
        $sql = "select subject_id, max(medal_ord) as total 
                from medal_marks 
                where user_id = " . $user[$old] . "  
                group by subject_id
                order by subject_id";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            if (!in_array($row['subject_id'], $subjects)) $subjects[] = $row['subject_id'];
            $medalMarks[$row['subject_id']] = $row['total'];
        }
        //echo "<pre>"; print_r($medalMarks); echo "</pre>";
        
        $subject_medals = array();
        $sql = "select subject_id, medal_ord, missions_required from medals_subjects";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $subject_medals[$row['subject_id']][$row['medal_ord']] = $row['missions_required'];
        }
        
		mysql_query("set autocommit=0");
        mysql_query("begin");
		
        $success = true;
        foreach ($subjects as $subject) {
            // figure out how many missions have been accomplished based on highest medal earned
            $numMissions = 0;
            $medal = isset($medalMarks[$subject]) ? $medalMarks[$subject] : 0;
            if ($medal) {
                do {
                    $numMissions += intval($subject_medals[$subject][$medal]);
                } while (--$medal);
            }
            
            $updateByMedals = true;
            if (isset($missionMarks[$subject])) {
                if ($missionMarks[$subject] >= $numMissions) {
                    $updateByMedals = false;
                    
                    $arr1 = array();
                    $sql = "select date_tasks_mission_id from date_tasks_mission_marks where user_id = " . $user[$old] . " and subject_id = " . $subject;
                    $result = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($result)) {
                        $arr1[] = $row['date_tasks_mission_id'];
                    }
                    
                    $arr2 = array();
                    $sql = "select date_tasks_mission_id from date_tasks_mission_marks where user_id = " . $user[$new] . " and subject_id = " . $subject;
                    $result = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($result)) {
                        $arr2[] = $row['date_tasks_mission_id'];
                    }
                    
                    $diff = array_diff($arr1, $arr2);
                    $update = array();
                    foreach ($diff as $id) {
                        $update[] = $id;
                    }
                    //echo "Subject: " . $subject;
					//echo "<pre>"; print_r($diff); echo "</pre>";
					
                    if (count($update)) {
                        $sql = "update date_tasks_mission_marks
                                set user_id = " . $user[$new] . "
                                where user_id = " . $user[$old] . "
                                and date_tasks_mission_id in (" . implode(',', $update) . ")";
                        if (mysql_query($sql)) {
                            $sql = "update date_tasks_marks dtm
									join date_tasks dt using (date_task_id)
									join date_tasks_missions dtmm using (date_tasks_mission_id) 
                                    set dtm.user_id = " . $user[$new] . " 
                                    where dtm.user_id = " . $user[$old] . " 
                                    and dtmm.date_tasks_mission_id in (" . implode(',', $update) . ")";
							//echo $sql . "<br />";
                            if (!mysql_query($sql)) {
								echo $sql . "<br />";
                                $success = false;
								break;
                            }
                        } else {
							echo $sql . "<br />";
                            $success = false;
							break;
                        }
                    }
                }
            }
            
            if ($updateByMedals && $numMissions) {
                // create bogus missions
                $date = unixtojd();
                $mission_id = getMissionID();
                
                $user_id = $user[$new];
                for ($j = 0; $j < $numMissions; $j++) {
                    $sql = "insert into date_tasks_mission_marks (user_id, date_tasks_mission_id, subject_id, mark_date)
                            values ($user_id, $mission_id, $subject, $date)";
                    if (!mysql_query($sql)) {
                        echo $sql . "<br />";
                        $success = false;
                        break;
                    } 
                    $mission_id++;
                }
				
				// need to set date_tasks_missions table to autoincrement based on last mission_id inserted
				$mission_id = getMissionID();
				mysql_query("alter table date_tasks_missions auto_increment = " . $mission_id);
            }
        }
		
		//echo $success;		
        //echo "<pre>"; print_r($totals); echo "</pre>";
        //echo "<pre>"; print_r($subject_medals); echo "</pre>";
        if ($success) {
            //update medals and ranks
            require_once('classes/medal_updater.php');
            require_once('classes/rank_updater.php');
            $mupdater = new medal_updater();
            $rupdater = new rank_updater();
            $mupdater->update_medal_two($user[$new]);
            $rupdater->update_rank_two($user[$new]);
			
			// delete all medals/ranks from old account
			mysql_query("delete from medal_marks where user_id = " . $user[$old]);
			mysql_query("delete from rank_marks where user_id = " . $user[$old]);
			
			// commit the transaction
			mysql_query("commit");
			mysql_query("set autocommit=1");
			
			$msg = "Accounts have been merged.";
        } else {
			// rollback the transaction
			mysql_query("rollback");
			mysql_query("set autocommit=1");
			$msg = "Error merging accounts.";
		}
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Merge Accounts</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            p {
                color: red;
            }
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <h1>Merge Accounts</h1>
        
        <p>
           <?=$msg?> 
        </p>
        
        <form action="mergeAccounts.php" method="post">
            Enter serial number of OLD account <input type="text" name="old" /><br />
            Enter serial number of NEW account <input type="text" name="new" /><br />
            <input type="submit" name="submit" value="submit" />
        </form>
    </body>
</html>