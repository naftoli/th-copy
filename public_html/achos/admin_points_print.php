<? 
$admin_auth = array('school','user'); 

require('header.php');
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$lines=5;
$cols=2;

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
$user_id = gri('user_id', -1);

if($auth_mode == 'user') {
	check_school_setting($user_id, 'home_school');
	$lines = 1;
	$cols = 1;
}

$tanya = gri('tanya');
$subject_id = gri('subject_id', -1);
$medal_stage = gri('medal_stage');
$points_codes_template_id = gri('points_codes_template_id', -1);

if(!is_null($tanya)) {
  // Tanya daily cards
} 
elseif($subject_id != -1) {
  // Local cards
  // $points
} 
elseif($points_codes_template_id != -1) {
	$row = mysql_fetch_assoc(mq("SELECT school_id, subject_id, points, left_circle, right_circle, description, series FROM points_codes_templates WHERE points_codes_template_id = $points_codes_template_id" . ($admin_user['auth'] != 'super' ? ' AND (school_id IS NULL OR school_id ' . ($auth_mode == 'user' ? " = $school_id" : 'IN (' . implode(',', $admin_user['auths']['school']) . ')') . ')' : '')));
	
	if ($row) {
		if (!is_null($row['school_id'])) 
			$school_id = $row['school_id'];
			
		$subject_id = $row['subject_id'];
		sgr('points', $row['points']);
		sgr('left_circle', $row['left_circle']);
		sgr('right_circle', $row['right_circle']);
		sgr('description', $row['description']);
		sgr('series', $row['series']);
	}
} 
elseif(!is_null($medal_stage)) {
	$medal_stage = max(1, min($medal_stage, 4));
  // Tanya Release cards
} 
elseif(gr('subject_mission_series')) {
  // Global AKA mission cards
  @list($subject_id, $mission_number_series) = split('/', gr('subject_mission_series', '-1/-1'));
  $subject_id = intval($subject_id);
  $mission_number_series = intval($mission_number_series);
  // $is_bonus
} else {
  // Global AKA mission cards
  @list($subject_id, $mission_number) = split('/', gr('subject_mission', '-1/-1'));
  $subject_id = intval($subject_id);
  $mission_number = floatval($mission_number);
  // $is_bonus
}

$points = grf('points', 50);
$is_bonus = gri('is_bonus', 0);

$fb = gr('fb', 'fb');
$copies = min(max(gri('copies', 1), 1), 100);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Print Achievement Cards'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>		
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<LINK href="card_printer.css" rel="stylesheet" type="text/css">
		<STYLE type="text/css">
			.fronts, .backs {
			  margin:-.05in .125in;
			}

			<?=$auth_mode == 'user' ? '' : '.fronts, '?>.backs {
			  page-break-after: always;
			}

			.fronts td, .backs td {
			  border: 1px dashed black;
			  -webkit-box-sizing: border-box;
			  -moz-box-sizing: border-box;
			  vertical-align: middle;
			  /*height: 2.125in;*/
			  height: 2in;
			  width: 3.5in;
			}
			/*.fronts .row5 > td, .backs .row5 > td {
			  height: auto;
			}
			.fronts .row5 .card_front {
				margin-top:18px;
			}
			.fronts .row5 .card_front, .backs .row5 .card_back {
				margin-top:11px;
				margin-bottom:-15px;
			}*/
			.fronts td td, .backs td td {
			  width: auto;
			  height: auto;
			  border: none;
			}

			@media print {
			  .fronts td, .backs td {
				border: none;
			  }

			  hr {
				display: none;
			  }
			}
		</STYLE>
	</HEAD>

	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
					
			<DIV class="noprint">
			
				<H1>
					<?=T_('Print Achievement Cards')?>
				</H1>
				
				<? if (!empty($message)) : ?>
					<H2><?=$message?></H2>
				<? endif; ?> <!-- if (!empty($message)) -->

				<? if ($auth_mode != 'user'): ?>
				
					
<!--
					<P style="font-size: 150%;">
						File<?=$next_arr?>Page Set up<BR>
						<BR>
						Portrait<BR>
						Scale 95 (make sure that shrink to page is NOT checked off)<BR>
						<BR>
						Margins: Top: 0.3<BR>
						Left, Right, Bottom: 0.0<BR>
						<BR>
						All headers and footers: Blank<BR>
						<BR>
						Print on green perforated paper
					</P>
