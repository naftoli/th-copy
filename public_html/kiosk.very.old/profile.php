<?php
include_once ("../header.php");
require_once('../file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
       school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, 
       rank_ord, rank_name, rank_image_id, rank_color
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

$user_row['class_average'] = ( is_null($user_row['class_id']) ) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0), 2);
$user_row['school_average'] = ( is_null($user_row['class_id']) ) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = {$user['school_id']} AND user_start_date IS NOT NULL"), 0), 2);
$user_row['total_miles'] = number_format( $user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']}")), 0), 2 );

$today = cal_from_jd(unixtojd(), CAL_JEWISH);
$chay_elul = cal_to_jd(CAL_JEWISH, 13, 18, $today['year']-($today['month']==13 && $today['day']>=18 ? 0 : 1));

function get_rank_class ($rank_ord, $user_rank)
{
    return ($rank_ord==$user_rank) ? "currentrank$rank_ord" : "rank$rank_ord";
}

function print_ranks_list ($bot_start, $user_rank)
{
    $ranks_row = mysql_fetch_column(mq("SELECT rank_ord, rank_name FROM ranks"));
    $total = count($ranks_row);

?><ul class="rank_top">
<?php for( $i=1; $i<$bot_start; $i++ ) { ?>
                        <li class="<?=get_rank_class($i, $user_rank)?>"><?=$ranks_row[$i]?></li>
<?php } ?>
                    </ul>
                    <ul class="rank_bot">
<?php for( $i=$bot_start; $i<=$total; $i++ ) { ?>
                        <li class="<?=get_rank_class($i, $user_rank)?>"><?=$ranks_row[$i]?></li>
<?php } ?>
                    </ul>
<?php
}
//echo '<p><pre style="font:normal 14px arial;color:#fff;">';print_r($user_row);echo '</pre></p>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="chrome=1">
<title>User Profile - Tzivos Hashem Management System</title>
<link rel="alternate" media="print" href="../withdraw_print.php">
<link rel="stylesheet" type="text/css" href="scripts/shadowbox/shadowbox.css">
<link href="styles/reset.css" rel="stylesheet" type="text/css" />
<link href="styles/style.css" rel="stylesheet" type="text/css" />
<link href="styles/print.css" rel="stylesheet" type="text/css" media="print" />
<!--[if IE]>
<link href="styles/style_ie.css" rel="stylesheet" type="text/css" />
<![endif]-->
<style type="">
.rank {
  width: 100px;
  height: auto;
  border: none;
  position: absolute;
  margin-top: 100px;
}
</style>
<script src="scripts/jquery.core.js" type="text/javascript"></script>
<script src="scripts/jquery.ui.js" type="text/javascript"></script>
</head>

<body class="red">
    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Profile</div>
            <div class="two_column">
            	<div class="padding_top padding_left member_info_box">
                	<div class="member_photo">
                        <div class="member_photo_img"><?=!is_null($user_row['user_photo_id']) ? linkImgFile($user_row['user_photo_id'], NULL, 150) : ''?></div>
                        <div class="member_photo_cover"></div>
                        <!--<div class="member_badge">
                            <?=!is_null($user_row['rank_image_id']) ? linkImgFile($user_row['rank_image_id'], 100, 100) : ''?>
                        </div>
                    --></div>
                    <div class="member_info">
                    	<ul>
                        	<li class="member_name"><?=$user['display']?></li>
                        	<li><label>Rank:</label> <span><?=$user_row['rank_name']?></span></li>
                        	<li><label>Serial #:</label> <span><?=$user_row['user_serial']?></span></li>
                        	<li><label>Platoon:</label> <span><?=$user_row['class_grade']?><?=$user_row['class_sub']?></span></li>
                        	<li><label>Teacher:</label> <span><?=$user_row['class_teacher']?></span></li>
                        	<li><label>Platoon Average:</label> <span><?=$user_row['class_average']?></span></li>
                        	<li><label>Base Average:</label> <span><?=$user_row['school_average']?></span></li>
                        	<li><label>Total Miles:</label> <span><?=$user_row['total_miles']?></span></li>
                        </ul>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="rank_badges">
                <?php print_ranks_list (9, $user_row['rank_ord']);?>
                </div>                
            </div>
            <div class="one_column">
              <ul class="buttons button_icons">
                    <li><a href="prof_ranks.php" class="icon_ranks">Ranks</a></li>
                    <li><a href="prof_medals.php" class="icon_medals">Medals</a></li>
                    <li><a href="#" class="icon_mileage">Mileage</a></li>
                    <li><a href="mission.php" class="icon_missions">Upcoming Missions</a></li>
              </ul>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
