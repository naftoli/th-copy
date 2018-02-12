<? 
include ("../db.php");
include ("../lang.php");
include ("../file_save.php");

include ("get_user_id.php");
$user_id = get_user_id();
$camp_id = get_camp_id($user_id);

$title = 'Store Withdraw';
include("includes/header.php"); 

$user_row = mysql_fetch_assoc(mq("SELECT u.*, c.* FROM users AS u JOIN camps AS c USING (camp_id) WHERE user_id=" . $user_id));

// ***** PRINTER ***** //
$sql = "SELECT kiosk_print FROM camps WHERE camp_id=" .  $camp_id;
$query = mysql_query($sql);
$kiosk_print = mysql_fetch_assoc($query);
// ***** PRINTER ***** //

// ***** CHECKOUT ***** //
if (isset($_POST['checkout'])) {
	$sql = "UPDATE store_purchases SET prize_shipped=1 WHERE user_id=" . $user_id . " AND prize_shipped=0";
	mq($sql);	
}
// ***** CHECKOUT ***** //

$sql = "SELECT * FROM store_purchases JOIN prizes_camp USING (prize_id) WHERE user_id=" . $user_id . " AND prize_shipped > 0 AND voucher_id=0";
$query = mysql_query($sql);
$num_vouchers = mysql_num_rows($query);
?>

<html>

	<head>
	
		<style type="text/css"> 
			.alert_two {
				position:relative; left:200px; width:200px; padding:8px; padding-left:65px; margin:8px auto; border:2px #CCC solid; text-align:center; font-size:15px; -moz-border-radius:10px; -webkit-border-radius:10px; border-radius:10px; background:url(../images/icon_alert.png) no-repeat 10px 10px;
			}
		
			iframe { 
			overflow-x: hidden; 
			overflow-y: scroll; 
			} 
		</style> 

		<script type="text/javascript">
			var vouchers = 0;
		</script>

		<script type="text/javascript">
			$(document).ready(function(){	
				 $("a.icon_withdraw").click(function(event){
					   $("div#print div").hide();
						var index = $("a.icon_withdraw").index(this);
					   $("div#print div").eq(index).show();
					   
					   $(this).parent().animate({ opacity: 0}, function() {
							$(this).hide();
							$(".card_withdraw").eq(index).animate({marginTop:'400px'},1000);
					   });
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
		</script>

	</head>
	
	<body class="green">

		<div id="wrapper">
		
			<? include ("camp_header.php"); ?>
			
			<div id="main">
			
				<div id="page_title">
					Withdraw
				</div>
				
				<div class="three_column padding_top">
				
					<div class="content ">
					
						<div id="slider">
						
							<ul>
						  
							<? 
						 
							if ($num_vouchers > 0) {
							
								while ($row = mysql_fetch_assoc($query)) { ?>
							
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
													Tzivos Hashem 
												
													<div class="receipt_title">
														Store Voucher
													</div>
													
													This voucher entitles 
													
													<div class="receipt_name">
														<?=$user_row['rank_name'] . ' ' . $user_row['first'] . ' ' . $user_row['last'];?>
													</div>
													
													to
													
													<div class="receipt_name">
														<? 
															if ($row['prize_quantity'] == 1) 
																echo $row['prize_quantity'] . " " . $row['prize_name'] . "!";
															else 
																echo $row['prize_quantity'] . " " . $row['prize_name'] . "s!";
														?>
													</div>
													
													<div class="receipt_small">
														Print this voucher and present it to your base commander to redeem it for 
														<? 
															if ($row['prize_quantity'] == 1)
																echo $row['prize_quantity'] . " " . $row['prize_name'] . "!";
															else 
																echo $row['prize_quantity'] . " " . $row['prize_name'] . "s!";
														?>													
													</div>
													
												</div> <!-- receipt_text -->
											
												<div class="receipt_school">
											
													<div class="logo">
														<?=!is_null($user_row['camp_logo_id']) ? linkImgFile($user_row['camp_logo_id'], null, 48) : ''?>
													</div>
												
													This voucher is only valid in:
												
													<div class="strong">
														<?=T_('Base')?>: #<?=$user_row['camp_number']?>
													</div>
												
													<div class="strong">
														<?=es($user_row['camp_name'])?>
													</div>
												
													<?=es($user_row['camp_city'] . ', ' . $user_row['camp_state'])?>
												
												</div> <!-- receipt_school -->
											
											</div> <!-- card_front receipt -->
										
										</div> <!-- card_shadow card_front_left card_withdraw -->
										
										
										<div class="member_info">
										
											<div>
											
												<? //if ($unused_vouchers > 0) { ?>
												<!--<div class="alert>
													You have 
													<span name="v"><?//=$unused_vouchers;?></span> 
													printed vouchers that may have not yet been redeemed.
												</div>-->
												<? //} ?>										
												
											</div>
											
										</div>
										
										<? if ($kiosk_print['kiosk_print'] == 1) : ?>
										<div class="button button_icons">
											<div class="bottom">											
												<a class="icon_withdraw" id="print_button-<?=$row['store_purchase_id'];?>" onclick='$("iframe#vouch").show(); document.getElementById("vouch").src="store_withdraw_print.php?store_purchase_id=<?=$row['store_purchase_id'];?>";' href="#">Print</a>												
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
								<P style="width:100%;vertical-align:middle;height:100%;text-align:center;"><?//=sprintf(T_('%d miles to earn your next pack of 5 Rebbe pictures.'), 50-$left_points)?>
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
		
		<div id="print">
			<iframe style="display:none;" style="HEIGHT:1000px;"  WIDTH="1000" HEIGHT="1000" name="vouch" id="vouch"></iframe>		
		</div>
		
	</body>

<? include("includes/footer.php"); ?>

</html>