<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
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

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Deposit your Achievement or Prize card'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		
		<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
		<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
		<LINK href="card_printer.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="modules/easySlider1.7.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			$(function () {
			  $("#slider").easySlider({
				numeric: true,
				controlsBefore: '<div class="page_dots">',
				controlsAfter:  '<\/div>'
			  });
			});

			<? if (gr('p')!==''): ?>
				$(window).load(function () {
				  $('a', $('#controls<?=gr('p')?>')).click();
				});
			<? endif; ?>
		</SCRIPT>
	</HEAD>
	
	<body class="orange">
	
		<?if($user['registered']) include('code_processor.php'); ?>
		
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
					<p class="js_alert">Notice: You have javascript disabled.<br>Some parts of the site will not function without javascript.</p>
				</noscript>
				
			</div> <!-- header -->

			<div id="main">
			
				<div id="page_title">
					<?=T_('Deposit')?>
				</div>
				
				<div class="three_column padding_top">
				
					<div class="content deposit">
						<? $codes = mq("SELECT first, last, code_id, code_id_prefix, UNIX_TIMESTAMP(grant_date) grant_date FROM user_codes LEFT JOIN admins USING (admin_id) WHERE user_id = {$user['user_id']} ORDER BY grant_date"); ?>
						<? if(!mysql_num_rows($codes)): ?>
							<P style="text-align: center; padding-top: 60px;"><?=T_('No achievement cards available for deposit.')?></P>
						<? else: ?>
							<div id="slider">
							
								<ul>
								
									<? while ($row = mysql_fetch_assoc($codes)): ?>
									
										<li>
										
											<FORM action="deposit.php" method="post" accept-charset="UTF-8" onSubmit="var form = this; $('body').fadeTo('normal', 0.4, function () { form.submit(); }); return false;">
											
												<? $code_details = code_details($row['code_id_prefix'], $row['code_id'], $user['user_id']); ?>
												
												<? if (!$code_details): ?>												
													<P><?=sprintf(T_('Card %s is missing'), $row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT))?></P>													
												<?else:?>
												
													<div class="card_single">
													
														<div class="card_shadow">
															<?=display_card_front($code_details['expires'], $code_details['school_number'], $code_details['school_name'], $code_details['school_city'], $code_details['school_state'], $code_details['school_logo_id'])?>
														</div>
														
														<div class="card_shadow">
															<?=display_card_back($row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT), $code_details['points'], $code_details['bonus'], $code_details['left_circle'], $code_details['right_circle'], $code_details['description'], $code_details['subject_name'], $code_details['subject_image_id'], $code_details['series'])?>
														</div>
														
														<div class="member_info">
															<div>
																<label><?=T_('Granted by')?>:</label> <span><?=es($row['first'] . ' ' . $row['last'])?></span>
															</div>
															<div>
																<label><?=T_('Date')?>:</label> <span><?=dateToHebrew(unixtojd($row['grant_date']))?></span>
															</div>
														</div>
														
														<div class="button button_icons">
															<INPUT type="hidden" name="scan_code" value="<?=$row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT)?>">
															
															<!-- ********** Deposit to Account (Button) ********** -->
															<div class="bottom">
																<a class="icon_deposit" href="#" onClick="$(this).parents('form').get(0).onsubmit(); return false;">
																	<?=T_('Deposit to Account')?>
																</a>
															</div>
															<!-- ********** Deposit to Account (Button) ********** -->
														</div>
														
													</div> <!-- card_single -->
													
												<?endif;?>
												
											</FORM>
											
										</li>
										
									<?endwhile;?>
								</ul>
								
							</div> <!-- slider -->
						<?endif;?>
						
					</div> <!-- content deposit -->
					
				</div> <!-- three_column padding_top -->
				
			</div> <!-- main -->
			
		</div> <!-- wrapper -->
		
		<div id="footer">
			<div class="footer_logo"></div>
			<div class="footer_logout"></div>
		</div>
		
		<input type="hidden" name="dir" value="<?=$dir;?>">
	</body>
	
</html>
