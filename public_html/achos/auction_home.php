<?php require('header.php'); ?>

<?php
require('calendar.php');
require_once('file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, user_start_date, kiosk_edit
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

$auctions = mq("SELECT auction_id, auction_name, auction_message, auction_date, auction_run_date FROM auctions WHERE auction_ran = 0 AND (auction_run_date IS NULL OR auction_run_date >= " . unixtojd() . ")  AND (school_id IS NULL OR school_id = {$user['school_id']})");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Auctions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
		<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
	</HEAD>

	<body class="lgreen">
	
		<div id="wrapper">
		
			<div id="header">
			
				<div class="org">
				
					<? include ("nav.php"); ?>
					
					<div class="org_photo">
						<?=!is_null($user_row['school_logo_kiosk_id']) ? linkImgFile($user_row['school_logo_kiosk_id'], null, 100) : (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 100) : '')?>
					</div> <!-- org_photo -->
					
					<?=T_('Base')?>: #<?=$user_row['school_number']?><BR>
					<?=es($user_row['school_name'])?><BR>
					<?=es($user_row['rank_name'])?> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?>
					
				</div> <!-- org -->
				
				<noscript>
					<p class="js_alert">
						Notice: You have javascript disabled.<br>Some parts of the site will not function without javascript.
					</p>
				</noscript>
				
			</div> <!-- header -->

			<div id="main">
			
				<div id="page_title">
					<?=T_('Auctions')?>
				</div> <!-- page_title -->
				
				<div class="one_column padding_left campaign_main">
				
					<div class="medalImage iconl_auction">
					</div> <!-- medalImage iconl_auction -->
					
					<? $row = mysql_fetch_assoc($auctions); ?>
					
					<div style="clear: both; font-size: 75%;">
						<?=es($row['auction_message'])?>
					</div> <!-- clear: both; font-size: 75%; -->
					
					<? @mysql_data_seek($auctions, 0); ?>
					
				</div> <!-- one_column padding_left campaign_main -->
	  
				<div class="one_column padding_top">
				
					<div class="goal">
					
						<div class="title icon_auction">
							<?=T_('Auctions')?>
						</div> <!-- title icon_auction -->
						
						<div class="text">
						</div> <!-- text -->
						
					</div> <!-- goal -->
					
					<ul class="buttons button_icons">
						<li>
							<a href="auction_overview.php" class="icon_auction"><?=T_('Overview')?></a>
						</li>
						
						<li>
							<a href="auction_winners.php" class="icon_auction"><?=T_('Winners')?></a>
						</li>
					</ul>

				</div> <!-- one_column padding_top -->
				
				<div class="one_column padding_top">
				
					<div class="title icon_upcoming">
						<?=T_('Upcoming Auctions')?>
					</div> <!-- title icon_upcoming -->		
				
					<ul class="buttons button_icons">
						<?php while($row = mysql_fetch_assoc($auctions)): ?>
						<? if ( $row['auction_id'] != 37 ) continue; ?>
						<li>
							<a href="auction.php?auction_id=<?=$row['auction_id']?>" class="icon_auction"><?=es($row['auction_name'])?><!--<?if(!is_null($row['auction_run_date'])):?><br><span class="small"><?=$row['auction_run_date'] < unixtojd() ? T_('Auction in progress') : ($row['auction_run_date'] == unixtojd() ? T_('Auction today') : sprintf(T_('%d days to auction'), $row['auction_run_date']-unixtojd()))?></span><?endif;?>-->
								<!--
								<span class="small">
									<BR><?=sprintf(T_('Mile cut off: %s'), dateToHebrewNoYear($row['auction_date']))?>
								</span>
								-->
							</a>
							<?php endwhile; ?>
					</ul>
					
				</div> <!-- one_column padding_top -->
				
			</div> <!-- main -->

			<div id="footer">
			
				<div class="footer_logo">
				</div> <!-- footer_logo -->
				
				<div class="footer_logout">
				</div> <!-- footer_logout -->
				
			</div> <!-- footer -->

		</div> <!-- wrapper -->

	</body>
	
</html>
