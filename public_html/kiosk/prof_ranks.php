<?php 
include_once ("../header.php");
require_once('../file_save.php');
include_once ("classes/rank.php");

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
    global $user_medals;
	global $all_medals;
	
    if (count($user_medals) <= $count)
        return "<img src='images/medals/holder.png' width='96px' height='100px' style='opacity:1'>";
    
    return linkImgFile($user_medals[$count]['profile_photo_id'], "96px", "100px", "style='opacity:1'");
} 

// ********** RANKS ********** //
$ranks = array();
$sql = "SELECT * FROM ranks ORDER BY rank_ord";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$rank = new rank($row);
	array_push($ranks, $rank);
}
// ********** RANKS ********** //

// ********** MEDALS ********** //
$sql = "SELECT medals_subjects.profile_photo_id ";
$sql = $sql . "FROM medals_subjects, medal_marks ";
$sql = $sql . "WHERE medal_marks.user_id=" . $user['user_id'] . " ";
$sql = $sql . "AND medals_subjects.subject_id=medal_marks.subject_id ";
$sql = $sql . "AND medals_subjects.medal_ord=medal_marks.medal_ord ";
$sql = $sql . "ORDER BY medal_marks.date_awarded ";
echo "<input type='hidden' name='SQL' value='$sql'>";
$user_medals_result = mq($sql);
$user_medals = dbResultToArray($user_medals_result);
// ********** MEDALS ********** //

$title = "Ranks";
include("includes/header.php");
?>

<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
		</div>
		
        <div id="main">
		
            <div id="page_title">
				Ranks
			</div>
			
            <div class="three_column padding_top">
			
				<div class="content">
				
                    <div id="slider">
					
						<ul class="ranks">
							<? $count = 0; ?>
							
							<!-- ********** RANKS ********** -->
							<? for ($rank_number = 0 ; $rank_number < count($ranks); $rank_number++) : ?>
								<li class="rank_1">
									<div class="slider_title">
										<?=$ranks[$rank_number]->rank_name;?>
									</div>
									
									<div class="rankImage">
										<?=getRankImage($ranks[$rank_number]->prof_rank_image_id, $ranks[$rank_number]->rank_name)?>
									</div>
									
									<!-- ********** MEDALS *********** -->
									<div class="medals">
										<? 	
											$num = 0;
											if($rank_number == count($ranks) - 1) 
												$num = $ranks[$rank_number]->medals_required;
											else
												$num = $ranks[$rank_number + 1]->medals_required - $ranks[$rank_number]->medals_required;
										?>
										
										<? for ($i = 0; $i < $num && $i < 20; $i++) : ?>
											<div><?=getMedalImage($count)?></div>
											<? $count++; ?>
										<? endfor; ?>
									</div>
									<!-- ********** MEDALS *********** -->
				
								</li>
						   <? endfor; ?>
						   <!-- ********** RANKS ********** -->
						   
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
