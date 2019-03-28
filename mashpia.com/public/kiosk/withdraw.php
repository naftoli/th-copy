<? 
include_once ("../header.php");
require_once('../file_save.php');

echo "<input type='hidden' name='julian_today' value='" . julian_today() . "'>\n";

$title = 'Withdraw points';
include("includes/header.php"); 

if (isset($_COOKIE['kiosk_machine']))
	$kiosk_machine = true;
else
	$kiosk_machine = false;
	
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


// MMC change cut-off date from this year to last year
//$today = cal_from_jd(unixtojd(), CAL_JEWISH);  --> out
$today = cal_from_jd(unixtojd()-365, CAL_JEWISH);
$chay_elul = cal_to_jd(CAL_JEWISH, 13, 18, $today['year']-($today['month']==13 && $today['day']>=18 ? 0 : 1));


$withdraw_used_points = mysql_fetch_assoc(mq("SELECT SUM(points) points_total FROM user_withdraw WHERE user_id = {$user['user_id']}"));
$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= $chay_elul")), 0));
$left_points = $cur_points - $withdraw_used_points['points_total'];

$beginning_of_hebrew_year = beginning_of_hebrew_year();
$sql = "SELECT count(*) AS vouchers_printed_this_year FROM user_withdraw WHERE user_id=" . $user['user_id'] . " AND jul_print_date >= " . $beginning_of_hebrew_year;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$vouchers_printed_this_year = $row['vouchers_printed_this_year'];

$vouchers = mysql_fetch_assoc(mq("SELECT count(*) AS no_of_vouchers FROM user_withdraw WHERE user_id=" . $user['user_id']));
$cashed = mysql_fetch_assoc(mq("SELECT count(*) AS cashed FROM user_withdraw WHERE scan_date > 0 AND user_id=" . $user['user_id']));
$uncashed = (int)$vouchers['no_of_vouchers'] - (int)$cashed['cashed'];

$total_vouchers = 64;
$miles_available = (float)mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']}")), 0);
$total_vouchers_earned = floor($miles_available / 50);
if ($total_vouchers_earned > 64) 
	$total_vouchers_earned = 64;
$tve_msg = "<label>Total Packs Earned:</labe><span>" . $total_vouchers_earned . " of 64</span>";

// ***** PRINTER ***** //
$sql = "SELECT kiosk_print FROM schools WHERE school_id=" .  $user['school_id'];
$query = mysql_query($sql);
$kiosk_print = mysql_fetch_assoc($query);
// ***** PRINTER ***** //

?>

<style type="text/css"> 
	iframe { 
	overflow-x: hidden; 
	overflow-y: scroll; 
	} 
</style> 

<script type="text/javascript">
	var vouchers = 0;
</script>

<script type="text/javascript">

	var vouchers_printed_this_year = <?=$vouchers_printed_this_year;?>;
	
	$(document).ready(function(){	
		 $("a.icon_withdraw").click(function(event){
		 
			if (vouchers_printed_this_year <= 64)
			{
				$("div#print div").hide();
				var index = $("a.icon_withdraw").index(this);
				$("div#print div").eq(index).show();
			   
				$(this).parent().animate({ opacity: 0}, function() {
					$(this).hide();
					$(".card_withdraw").eq(index).animate({marginTop:'400px'},1000);
				});
			}
			
		 });
	});	
	
	function fhide()
	{
		$("iframe#vouch").hide();
		vouchers++;
		var spans = document.getElementsByName("v");
		for ( var i in spans )
		{
		   spans[i].innerHTML=vouchers;
		} 
	}
	
	function print_voucher() 
	{
		if (vouchers_printed_this_year >= 64) 
			alert("You can only print 64 vouchers in the current school year.");
		else
		{
			$("iframe#vouch").show();
			document.getElementById("vouch").src="../withdraw_print.php";
		}
	}
</script>