-->					
									
						<HR>
					
						<!-- Print Local (school) achievement cards -->
						<FORM action="admin_points_print.php" method="get" accept-charset="UTF-8">
						
							<H2>
								<?=T_('Create achievement cards')?>
							</H2>
							
							<P>
								<? if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : ?>								
									<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

								<LABEL><?=T_('Select Institution')?>: 
									<SELECT name="school_id">
										<?while($school_row = mysql_fetch_assoc($school_result)):?>
										<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
										<?endwhile;?>
									</SELECT>
								</LABEL>
								
								<BR>
								
								<? endif; ?> <!-- if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) -->
								
								<? $subject_result = mq('SELECT subject_id, subject_name, inst_name FROM subjects JOIN institutions USING (inst_id) WHERE'  . ($admin_user['auth'] != 'super' ? ' inst_id IN (' . implode(',', $admin_user['inst_ids']) . ') AND' : '') . ' subject_type = \'school_points\' ORDER BY inst_name, subject_name'); ?>
								
								<LABEL><?=T_('Select Campaign')?>:
									<SELECT name="subject_id">
										<? while($row = mysql_fetch_assoc($subject_result)): ?>
										<OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></OPTION>
										<? endwhile; ?>									
									</SELECT>
								</LABEL>
								
								<BR>
								
								<LABEL>
									<?=T_('Enter Description')?>: 
									<INPUT type="text" name="description" value="<?=gr('description')?>">
								</LABEL>
								
								<BR>
								
								<LABEL>
									<?=T_('Enter # of Miles')?>: 
									<INPUT type="text" name="points" maxlength="9" size="9" value="<?//=$points?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999999.99));">
								</LABEL>
								
								<BR>
								
								<LABEL>
									<?=T_('Left Circle')?>: 
									<INPUT type="text" name="left_circle" value="1<?//=gr('left_circle')?>" maxlength="1">
								</LABEL>
								
								<BR>
								
								<LABEL>
									<?=T_('Right Circle')?>: 
									<INPUT type="text" name="right_circle" value="1<?//=gr('right_circle')?>" maxlength="1">
								</LABEL>
								
								<BR>
<!--								
								<LABEL>
									<?=T_('Series')?>: 
									<INPUT type="text" name="series" maxlength="3" size="3" value="<?=gr('series')?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 255));">
								</LABEL>
								
								<BR>
								
								<LABEL>
									<?=T_('Page type')?>: 
								
									<SELECT name="fb">
										<OPTION value="fb" <?=$fb == 'fb' ? 'selected' : ''?>><?=T_('Fronts and backs')?>
										<OPTION value="f" <?=$fb == 'f' ? 'selected' : ''?>><?=T_('Fronts only')?>
										<OPTION value="b" <?=$fb == 'b' ? 'selected' : ''?>><?=T_('Backs only')?>
									</SELECT>
								</LABEL>
								
								<BR>
