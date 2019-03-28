<?php
$admin_auth = array('user', 'school'); 
require('header.php');

$action = gr('action', 'edit');
$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
if ( isset( $_POST['user_id'] ) ) 
	$user_id = $_POST['user_id'];
else 
	$user_id = gri('user_id', -1);
session_start();
$_SESSION["child_id"] = $user_id;

//if ($auth_mode == 'user') 
	//check_school_setting($user_id, 'home_school');

//$user_row = mysql_fetch_assoc(mq("SELECT username, first, last, school_id, school_type_id, school_type_name FROM users LEFT JOIN school_types USING (school_type_id) WHERE user_id = $user_id AND school_id = $school_id"));
$user_row = mysql_fetch_assoc(mq("
	SELECT username, first, last, school_id, child_type_id, child_type_name, school_type_id, school_type_name 
	FROM users LEFT JOIN school_types USING (school_type_id) 
	LEFT JOIN child_types USING (child_type_id) 
	WHERE user_id = $user_id "));
if (!$user_row) 
	user_error('can not locate user', E_USER_ERROR);
	
$edit_result = false;

if (!empty($action)) {

	switch($action) {
		case 'edit':
		    $sql2 = "
            SELECT institutions.inst_name, subjects.subject_name, subjects.subject_id, user_tracks.track_id, user_tracks.level, enrolled 
            FROM subjects 
            JOIN school_subjects USING (subject_id) 
            JOIN school_type_subjects 
                ON (subject_type NOT IN ('school_points', 'home_points') 
                    AND subjects.subject_id = school_type_subjects.subject_id 
                    AND school_type_subjects.school_type_id = {$user_row['school_type_id']}) 
            LEFT JOIN user_tracks ON (subjects.subject_id = user_tracks.subject_id AND user_id = $user_id) 
            LEFT JOIN institutions USING (inst_id) 
            WHERE subject_type NOT IN ('school_points', 'home_points')  
            AND school_id = {$user_row['school_id']} 
            ORDER BY institutions.inst_name, subjects.subject_name";
            //echo $sql2;
			$edit_result = mq($sql2);
		 
		 /*
			$edit_result = mq("
			SELECT institutions.inst_name, subjects.subject_name, subjects.subject_id, user_tracks.track_id, user_tracks.level, enrolled 
			FROM subjects 
			JOIN school_subjects USING (subject_id) 
			JOIN user_tracks ON (subjects.subject_id = user_tracks.subject_id AND user_id = $user_id) 
			LEFT JOIN institutions USING (inst_id) 
			WHERE subject_type NOT IN ('school_points', 'home_points', 'Tanya') 
			AND school_id = {$user_row['school_id']} 
			ORDER BY institutions.inst_name, subjects.subject_name");
		  * 
		  */
		break;

		case 'edit2':
			//var_dump(gra('subject'));
			foreach(gra('subject') as $subject_id => $data) {
				$subject_id = intval($subject_id);
				$track = intval($data['track']);
				$level = max(6, min(intval($data['level']), 14));
				$enrolled = intval($data['enrolled']);
				
				if ($data['track'] == -1) 
					mq("DELETE FROM user_tracks WHERE user_id = $user_id AND subject_id = $subject_id");
				else 
					mq("INSERT INTO user_tracks SET user_id = $user_id, subject_id = $subject_id, track_id = $track, level = $level, enrolled = $enrolled ON DUPLICATE KEY UPDATE track_id = VALUES(track_id), level = VALUES(level), enrolled = VALUES(enrolled)");
				
				//if enrolling into yoma depagra we need to create birthday mission				
				if ($subject_id == 40) {
					require_once 'class.birthday.php';
					require_once 'class.birthdayYi.php';
					$b = new Birthday($user_id);
					$b->setBirthday();
					$b = new BirthdayYi($user_id);
					$b->setBirthday();
				}
				mq("DELETE FROM user_tracks USING user_tracks LEFT JOIN school_type_subjects ON (user_tracks.subject_id = school_type_subjects.subject_id AND school_type_subjects.school_type_id = {$user_row['school_type_id']}) WHERE user_id = $user_id AND school_type_subjects.subject_id IS NULL");
			}
			
			//$message = T_("Soldier's ladders edited");
			header("Location: admin_user_track.php?user_id=" . $user_id);
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
	}
	
}

$tracks_result = mq("SELECT track_id, track_name FROM tracks ORDER BY CAST(track_name AS SIGNED), track_name");
$subject_tracks_result = mysql_fetch_column_tuple(mq("SELECT subject_id, track_id FROM date_tasks_missions GROUP BY subject_id, track_id ORDER BY subject_id"));
$subject_level_result = mysql_fetch_column(mq("SELECT subject_id, MIN(level) min_level, MAX(level) max_level FROM date_tasks_missions GROUP BY subject_id ORDER BY subject_id"));

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_("Soldier's Ladders/Years"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
// Popup window code
function newPopup(url) {
	popupWindow = window.open(
		url,'popUpWindow','height=400,width=200,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes')
}
</script>		
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<DIV CLASS="body <?=$align_start?>">
		
			<H1><?=T_("Soldier's Ladders/Years")?></H1>
			
			<DIV class="infobox">
				<?=T_('Instructions')?>
				<BR>
<!--				<DL>
					<DT>
						<?=T_('For all campaigns except Tehillim and Tanya choose from ladder 1 or 2')?>
						<DD>
							<OL style="margin: 0px; padding: 0px;">
								<LI><?=T_('Ladder One = Basic missions, suggested fro children up to grade 2')?>
								<LI><?=T_('Ladder Two = Advanced missions, suggested for children grade 3 and up')?>
							</OL>
				</DL>-->
				<P>
								<br />Click <a href="JavaScript:newPopup('http://www.mashpia.com/chart.html');">here</a> to use a chart to help you 
								decide what year to put your child on to.
				</P>
				<?//=sprintf(T_('For Tehillim please %sprint the yearly growth plan%s for your grade and get each child to choose from one of ten ladders that will work best for them.'), "<A href='admin_print_pdf.php?school_id=$school_id&amp;action=print&amp;class_id=$class_id&amp;user_id=$user_id&amp;type=tbp_growth_planner'>", '</A>')?>			
			</DIV>
			
			<? if (!empty($message)):?>
				<H2><?=$message?></H2>
			<?endif;?>
			
			<? if (gr('back')) : ?>
				<A HREF="<?=es(gr('back'))?>school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;user_id=<?=$user_id?>"><?=es(gr('back_text'))?></A>
			<? elseif ($auth_mode != 'user') : ?>
			<? //elseif (empty($admin_user['auths']['user'])) : ?>
				<A HREF="admin_user.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_('Back to Soldier list')?></A>
			<? endif; ?>
			
			<? if ($user_row && $edit_result) : ?>
				<H2>
					<?=es(sprintf(T_('For: %s %s'), $user_row['first'], $user_row['last']))?>
				</H2>
				
				<P>
					<?=T_('Mission Type')?>: <?=es($user_row['child_type_name'])?>
					<BR>
				</P>
				
				<FORM action="admin_user_track.php" method="post" accept-charset="UTF-8" name="user_tracks">
					<DIV>
						<INPUT type="hidden" name="action" value="edit2">
						<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
						<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
						<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
						
						<TABLE border=1 cellpadding=2>
							<TR>
								<TH><?=T_('Campaign')?></TH>
								<TH><?=T_('Enrolled')?></TH>
								<TH><?=T_('Ladder')?></TH>
								<TH><?=T_('Year')?> (6 - 14)</TH>
							</TR>
							<!--
							<TR>
								<TH><?=T_('Change all')?>:</TH>
								<TH></TH>
								<TH>
									<SELECT name="track_all">
										<OPTION value="-1">&lt;<?=T_('Campaign Disabled')?>&gt;
										<?while($track_row = mysql_fetch_assoc($tracks_result)):?>
										<?if ($track_row['track_id'] != 1) continue;?>
										<OPTION value="<?=$track_row['track_id']?>"><?=es($track_row['track_name'])?></OPTION>
										<?endwhile;?>
										<?mysql_data_seek($tracks_result, 0);?>
									</SELECT>
									<BR>
									<INPUT type="button" value="<?=T_('Change All')?>" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('subject')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[track]')==this.form.elements[i].name.length-7) this.form.elements[i].selectedIndex = this.form.elements['track_all'].selectedIndex;}">
								</TH>
								<TH>
									<INPUT type="text" name="level_all" maxlength="2" size="2" onChange="this.value = Math.max(6, Math.min(parseInt('0'+this.value, 10), 14));"><BR><INPUT type="button" value="<?=T_('Change All')?>" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('subject')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[level]')==this.form.elements[i].name.length-7) { this.form.elements[i].value = this.form.elements['level_all'].value; this.form.elements[i].onchange();}}">
								</TH>
							</TR>
							-->
							<?while($row = mysql_fetch_assoc($edit_result)):?>
							<TR>
								<?if (in_array($row['subject_id'], array(15, 91))) continue;?>
								<TD><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></TD>
								<TD STYLE="text-align: center;"><INPUT type="hidden" name="subject[<?=$row['subject_id']?>][enrolled]" value="0"><INPUT type="checkbox" name="subject[<?=$row['subject_id']?>][enrolled]" value="1" <?=$row['enrolled'] ? 'CHECKED' : ''?>></TD>
								<TD>
									<?// $disable = ""; if ($row['subject_id'] == 1) $disabled=""; else $disabled=" disabled='disabled'"; ?>
									<SELECT name="subject[<?=$row['subject_id']?>][track]"<?//=$disabled;?> disabled>
										<OPTION value="-1">&lt;<?=T_('Campaign Disabled')?>&gt;
										<?while($track_row = mysql_fetch_assoc($tracks_result)):?>
										<?if(isset($subject_tracks_result[$row['subject_id']]) && !isset($subject_tracks_result[$row['subject_id']][$track_row['track_id']])) continue;?>
										<? if ($row['subject_id'] != 1 && $track_row['track_id'] != 1) continue; ?>
										<? if ($row['subject_id'] == 1) : ?>
											<? if (in_array($track_row['track_id'], array(1,2,8,9,10))) continue; ?>
										<? endif; ?>
										<OPTION value="<?=$track_row['track_id']?>" <?=$track_row['track_id'] == $row['track_id'] ? 'SELECTED' : ''?>><?=es($track_row['track_name'])?></OPTION>
										<?endwhile;?>
										<?mysql_data_seek($tracks_result, 0);?>
									</SELECT>
									<? //else : ?>
									<?//=$row['track_id'];?>
									<? //endif; ?>
								</TD>
								<TD STYLE="text-align: <?=$align_end?>;">
									<?	
									$str = '';
									if (isset($subject_level_result[$row['subject_id']])) {
										$min = $subject_level_result[$row['subject_id']]['min_level'];
										if ($min < 6) 
											$min = 6;
										$str = "(" . $min . " - " . $subject_level_result[$row['subject_id']]['max_level'] . ")";
									}
									echo $str;
									?>
										<INPUT type="text" name="subject[<?=$row['subject_id']?>][level]" value="<?=es($row['level'])?>" maxlength="2" size="2" onChange="this.value = Math.max(6, Math.min(parseInt('0'+this.value, 10), 14));"> <A HREF="#" onClick="el=document.forms['user_tracks'].elements['subject[<?=$row['subject_id']?>][level]']; el.value=Math.max(6, Math.min(parseInt('0'+el.value, 10)+1, 14)); return false;">+</A> <A HREF="#" onClick="el=document.forms['user_tracks'].elements['subject[<?=$row['subject_id']?>][level]']; el.value=Math.max(6, Math.min(parseInt('0'+el.value, 10)-1, 14)); return false;">&minus;</A></TD>
							</TR>
							<?
							if ($row['subject_id'] == 1) {
							    if ($row['level'] && $row['track_id']) {
    								$sql = "
    									select name, quantity from date_tasks dt 
    									join date_tasks_missions dtm using (date_tasks_mission_id) 
    									where dtm.subject_id = 1 
    									and dtm.level = " . $row['level'] . " 
    									and dtm.track_id = " . $row['track_id'] . " 
    									group by name, quantity";
    								$result = mysql_query($sql);
    								while ($row = mysql_fetch_assoc($result)) {
    									if ($row['name'] == "How many Kapitlach did you say?") {
    										$kapitlach = $row['quantity'];
    									} else if ($row['name'] == "How many minutes did you spend saying תהלים?") {
    										$minutes = $row['quantity'];
    									}
    								}
    								if (isset($kapitlach) && isset($minutes))
    									echo "<tr><td colspan=4><i>Quota:</i> $kapitlach kapitlach; $minutes minutes <br /><i>Please note: when changing ladder/year new quota will only show after saving</i></td></tr>";
								}
							}
							?>
							<?endwhile;?>
						</TABLE>
						
						<P>
							<INPUT type="submit" value="<?=T_('Save')?>" onclick="alert('You cannot change any ladders / years in middle of the term.'); return false;">
						</P>
					</DIV>
				</FORM>
				
	<? endif; ?>
	</DIV>
	<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
