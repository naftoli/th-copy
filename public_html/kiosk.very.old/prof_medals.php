<?php 
include_once ("../header.php");
require_once('../file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
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

$subjects_result = mq("SELECT subjects.subject_id, subject_name, subject_type, subjects.black_image_id 
FROM subjects 
JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = {$user_row['school_id']}) 
WHERE subject_type NOT IN ('school_points', 'home_points') 
ORDER BY subject_name");

$right_subjects = subjectsToSearchIndex($subjects_result);

$user_medals = userMedalsToSearchIndex($user_medals_result);

$all_medals_result = mq("
SELECT medals_subjects.subject_id, medals_subjects.medal_ord, profile_photo_id, medal_name, subject_name, prof_image_id
FROM medals_subjects, subjects, medals
WHERE
    medals_subjects.subject_id = subjects.subject_id AND
    medals_subjects.medal_ord = medals.medal_ord
ORDER BY subject_id, medal_ord
");

$all_medals = dbResultToArray($all_medals_result);

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
                    <?
                        $prv_subject = null;
                        $is_first_subj = true;
                        foreach ($all_medals as $medal) 
                        {
                            if(!in_array($medal['subject_id'],$right_subjects))
                                continue;
                            if ($medal['subject_id']!=$prv_subject)
                            {
                                if (!$is_first_subj)
                                {
                    ?>
                                </div>
                            </li>
                    <?                
                                }
                                $prv_subject = $medal['subject_id'];
                                $is_first_subj = false;
                    ?>
                            <li>
                                <div class="slider_title"><?=$medal["subject_name"];?></div>
                                <div class="medalImage"><?=linkImgFile($medal['prof_image_id'], 200, 210);?></div>
                                <div class="medals">
                    <?      }
                            $class_str = (isset($user_medals[$medal['subject_id']][$medal['medal_ord']]) && $user_medals[$medal['subject_id']][$medal['medal_ord']]) ? ' class="active"' : '';
                    ?>
                                    <div<?=$class_str;?>><a href="prof_medal.php?subj=<?=$medal['subject_id'];?>&medal=<?=$medal['medal_ord'];?>"><?=linkImgFile($medal['profile_photo_id'], 96, 100);?></a></div>
                    <?
                        }
                    ?>
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
