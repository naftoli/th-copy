<? 
include_once ("../header.php");
require_once('../file_save.php');
$title ='Withdraw points';
include("includes/header.php"); 
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
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

$today = cal_from_jd(unixtojd(), CAL_JEWISH);
$chay_elul = cal_to_jd(CAL_JEWISH, 13, 18, $today['year']-($today['month']==13 && $today['day']>=18 ? 0 : 1));

$withdraw_used_points = mysql_fetch_assoc(mq("SELECT SUM(points) points_total FROM user_withdraw WHERE user_id = {$user['user_id']}"));
$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= $chay_elul")), 0));
$left_points = $cur_points - $withdraw_used_points['points_total'];
/*
echo "<p><pre style='font: normal 14px Arial;color: #fff;'>";print_r($withdraw_used_points);echo "</pre></p>";
echo "<p><pre style='font: normal 14px Arial;color: #fff;'>";echo "cur_points: $cur_points, user: {$user['user_id']}</pre></p>";
*/
?>
<style type="text/css"> 
iframe { 
overflow-x: hidden; 
overflow-y: scroll; 
} 
</style> 
 
<script type="text/javascript">
	$(document).ready(function(){	
		 $("a.icon_withdraw").click(function(event){
		   $("div#print div").hide();
			var index = $("a.icon_withdraw").index(this);
		   $("div#print div").eq(index).show();
		   $(this).parent().animate({ opacity: 0}, function() {;});
		   event.preventDefault();
		 });
	});	
</script>


<body class="green">
<div id="wrapper">
	<div id="header">
        <?php include("includes/topbar.php"); ?>
    </div>
    <div id="main">
    	<div id="page_title">Withdraw</div>
        	<div class="three_column padding_top">
            	<div class="content ">
                	<div id="slider">
                	 
                      <ul>
                      
                      <? 
                     
                  if($left_points >= 50)
                  {
                      for ($prnum=((int)($left_points/50));$prnum>0;$prnum--) {?>                      	
                      	<li>
                        	<div class="card_single">
                            	<div class="card_shadow card_front_left card_withdraw">
                                	<div class="card_front">
                                        <? require('../withdraw_print.php');?>
                                    </div>
                                </div>
                                <div class="member_info">
                                	<div><label>Given by:</label> <span><?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?></span></div>
                                	</div>
                  				<p><?=T_('You have earned enough points to qualify for a card.')?></p>
                  				<div class="button button_icons">
                                	<div class="bottom"><a class="icon_withdraw" id="print_button-<?=$prnum?>" onclick="document.getElementById('vouch').src='../withdraw_print.php';" href="#">Print</a>
                                    </div>
                                </div>
                        	</div>
                        </li>
      				<?php 
                      }
                  }
                  else
                  {?>
                    <li>
                      <div class="card_single">
                       	<div class="member_info">
                           <div>
                                <?=sprintf(T_('You need %d more points to qualify for a card.'), 50-$left_points)?>
                    		</div>
                    	</div>
                    </div>
                    </li>
                <?}?>
  
					</ul>
				</div>
			</div>
 		</div>
	</div>
	<div id="footer">
	<? include("includes/bottombar.php"); ?>
	</div>
</div>
<div id="print">
	<iframe  WIDTH="1000" HEIGHT='1000' name="vouch" id="vouch"></iframe>
</div>
</body>

<? include("includes/footer.php"); ?>