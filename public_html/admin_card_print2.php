<? 
$dual_auth = true; 
$admin_auth = array('school'); 

require('header.php'); 
require_once('calendar.php');
require_once('file_save.php');

$action = "";
if (isset($_GET["action"])) {
	$action = $_GET["action"];
}

if (!empty($admin_user) && $admin_user['auth'] == 'super' && ($rank_marks = gra('rank_marks'))) {
	foreach($rank_marks as $user_id => $rank_ord) {
		$user_id = intval($user_id);
		$rank_ord = intval($rank_ord);
		mq("UPDATE rank_marks SET date_printed = NOW() WHERE user_id = $user_id AND rank_ord = $rank_ord AND date_printed IS NULL");
	}
}

$lines=5;
$cols=2;

if (!empty($admin_user)) {
	assure_id_school('school_id');
	$school_id = gri('school_id', -2);
	$class_id = gri('class_id', -1);
	$user_id = gri('user_id', -1);
	//$type = gr('type', 'temporary');
	$card_type = $_REQUEST['type_of_card'];
	$rank_ord = gri('rank_ord', -1);
	$rank_type = gr('rank_type', 'current');
	$hide_printed = $admin_user['auth'] == 'super' ? gri('hide_printed', 0) : 0;
} 
else {
	$school_id = $user['school_id'];
	$class_id = $user['class_id'];
	$user_id = $user['user_id'];
	$card_type = 'temporary';
	$rank_ord = -1;
	$rank_type = 'current';
	$hide_printed = 0;
}

if ($card_type == 'permanent') {
	$lines = 1;
	$cols = 1;
}

switch($rank_type) {

	case 'past':
		$rank_sql = 'LEFT JOIN rank_marks USING (user_id) LEFT JOIN ranks USING (rank_ord)';
    break;

	case 'all':
		$rank_sql = 'JOIN ranks';
    break;

	case 'current':
		
	default:
		$rank_sql = 'LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id)' . ($hide_printed ? ' LEFT JOIN rank_marks USING (rank_ord, user_id)' : '') . ' LEFT JOIN ranks USING (rank_ord)';
    break;
}

if (isset($_GET["school_id"]))
	$school_id = $_GET["school_id"];
	
$sql1 = "SELECT DISTINCT user_id, first, last, first_he, last_he, username, user_code, user_serial, school_name, school_city, school_state, school_number, school_logo_id, class_grade, class_sub, user_photo_id, dob, dob_he_offset, user_start_date, rank_ord, rank_name, rank_image_id, rank_color, rank_background_image_id 
		FROM users $rank_sql" . ' 
		LEFT JOIN schools USING (school_id) 
		LEFT JOIN classes USING (school_id, class_id) 
		WHERE user_registered > 0' . 
		($school_id != -1 ? " AND school_id = $school_id" : '') . 
		($class_id != -1 ? " AND class_id = $class_id" : '') . 
		($user_id != -1 ? " AND user_id = $user_id" : '') . 
		($rank_ord != -1 ? " AND rank_ord = $rank_ord" : '') . 
		($hide_printed && $rank_type != 'all' ? ' AND date_printed IS NULL AND date_promoted in (2455817,2455772)' : '') . ' 
		AND school_id not in (82) ' . ' 
		ORDER BY school_name, class_grade, class_sub, last, first, username, rank_ord';
		//echo $sql1;
$query1 = mysql_query($sql1);