-->								
								<input type='hidden' name='fb' value='b'>

								<LABEL>
									<?=T_('# of sheets (10 cards per sheet)')?>: 
									
									<SELECT name="copies">
										<? for ($i = 1; $i <= 500; $i += $i <20 ? 1 : ($i < 100 ? 5 : 50)):?>
										<OPTION value="<?=$i?>" <?=$copies == $i ? 'selected' : ''?>><?=$i?>
										<?endfor;?>
									</SELECT>
								</LABEL>
																
								<BR>
								
								<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
								
								<p>Please Note: If you would like to add a new campaign please email 
								<a href='mailto:cth@tzivoshashem.org'>cth@tzivoshashem.org</a></p>
								
							</P>
							
						</FORM>
						<!-- Print Local (school) achievement cards -->
						
						<HR>
						
				<?endif;?> <!-- if ($auth_mode != 'user') -->
				
			</DIV> <!-- noprint -->
			
			<?
			
			if (!is_null($tanya)) {
				$sql = "SELECT subject_name, subject_id, subject_image_id, school_id, school_name, school_city, school_state, school_logo_id, school_number FROM subjects JOIN schools WHERE school_id = $school_id AND subject_type = 'Tanya'";
				$table = 'points_codes';
				$names = 'points';
				$values = $tanya == 7 ? 7 : $tanya * 0.5;
				$points = $tanya * 0.5;
				$bonus = $tanya == 7 ? 3.5 : 0;
				$expires = unixtojd() + 180;
				$prefix = '1'; //1 prefix for point cards
				$description = sprintf(T_("Learned Tanya for %s days"), $tanya);
				$left_circle = $tanya;
				$right_circle = $tanya;
				$series = '';
			} 
			elseif (isset($mission_number) || isset($mission_number_series)) { //mission cards
				$sql = 'SELECT subject_name, mission_number, subject_id, subject_image_id, school_id, school_name, school_city, school_state, school_logo_id, school_number, mission_name, (SELECT IFNULL(SUM(points*mandatory_qty), 0) FROM date_tasks JOIN date_tasks_missions pm USING (date_tasks_mission_id) WHERE pm.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND is_bonus = 0) points' . ($is_bonus != 0 ? ", (SELECT IFNULL(SUM(points*mandatory_qty), 0) FROM date_tasks JOIN date_tasks_missions pm USING (date_tasks_mission_id) WHERE pm.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND is_bonus = $is_bonus) points_bonus" : '') . " FROM subjects JOIN schools JOIN school_type_subjects USING (subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id) WHERE subject_id = $subject_id AND school_id = $school_id AND subject_type != 'school_points' AND" . (isset($mission_number) ? " mission_number = $mission_number LIMIT 1" : " mission_number >= $mission_number_series AND mission_number < $mission_number_series+1 GROUP BY subject_name, mission_number, subject_id, subject_image_id, school_id, school_name, school_city, school_state, school_logo_id, school_number ORDER BY subject_name, mission_number");
				$table = 'date_tasks_mission_codes';
				$names = 'mission_number, include_bonus_task';
				$expires = unixtojd() + 60;
				$prefix = '4'; //4 prefix for date_task missions cards
			} 
			elseif (!is_null($medal_stage)) { // release cards
				$sql = "SELECT subject_name, subject_id, subject_image_id, school_id, school_name, school_city, school_state, school_logo_id, school_number FROM subjects JOIN schools WHERE school_id = $school_id AND subject_type = 'Tanya'";
				$table = 'tanya_medal_cards';
				$names = 'medal_stage';
				$values = $medal_stage * 25;
				$points = 25;
				$bonus = '';
				$expires = unixtojd() + 180;
				$prefix = '7'; //7 prefix for tanya release cards
				$description = T_("Checkpoint");
				$left_circle = $medal_stage;
				$right_circle = $medal_stage;
				$series = '';
			} 
			elseif ($subject_id != -1 && ($auth_mode != 'user' || $points_codes_template_id != -1)) { // point cards
				$sql = "SELECT subject_name, subject_id, subject_image_id, school_id, school_name, school_city, school_state, school_logo_id, school_number FROM subjects JOIN schools WHERE subject_id = $subject_id AND school_id = $school_id AND subject_type = 'school_points'";
				$table = 'points_codes';
				$left_circle = gr('left_circle');
				$right_circle = gr('right_circle');
				$description = gr('description');
				$series = gr('series');
				$names = 'points, left_circle, right_circle, description, series';
				$values = $points . ', ' . ms($left_circle) . ', ' . ms($right_circle) . ', ' . ms($description) . ', ' . nullif($series, '');
				$bonus = '';
				$expires = unixtojd() + 180;
				$prefix = '1'; //1 prefix for point cards
				$left_circle = $left_circle === '' ? '&nbsp;' : es($left_circle);
				$right_circle = $right_circle === '' ? '&nbsp;' : es($right_circle);
			} 
			else {
				$sql = 'SELECT NULL FROM dual WHERE 1=2'; //designed to return no rows
			}

			$result = mq($sql);
			$row = mysql_fetch_assoc($result);
			
			if (mysql_num_rows($result) > 1) {
				$result_back = mq($sql);
				$row_back = mysql_fetch_assoc($result_back);
			} 
			else {
				$row_back = $row;
			}
			
			?>
			
			<? if (mysql_num_rows($result)) : ?>
			<div class="noprint">	
				<H2>Printing Instructions</H2>		
				<p><img src="images/Print-Dialog-Small-2.jpg" align="right" /><img src="images/Print-Dialog-Small-1.jpg" align="right" />
										In your browser click 'File' then 'Page Setup...'</p>
										<p>Step 1: Set the Orientation to Portrait</p>
										<p>Step 2: Check 'Shrink to fit Page Width'</p>
										<p>Step 3: In Options check 'Print Background (colors & images)'</p>
										<p>Step 4: In the second tab set Top, Right, Left Margins to 0.5 inches</p>
										<p>Step 5: In the second tab set Bottom Margin to 0.0 inches</p>
										<p>Step 6: Set all Headers & Footers to Blank</p>
										<p>Note: The browser will save these preferences for later use.</p>								
			</div>
			
				<P class="noprint" style="text-align: center;">
					<INPUT type="button" value="<?=T_('Print')?>" onClick="print();">
				</P>
				
				<? if ($auth_mode == 'user') : ?>
			
						<FORM action="admin.php#grant" method="post" accept-charset="UTF-8">
							<P class="noprint" style="text-align: center;">
							<INPUT type="submit" value="<?=T_('Grant Card to Soldier')?>"> (<?=T_("Store card in Soldier's inbox")?>)
							<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
							</P>
				<? endif; ?> <!-- if ($auth_mode == 'user') -->
							<?
							
							if (mysql_result(mq("SELECT GET_LOCK('$table', 30)"),0) != 1) 
								trigger_error('could not get lock', E_USER_ERROR);
								
							for ($copy = 0; $copy < $copies*ceil(mysql_num_rows($result)/($lines*$cols)); $copy++):
							?>
							
							<? if ($fb == 'fb' || $fb == 'f') : ?>
							
							<TABLE class="fronts">
							
							<? for ($line = 0; $line < $lines; $line++) : ?>
							
							<TR class="row<?=($line+1)?>">
							
							<? for ($col = 0; $col < $cols; $col++) : ?>
							
							<TD>
							
							<?
							if ($row) {

								if (isset($mission_number) || isset($mission_number_series)) {
									$points = floatval($row['points']);
									$bonus = $is_bonus ? $row['points_bonus'] : 0;
									$description = $row['mission_name'];
									$left_circle = $row['mission_number'];
									$right_circle = $row['mission_number'];
									$series = '';
									$values = "{$row['mission_number']}, $is_bonus";
								} 
								elseif (!is_null($tanya) || !is_null($medal_stage)) {
									$subject_id = $row['subject_id'];
								}

								echo display_card_front($expires, $row['school_number'], $row['school_name'], $row['school_city'], $row['school_state'], $row['school_logo_id']);

							}
							
							?>
							
							</TD>
							
							<? 
								if (mysql_num_rows($result) > 1) 
									$row = mysql_fetch_assoc($result); 
							?>
							
							<? endfor; ?>
							
							</TR>
							
							<? endfor; ?>
							
							</TABLE>
							
							<?
							
							if (mysql_num_rows($result) > 1) {
							
								if (!$row) {
									mysql_data_seek($result, 0);
									$row = mysql_fetch_assoc($result);
								}
								
							}
							
							?>
							
							<? endif; ?> <!-- if ($fb == 'fb' || $fb == 'f') -->
							
							<HR>
							
							<? if ($fb == 'fb' || $fb == 'b') : ?>
							
							<TABLE class="backs">
							
							<? for ($line = 0; $line < $lines; $line++) : ?>
							
							<TR class="row<?=($line+1)?>">
							
							<? for ($col = 0; $col < $cols; $col++) : ?>
							
							<TD>
							
							<? if ($row_back) : ?>
							
							<?
							if (isset($mission_number) || isset($mission_number_series)) {
							  $points = floatval($row_back['points']);
							  $bonus = $is_bonus ? $row_back['points_bonus'] : 0;
							  $description = $row_back['mission_name'];
							  $left_circle = $row_back['mission_number'];
							  $right_circle = $row_back['mission_number'];
							  $series = '';
							  $values = "{$row_back['mission_number']}, $is_bonus";
							} 
							elseif (!is_null($tanya) || !is_null($medal_stage)) {
							  $subject_id = $row_back['subject_id'];
							}

							$count = 0;
							do {
							  if($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
							  $id = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
							} while(mysql_result(mq("SELECT COUNT(*) FROM $table WHERE code_id = $id"),0) != 0);

							mq("INSERT INTO $table (code_id, school_id, subject_id, $names, expiration_date) VALUES ($id, $school_id, $subject_id, $values, FROM_DAYS($expires-1721060))");

							$id = $prefix . str_pad($id, 19, '0', STR_PAD_LEFT);

							echo display_card_back($id, $points, $bonus, $left_circle, $right_circle, $description, $row_back['subject_name'], $row_back['subject_image_id'], $series);

							?>
							
							<? if ($auth_mode == 'user') : ?>
							
							<INPUT type="hidden" name="code[]" value="<?=$id?>">
							
							<? endif; ?> <!-- if ($auth_mode == 'user') : -->
							
							<? endif; ?> <!-- if ($row_back) : -->
							
							</TD>
							
							<? if(mysql_num_rows($result) > 1) $row_back = mysql_fetch_assoc($result_back); ?>
							
							<? endfor; ?> <!-- for ($col = 0; $col < $cols; $col++) : -->
							
							</TR>
							
							<? endfor; ?> <!-- for ($line = 0; $line < $lines; $line++) : -->
							
							</TABLE>
							

							
<?							
							if (mysql_num_rows($result) > 1) {
							
								if (!$row_back) {
									mysql_data_seek($result_back, 0);
									$row_back = mysql_fetch_assoc($result_back);
								}
								
							} 
?>

							
							<HR>
							
							<? endif; ?>
							
							<? endfor; ?>
							
							<? mq("SELECT RELEASE_LOCK('$table')"); ?>
							
							<? endif; ?> <!-- if ($auth_mode == 'user') -->
							
							<? if($auth_mode == 'user'): ?>
							
						</FORM>
						
			<? endif; ?> <!--  if (mysql_num_rows($result)): -->

			
			
			
			
			
			
			
			
			
		</DIV> <!-- body -->
		
		<DIV class="noprint">
			<? include('admin_footer.php'); ?>
		</DIV>
		
	</BODY>
	
</HTML>
