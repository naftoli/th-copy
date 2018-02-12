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

function getRankImage($imageId, $rank_name)
{
    global $user_row;
    $extra = "style='opacity:.2'";
    //if($user_row['rank_name'] == $rank_name)
        $extra = "style='opacity:1'";
    return linkImgFile($imageId, "200px", "162px", $extra);
} 

function getMedalImage($count)
{
    global $user_medals,$all_medals;
    if(count($user_medals) <= $count)
        return "<img src='images/medals/holder.png' width='96px' height='100px' style='opacity:1'>";
    
    return linkImgFile($user_medals[$count]['profile_photo_id'], "96px", "100px", "style='opacity:1'");
} 

$user_medals_result = mq("
SELECT medals_subjects.profile_photo_id 
FROM medals_subjects, medal_marks
WHERE medal_marks.user_id = {$user['user_id']} and 
		medals_subjects.subject_id = medal_marks.subject_id AND
    	medals_subjects.medal_ord = medal_marks.medal_ord
ORDER BY medals_subjects.subject_id, medals_subjects.medal_ord
");

$user_medals = dbResultToArray($user_medals_result);

$ranks_result = mq("
SELECT * FROM ranks
ORDER BY rank_ord
");

$ranks = dbResultToArray($ranks_result);

$title = "Ranks";
include("includes/header.php");

?>
<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Ranks</div>
            <div class="three_column padding_top">
              <div class="content">
                    <div id="slider">
                      <ul class="ranks">
                      	<?
                        $count = 0;
                        for ($x=0 ; $x<count($ranks); $x++) 
                        {?>
                            <li class="rank_1">
                            	<div class="slider_title"><?=$ranks[$x]["rank_name"];?></div>
                                <div class="rankImage"><?=getRankImage($ranks[$x]["prof_rank_image_id"],$ranks[$x]["rank_name"])?></div>
                            	<div class="medals">
                            		<?
                            		$num = 0;
                            		if($x==count($ranks)-1) 
                            		    $num = $ranks[$x]["medals_required"];
                            		else
                            		    $num = $ranks[$x+1]["medals_required"]-$ranks[$x]["medals_required"];
                            		for($i=0; $i<$num && $i<20; $i++){?>
                                    <div><?=getMedalImage($count)?></div>
                                    <?$count++;
                            		}?>
                                </div>
                            </li>
                       <?
                        }?>
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