//$result = !gr('display') ? false : mq("SELECT user_id, first, last, first_he, last_he, username, user_code, user_serial, school_name, school_city, school_state, school_number, school_logo_id, class_grade, class_sub, user_photo_id, dob, dob_he_offset, user_start_date, rank_ord, rank_name, rank_image_id, rank_color, rank_background_image_id FROM users $rank_sql" . ' LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (school_id, class_id) WHERE user_start_date IS NOT NULL' . ($school_id != -1 ? " AND school_id = $school_id" : '') . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($user_id != -1 ? " AND user_id = $user_id" : '') . ($rank_ord != -1 ? " AND rank_ord = $rank_ord" : '') . ($hide_printed && $rank_type != 'all' ? ' AND date_printed IS NULL' : '') . ' ORDER BY school_name, class_grade, class_sub, last, first, username, rank_ord'); //MAX(rank_ord) sub-query is not optimizied, consider joining to user and duplicating filter. However, might not be necessary since the sub-query is so fast (index read).
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_("Soldier's Rank Cards"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<STYLE type="text/css">
		.card_sheet {
		  margin: auto;
		  page-break-after: always;
		  margin-bottom:15px;
		}

		.card {
		  /*width: 3.375in;
		  height: 2.125in;*/
		  width: 3.5in;
		  height: 2in;
		  font-size: 2mm;
		  position: relative;
		  padding: 0px;
		  vertical-align: top;
		  margin: auto;
		}

		.top {
		  text-align: center;
		  font-weight: bold;
		  color: white;
		  height: .2in;
		  line-height: .2in;
		  font-size: 1.85mm;
		}

		.sig {
		  border-top: .03in solid #808080; /* default color */
		  height: .3in;
		  background-color: white;
		  text-align: right;
		  padding-<?=$align_end?>: .04in;
		}

		.card .sig h1 {
		  text-transform: uppercase;
		  padding: 0mm;
		  margin: 0mm;
		  font-weight: bold;
		  font-size: .1in;
		  line-height: .125in;
		  text-align:right;
		}

		.bg {
		  display: block;
		  position: absolute;
		  width: 3.375in;
		  height: 1.625in; /* 2.125 - .2 - .3 + .03 + .03 */
		  z-index: -1;
		}

		.card table {
		  width: 100%;
		}

		.card table td {
		  vertical-align: top;
		}

		.card p {
		  margin-top: 0px;
		  padding-top: 0px;
		}

		.card em {
		  text-transform: uppercase;
		  font-style: normal;
		}

		.card .first {
		  padding-<?=$align_start?>: .04in;
		  padding-top: .02in;
		}

		.card .first h2 {
		  text-transform: uppercase;
		  padding: 0px;
		  margin: 0px;
		  font-weight: bold;
		  font-size: .1in;
		  line-height: .125in;
		  padding-bottom: .04in;
		}

		.card .logo {
		  vertical-align: bottom;
		}

		.card .logo img {
		  height: .75in;
		  padding-<?=$align_start?>: .04in;
		}

		.card .base {
		  padding: .02in 0px;
		  vertical-align: bottom;
		}

		.card .base p {
		  margin: 0px;
		  padding: 0px;
		  padding-top: .04in;
		}

		.card .base b {
		  text-transform: uppercase;
		  font-size: 2.5mm;
		}

		.card .photo {
		  padding-top: .02in;
		  padding-<?=$align_end?>: .04in;
		}

		.card .photo img {
		  height: 1in;
		  border: .03in solid #808080;
		  display: block;
		  float: <?=$align_end;?>;
		}

		.card .code {
		  text-align: center;
		  font-size: .14in;
		  line-height: .14in;
		  margin-bottom: 0px;
		  width: 2.47in;
		  padding-<?=$align_start?>: .04in;
		  vertical-align: middle;
		  padding-top: .02in;
		}

		.card .code p {
		  margin-bottom: 0px;
		  padding-bottom: 0px;
		}

		.card .code img {
		  width: 2.43in;
		  height: .25in;
		}

		.card .stats {
		  padding-top: .02in;
		  padding-<?=$align_end?>: .04in;
		}

		.card .stats p {
		  font-size: .08in;
		  line-height: .09in;
		  text-align: <?=$align_start?>;
		  font-weight: bold;
		  text-align: center;
		  margin: 0px;
		  padding: 0px;
		  padding-bottom: .04in;
		}

		@media print {
		  body {
			background-color: transparent;
		  }

		  .card .bg {
			display: none;
		  }

		  .top {
			background-color: black;
		  }

		<? if ($card_type == 'permanent') : ?>
		  .top {
			visibility: hidden;
		  }
		  .sig {
			border-color: transparent !important;
			background-color: transparent !important;
		  }
		  .card_sheet {
		  	margin-bottom:0;
		  }
		<? else:?>
		  .sig {
			border-color: black;
			padding-right:.125in;
		  }
		  .panel {
			padding-left:.125in;
		  }

		  .card h1, .card em {
			color: black;
		  }
		  .card_sheet {
			margin:0 .16in;
		  	margin-bottom:-.1in;
		  }
		<? endif;?>
		}
		</STYLE>
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<script>
			$(document).ready(function() {
				$('.type_of_card').click(function() {
					$('#card_type').val($(this).val());					
				});					
			});
		</script>
		
		<DIV CLASS="body">
		
			<DIV class="noprint">			
				<H1><?=T_("Soldier's Rank Cards")?></H1>
				
				<? if (!empty($message)) : ?>
					<H2><?=$message?></H2>
				<? endif; ?>

				<? if (!empty($admin_user)) : ?>

					<H2>Printing Instructions</H2>
					
					<P style="font-size: 150%;">
						File<?=$next_arr?>Page Set up
						<BR>
						<BR>
						Portrait
						<BR>
						Scale 90
						<BR>
						<BR>
						Margins: Top, Left, Right, Bottom (all): 0.0
						<BR>
						<BR>
						All headers and footers: Blank
						<BR>
					</P>
					
					<HR>
					<? if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : ?>
						<? $school_result = mq('SELECT school_id, school_name FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name'); ?>

						<FORM action="admin_card_print.php" method="get" accept-charset="UTF-8">
							<P>
								<LABEL><?=T_('Select Institution')?>: 
									<SELECT name="school_id">								
									<? if($admin_user['auth'] == 'super') : ?>
										<OPTION value="-1">&lt;<?=T_('All')?>&gt;
									<? endif; ?>
									<? while ($school_row = mysql_fetch_assoc($school_result)) : ?>
										<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
									<? endwhile; ?>
									</SELECT>
								</LABEL>
								
								<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
							</P>
						</FORM>
						
					<HR>
					<?endif;?>
					
				<?endif;?>
				
			</DIV>
			
		<?if($school_id == -2):?>
			<?=T_('Please select an Institution.')?>
		<?else:?>
			<?if(!empty($admin_user)):?>
		<DIV class="noprint">
		<?
		$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
		$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_start_date IS NOT NULL ORDER BY class_grade, class_sub, last, first, username");
		$rank_result = mq('SELECT rank_ord, rank_name FROM ranks ORDER BY rank_ord');
		?>
		
		<FORM action="admin_card_print.php" method="get" accept-charset="UTF-8">
			<INPUT type="hidden" name="action" id="action" value="print">
			<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
			
			<P>								
				<?if(mysql_num_rows($class_result)):?>
					<?=T_('Show Platoon')?>: 
					<SELECT name="class_id">
						<OPTION value="-1">&lt;<?=T_('All')?>&gt;
						<?while($class_row = mysql_fetch_assoc($class_result)):?>
						<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
						<?endwhile;?>
					</SELECT>
					<BR>
				<?endif;?>
				
				<?if(mysql_num_rows($user_result)):?>
					<?=T_('Show Soldier')?> 
					<SELECT name="user_id">
						<OPTION value="-1">&lt;<?=T_('All')?>&gt;
						<? while($user_row = mysql_fetch_assoc($user_result)): ?>
						<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
						<?endwhile;?>
					</SELECT> 
					(<?=T_('Note: Only registered students')?>)<BR>
				<?endif;?>
				
				<?=T_('Limit to Rank')?>: 
				<SELECT name="rank_ord">
					<OPTION value="-1">&lt;<?=T_('All')?>&gt;
					<?while($row = mysql_fetch_assoc($rank_result)):?>
					<OPTION value="<?=$row['rank_ord']?>" <?=$row['rank_ord'] == $rank_ord ? 'SELECTED' : ''?>><?=es($row['rank_name'])?></OPTION>
					<?endwhile;?>
				</SELECT>
				
				<BR>
				
				<input type="hidden" id="card_type" value="">
				
				<?=T_('Card type')?>:
				<LABEL>
					<INPUT type="radio"  <? if ($card_type == 'temporary') echo 'checked'; ?> class="type_of_card" name="type_of_card" value="temporary"><?=T_('Temporary')?></LABEL>
				<LABEL>
				
				<INPUT type="radio"  <? if ($card_type == 'permanent') echo 'checked'; ?> class="type_of_card" name="type_of_card" value="permanent"><?=T_('Permanent')?></LABEL>
				
				<BR>
				
				<?=T_('Show Rank')?>:
				<LABEL><INPUT type="radio" name="rank_type" value="current" <?=$rank_type == 'current' ? 'checked' : ''?>><?=T_('Current Only')?></LABEL>
				<LABEL><INPUT type="radio" name="rank_type" value="past" <?=$rank_type == 'past' ? 'checked' : ''?>><?=T_('Current and Previous')?></LABEL>
				<LABEL><INPUT type="radio" name="rank_type" value="all" <?=$rank_type == 'all' ? 'checked' : ''?>><?=T_('All possible ranks')?></LABEL> (<?=T_('For use with Limit to Rank, and printing cards in advance')?>)
				
				<BR>
				
				<? if($admin_user['auth'] == 'super') : ?>
					<LABEL><?=T_('Hide cards marked as printed')?>: <INPUT type="checkbox" name="hide_printed" value="1" <?=$hide_printed ? 'checked' : ''?>></LABEL> (<?=T_('Not effective with choice: "All possible ranks"')?>)<BR>
				<?endif;?>
				
				<INPUT class="submit" type="submit" name="display" value="<?=T_('Go')?>">
			</P>
		</FORM>
		
		<HR>
		</DIV>
		<?endif;?>
		<?if($result): ?>
		<DIV class="noprint">
		<?if(!empty($admin_user)):?>
		<?if($admin_user['auth'] == 'super'):?><P><A HREF="admin_school.php"><?=T_('Back to Institution list')?></A></P><?endif;?>
		<P><A HREF="admin_class.php?school_id=<?=$school_id?>"><?=T_('Back to Platoon list')?></A></P>
		<P><A HREF="admin_user.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_('Back to Soldier list')?></A></P>
		</DIV>
		<?endif;?>
		<P class="noprint" style="text-align: center;"><INPUT type="button" value="<?=T_('Print')?>" onClick="print();"></P>
		
		<FORM action="admin_card_print.php" method="post" accept-charset="UTF-8">
			<? if (!empty($admin_user) && $admin_user['auth'] == 'super') : ?>
				<P class="noprint" style="text-align: center;">
				<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
				<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
				<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
				<INPUT type="hidden" name="rank_ord" value="<?=$rank_ord?>">
				<INPUT type="hidden" name="type" value="<?=$card_type?>">
				<INPUT type="submit" value="<?=T_('Mark Cards as Printed')?>">
				</P>
			<? endif; ?>

			<? $line=0; $col=0; ?>

			<? if ($action != "") : ?>
				<? while ($row = mysql_fetch_assoc($query1)) : ?> 
			
	<?
					if ($col >= $cols) {
						echo "</TR>\n";
						$col = 0;
					}
					
					if ($col == 0) {
						if($line>=$lines) {
							echo "</TABLE><HR class='noprint'>\n";
							$line = 0;
							$col = 0;
						}
						
						if ($line == 0) 
							echo "<TABLE class='card_sheet'>";
							
						$line++;
						echo "<TR>";
					}
					
					$col++;
					
					if (is_null($row['rank_color'])) 
						$row['rank_color'] = '#808080';
	?>

					<TD class="card">
					
						<? if(!is_null($row['rank_ord'])): ?>
						<INPUT type="hidden" name="rank_marks[<?=$row['user_id']?>]" value="<?=$row['rank_ord']?>">
						<?endif;?>
						
						<DIV class="top" style="background-color: <?=es($row['rank_color'])?>;">
							<?=sprintf(T_('This card entitles the cardholder to TH privileges with the %s rank'), es($row['rank_name']))?>
						</DIV>
						
						<DIV class="sig" style="border-color: <?=es($row['rank_color'])?>;">
							<?=T_('Authorized Signature')?>
							<H1><EM style="color: <?=es($row['rank_color'])?>;"><?=es($row['rank_name'])?></EM> <?=es($row['first_he'] ? $row['first_he'] : $row['first']), ' ', es($row['last_he'] ? $row['last_he'] : $row['last'])?></H1>
							<?=T_('SERIAL'), ' # ', $row['user_serial']?>
						</DIV>
						
						<?=!is_null($row['rank_background_image_id']) ? linkImgFile($row['rank_background_image_id'], NULL, NULL, 'class="bg"') : ''?>

						<DIV class="panel">
							<TABLE cellspacing="0" cellpadding="0">
								<TR>
									<TD class="logo" width="1">
									<?=!is_null($row['school_logo_id']) ? linkImgFile($row['school_logo_id']) : ''?>
									</TD>
									<TD class="base">
										<P>
											<?=T_('BASE')?> #<?=$row['school_number']?><BR>
											<B><?=es($row['school_name'])?></B><BR>
											<?=es($row['school_city']), ', ', es($row['school_state'])?>
										</P>
									</TD>

									<!-- ***** STUDENTS PHOTO ***** -->
									<TD class="photo" width="1">
										<? if ($row['user_photo_id'] > 0) : ?>
										<?=linkImgFile($row['user_photo_id'], NULL, NULL, 'style="border-color: ' . es($row['rank_color']) . ';"');?>
										<? endif; ?>
										<?//=$type == 'permanent' && !is_null($row['user_photo_id']) ? linkImgFile($row['user_photo_id'], NULL, NULL, 'style="border-color: ' . es($row['rank_color']) . ';"') : ''?>
									</TD>
									<!-- ***** STUDENTS PHOTO ***** -->
									
								</TR>
							</TABLE>
							<TABLE>
								<TR>
									<TD class="code">
										<P><IMG src="barcode.php/3<?=$row['user_code']?>" alt=""><BR>3<?=$row['user_code']?></P>
									</TD>
									<TD class="stats">
										<P>
											<?=T_('Member since')?><BR><SPAN dir="rtl"><?=dateToHebrewShortYear($row['user_start_date'])?></SPAN>
										</P>
										<P>
											<?
											if($bdate = dateToJD($row['dob'])+$row['dob_he_offset']) {
											  $cal = cal_from_jd($bdate, CAL_JEWISH);
											  $valid = T_('Valid until') . '<BR><SPAN dir="rtl">' . dateToHebrewShortYear(cal_to_jd(CAL_JEWISH, 13, cal_days_in_month(CAL_JEWISH, 13, $cal['year']+13), $cal['year']+13)) . '</SPAN>';
											} else {
											  $valid = '';
											}
											?>
											<?=$valid?>
										</P>
									</TD>
								</TR>
							</TABLE>
						</DIV>

					</TD>
				<? endwhile; ?>
			<? endif; ?>
		
		</TR></TABLE>
		</FORM>
		<? endif; ?>
		<? endif; ?>
		</DIV>
		
		<DIV class="noprint">
			<? include('admin_footer.php'); ?>
		</DIV>
	</BODY>
	
</HTML>