<body class="green">

    <div id="wrapper">
	
        <div id="header">
			<?php include("includes/topbar.php"); ?>
		</div>
		
        <div id="main">
		
            <div id="page_title">
				Withdraw
			</div>
			
            <div class="three_column padding_top">
			
				<div class="content ">
				
                    <div id="slider">
					
						<ul>
                      
						<? 
                     
						if ($left_points >= 50) {
						
							for ($prnum=((int)($left_points/50));$prnum>0;$prnum--) { ?>
                      	
							<li>
							
								<div class="card_single">
                              	
									<div class="behind_card">
										Your voucher has been<br /> sent to print.
									</div>
									
									<div class="card_shadow card_front_left card_withdraw">
									
										<div id="receipt" class="card_front receipt">
										
											<div class="receipt_logo">
											</div>
											
											<div class="receipt_text">
												770 Album - Rebbe Picture Packet 
											
												<div class="receipt_title">
													Voucher
												</div>
												
												Congratulations
												
												<div class="receipt_name">
													<?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?>
												</div>
												
												for earning
												
												<div class="receipt_name">
													50 Miles!
												</div>
												
												<div class="receipt_small">
													Print this voucher and present it to your base commander to redeem it for a pack of 5 Rebbe pictures!
												</div>
												
											</div> <!-- receipt_text -->
                                    	
											<div class="receipt_school">
										
												<div class="logo">
													<?=!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 48) : ''?>
												</div>
											
												This card is only valid in:
											
												<div class="strong">
													<?=T_('Base')?>: #<?=$user_row['school_number']?>
												</div>
											
												<div class="strong">
													<?=es($user_row['school_name'])?>
												</div>
											
												<?=es($user_row['school_city'] . ', ' . $user_row['school_state'])?>
											
											</div> <!-- receipt_school -->
										
										</div> <!-- card_front receipt -->
									
									</div> <!-- card_shadow card_front_left card_withdraw -->
								
									<div class="member_info">
									
										<div>
										
											<div class="member_name">
												Your Account Balance
											</div>
											
											
											<div>
												<?=$tve_msg;?>
												<!--<label>Total Packs Earned:</label> <span>25 of 64 </span>-->
											</div>
											
											<div>
												<label>Vouchers Ready to Print:</label> 
												<span><?=((int)($left_points/50))?></span>
											</div>
											
											<div class="alert">
												You have 
												<span name="v"><?=$uncashed;?></span> 
												printed vouchers that may have not yet been redeemed.
											</div>
											
										</div>
										
									</div> <!-- member_info -->
								
									<? if ( ($kiosk_machine && $kiosk_print['kiosk_print'] == 1) || (!$kiosk_machine) ) : ?>
									<div class="button button_icons">
										<div class="bottom">
											<a class="icon_withdraw" id="print_button-<?=$prnum?>" onclick='print_voucher();' href="#">
												Print
											</a>
										</div>
									</div>
									<? endif; ?>
									
								</div>
							
							</li>
      				<?php 
                      }
                  }
                  else
                  {?>
                    <li>
                              <div class="card_single"> 
                      <P style="width:100%;vertical-align:middle;height:100%;text-align:center;"><?=sprintf(T_('%d miles to earn your next pack of 5 Rebbe pictures.'), 50-$left_points)?>
                    		</p>
                    	</div>
  
                    </li>
                <?}?>
  
						</ul>
						
					</div> <!-- slider-->
				
				</div> <!-- content-->
				
			</div> <!-- three_column padding_top -->
			
		</div> <!-- main -->
	
		<div id="footer">
			<? include("includes/bottombar.php"); ?>
		</div>
		
	</div> <!-- wrapper -->
	
	<div id="print" style="background:black;">
		<iframe style="display:none;" style="HEIGHT:1000px;"  WIDTH="1000" HEIGHT="1000" name="vouch" id="vouch"></iframe>
	</div>
	
</body>

<? include("includes/footer.php"); ?>
