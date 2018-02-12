<?
//outputs text directly

function ui_banner() {
  global $ui_type, $align_start;

  switch($ui_type):
    case 'school':
?>
<DIV class="ui_school <?=$align_start?>">

	<DIV class="body">
	
		<DIV class="sub_menu">
			<BR><BR><BR><BR><BR>
		</DIV>
<?
    break;
  endswitch;
}

function ui_menu() {
	global $ui_type, $school_id, $align_start, $user_type, $camp_id, $admin;

	$url_id = isset($school_id) ? "?school_id=$admin->school_id" : '';
	
	$url_id2 = $url_id === '' ? '?' : $url_id . '&amp;';

	switch($ui_type):
		
		case 'camp':
			$msg = T_("You must choose a camp first.");
			?>
			<dl>
				<dt><?=T_('Camp Members')?>
					<dd>
						<ul>
							<ul>
								<li><a href="admin_camp_members.php"><?=T_('View / Edit')?></a>
								<? if ($user_type == "super") : ?>
								<!--<li><a onclick="return check_camp(); function check_camp() { if (document.camps_form.camp_id.value < 2) { alert('<?=$msg;?>'); return false; } else { document.camps_form.action.value='add_new'; document.camps_form.submit(); } } " href="#"><?//=T_('Add new')?></a>-->
								<li><a onclick="document.forms['members_form'].elements[1].value='add_new'; document.forms['members_form'].submit();" href="#"><?=T_('Add new')?></a>
								<? else : ?>
								<li><a href="admin_camp_members.php?action=add_new&camp_id=<?=$camp_id;?>"><?=T_('Add new')?></a>
								<? endif; ?>
							</ul>
						</ul>
			</dl>
			<?
		break; // camps
	
		case 'school':
?>

<DL>
	<DT><?=T_('Students (Soldiers)')?>
		<DD>
			<UL>
				<UL>
					<LI><A HREF="admin_user.php<?=$url_id?>"><?=T_('View / Edit')?></A>
				</UL>
				
				<LI><?=T_('Add New')?>					
				<UL>
					<LI><A HREF="admin_user.php<?=$url_id2?>action=add"><?=T_('Individual')?></A>
					<LI><A HREF="admin_school_file.php<?=$url_id?>"><?=T_('Upload School / Class')?></A>
				</UL>
					
				<LI><?=T_('Photos')?>				
				<UL>
					<LI><A HREF="admin_users_photo.php<?=$url_id?>"><?=T_("Upload Photos")?></A>
				</UL>
				
				<LI><?=T_('Enrollment')?>				
				<UL>
					<LI><A HREF="admin_users_register.php<?=$url_id?>"><?=T_("TH 5770")?></A>
					<LI><A HREF="admin_users_subject.php<?=$url_id?>"><?=T_("Campaign Enrollment")?></A>
				</UL>
				
				<LI><?=T_('Rank Cards')?>
				<UL>
					<LI><A HREF="admin_card_print.php<?=$url_id?>"><?=T_('Print')?></A>
				</UL>
				
				<LI><?=T_('Settings')?>
				<UL>
					<LI><A HREF="admin_user_kiosk.php<?=$url_id?>"><?=T_('Kiosk Mission Entry')?></A>
				</UL>
				
			</UL>
			
  <DT><?=T_('Teachers')?>
  <DD>
    <UL>
      <LI><A HREF="admin_admin.php<?=$url_id?>"><?=T_('Manage Admins')?></A>
    </UL>
  <DT><?=T_('School (Base)')?>
  <DD>
    <UL>
      <LI><A HREF="admin_school.php<?=$url_id2?>action=edit"><?=T_('Edit Profile')?></A>
    </UL>
  <DT><?=T_('Classes (Platoons)')?>
  <DD>
    <UL>
      <LI><A HREF="admin_class.php<?=$url_id?>"><?=T_('Manage')?></A>
      <LI><A HREF="admin_class.php<?=$url_id2?>action=add"><?=T_('Add New')?></A>
      <LI><A HREF="admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></A>
    </UL>
  <DT><?=T_('Teams (Squads)')?>
  <DD>
    <UL>
      <LI><A HREF="admin_team.php<?=$url_id?>"><?=T_('Manage')?></A>
      <LI><A HREF="admin_team.php<?=$url_id2?>action=add"><?=T_('Add New')?></A>
    </UL>

  <DT>&nbsp;
  <DD>
  <UL>
    <LI><A HREF="admin_invoice_items.php<?=$url_id?>"><?=T_('Invoice')?></A>
    <LI><A HREF="admin_received_stats.php<?=$url_id?>"><?=T_('Mark Ranks and Medals as Received')?></A>
    <LI><A HREF="admin_scan.php<?=$url_id?>"><?=T_('Scan Vouchers')?></A>
    <LI><A HREF="admin_withdraw.php<?=$url_id?>"><?=T_("Show/Cash Existing Soldiers' Vouchers")?></A>
    <LI><A HREF="admin_user_withdraw.php<?=$url_id?>"><?=T_("Create Soldiers' Vouchers")?></A>
  </UL>
</DL>
<?
		break; // school //

		case 'programs':
			global $subject_id;

			if (!isset($subject_id) || $subject_id == -1) :
?>
<DL>

<? $result = mq("SELECT subjects.subject_id, subject_name, subject_type FROM subjects JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE subject_type NOT IN ('school_points', 'home_points') ORDER BY subject_name"); ?>

<? if (mysql_num_rows($result)) : ?>
<DT><?=T_('My Campaigns - Army wide')?>
<DD>
<UL>
<? while($row = mysql_fetch_assoc($result)): ?>
<LI><A HREF="admin_todo_subject.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></A>
<? endwhile; ?>
</UL>
<? endif; ?> <!-- (mysql_num_rows($result)) : -->

<? $result = mq("SELECT subjects.subject_id, subject_name, subject_type FROM subjects JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE subject_type IN ('school_points', 'home_points') ORDER BY subject_name"); ?>

<? if (mysql_num_rows($result)) : ?>
<DT><?=T_('My Campaigns - base campaigns')?>
<DD>
<UL>
<? while($row = mysql_fetch_assoc($result)): ?>
<LI><A HREF="admin_todo_subject.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></A>
<? endwhile; ?>
</UL>
<? endif; ?> <!-- if (mysql_num_rows($result)) : -->

<? $result = mq("SELECT subjects.subject_id, subject_name FROM subjects LEFT JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE school_subjects.subject_id IS NULL AND subject_type NOT IN ('school_points', 'home_points') ORDER BY subject_name"); ?>
<? if (mysql_num_rows($result)) : ?>
<DT><?=es(T_('View & Enroll - Army wide'))?>
<DD>
<A HREF="#" onClick="document.getElementById('global_list').style.display = ''; document.getElementById('global_show').style.display = 'none'; return false;" id="global_show" style="padding-<?=$align_start?>: 2em;"><?=T_('Show')?></A>
<UL id="global_list" style="display: none;">
<? while($row = mysql_fetch_assoc($result)): ?>
<LI><A HREF="admin_school_subjects.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></A>
<? endwhile; ?>
</UL>
<? endif; ?> <!-- if (mysql_num_rows($result)) : -->

<? $result = mq("SELECT subjects.subject_id, subject_name FROM subjects LEFT JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE school_subjects.subject_id IS NULL AND subject_type IN ('school_points', 'home_points') ORDER BY subject_name"); ?>
<? if(mysql_num_rows($result)): ?>
<DT><?=es(T_('View & Enroll - base campaigns'))?>
<DD>
<A HREF="#" onClick="document.getElementById('local_list').style.display = ''; document.getElementById('local_show').style.display = 'none'; return false;" id="local_show" style="padding-<?=$align_start?>: 2em;"><?=T_('Show')?></A>
<UL id="local_list" style="display: none;">
<? while($row = mysql_fetch_assoc($result)): ?>
<LI><A HREF="admin_school_subjects.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></A>
<? endwhile; ?>
</UL>
<? endif; ?>
</DL>
<? else: ?>
<? $subject = mysql_fetch_assoc(mq("SELECT subject_name, subject_type, school_subjects.subject_id enrolled FROM subjects JOIN schools USING (inst_id) LEFT JOIN school_subjects USING (subject_id, school_id) WHERE subjects.subject_id = $subject_id AND school_id = $school_id")); ?>
<UL>
<LI><A HREF="admin_todo_subject.php<?=$url_id?>"><?=T_('Back to Campaign list')?></A>
</UL>
<DL>
<DT><?=es($subject['subject_name'])?>
<DD>
<UL>
<?if(!$subject['enrolled']):?>

<LI><A HREF="admin_school_subjects.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Enroll')?></A>

<? else: ?>

<? $result = mq("SELECT category_name, category_id, (SELECT COUNT(*) FROM todo_list LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'school' AND todo_list_marks.id = $school_id) WHERE visibility != 'none' AND todo_list.school_id IS NULL AND todo_list.recip = 'school' AND (todo_list.recip_id = $school_id OR todo_list.recip_id IS NULL) AND mark_date IS NULL AND todo_list.category_id = todo_categories.category_id) num FROM todo_categories WHERE subject_id = $subject_id"); ?>
<LI><A HREF="admin_todo_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Todo List')?></A>
<? if(mysql_num_rows($result)): ?>
<UL>
<? while($row = mysql_fetch_assoc($result)): ?>
<LI>(<?=$row['num']?>) <A HREF="admin_todo_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;category_id=<?=$row['category_id']?>"><?=es($row['category_name'])?></A>
<? endwhile; ?>
</UL>
<? endif; ?>

<?
      switch($subject['subject_type']) {
        case 'school_points':
?>
<LI><A HREF="admin_points_print.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Print Achievement Cards')?></A>
<?
		break; // programs

		case 'Tanya':
?>
<DL class="one_counter">
<DT><?=T_('Tanya Setup')?>
<DD>
<OL>
<LI><A HREF="admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=introduction"><?=T_('Introduction')?></A>
<LI><A HREF="admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=year"><?=T_('Year')?></A>
<LI><A HREF="admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=dates"><?=T_('Program Dates')?></A>
</OL>
<DT><?=T_('Tanya Launch')?>
<DD>
<OL>
<LI><A HREF="admin_users_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_("Solder's Enrollment")?></A>
<LI><A HREF="admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=ladder"><?=T_('Ladder Setup')?></A>
</OL>
<DT><?=T_('Tanya Management')?>
<DD>
<UL>
<LI><?=T_('CD')?>
<OL>
<LI><A HREF="http://www.anash.com/Perek_Aleph.zip"><?=T_('Rabbi Garelik')?></A>
<!-- <LI><A HREF=""><?=T_('Mrs Katz')?></A> -->
</OL>
<LI><?=T_('Reports')?>
<OL>
<!-- <LI><A HREF="admin_print_pdf.php<?=$url_id2?>type=tbp_yearly_progress"><?=T_('Tanya Baal Peh Yearly Progress Chart')?></A> -->
<LI><A HREF="admin_print_pdf.php<?=$url_id2?>type=tbp_progress_report_post"><?=T_('Tanya Baal Peh Weekly Quota Report')?></A>
<LI><A HREF="admin_print_pdf.php<?=$url_id2?>type=tbp_monthly_quota_post"><?=T_('Tanya Baal Peh Monthly Quota Report')?></A>
</OL>
<LI><?=T_('Mileage card')?>
<OL>
<LI><A HREF="admin_tanya_cards.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Print Achievement Cards')?></A>
</OL>
<LI><?=T_('Testing')?>
<OL>
<LI><A HREF="admin_tanya_lines_print.php<?=$url_id?>"><?=T_('Print Tanya log sheet/bar-codes')?></A>
<LI><A HREF="admin_tanya_cards.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Checkpoint mileage')?></A>
</OL>
<LI><?=T_('Modify Ladders')?>
<OL>
<LI><A HREF="admin_print_pdf.php<?=$url_id2?>type=tbp_growth_planner"><?=T_('Tanya Baal Peh Growth Planner')?></A>
<LI><A HREF="admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=ladder_only"><?=T_('Manage Ladders')?></A>
</OL>
<LI><?=T_("Levi's Toah")?>
<OL>
<LI><A HREF="admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=pledge"><?=T_('Enter Pledges and Income')?></A>
</OL>
</UL>
<DT><?=T_('Tanya Setup')?>
<DD>
<OL>
<LI><A HREF="http://www.mashpia.com/file_view.php?id=950252724&amp;m=d"><?=T_('Tanya with Nekudos')?></A>
</OL>
</DL>
<?
		break; // Tanya
		
        default:
?>
<LI><A HREF="admin_points_print.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Print Achievement Cards')?></A>
<LI><A HREF="admin_users_track.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_("Manage Soldier's Ladders/Years")?></A>
<LI><A HREF="admin_users_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_("Solder's Enrollment")?></A>
<?
        break;
      }
?>
</UL>
<UL>
<LI><A HREF="admin_school_subjects.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Un-Enroll')?></A>
</UL>
<? endif; ?>
</DL>
<? endif; ?>
<?
    break;

    case 'reports':
?>
<DL>
  <DT><?=T_('Soldier Reports')?>
  <DD>
    <UL>
      <LI><?=T_('Rank')?>
        <UL>
          <LI><A HREF="admin_stats.php<?=$url_id2?>subjects=army&amp;start_date=0&amp;cols[s]=1&amp;cols[m]=1&amp;cols[r]=1&amp;order_by=.r&amp;registered_only=1&amp;report_type=rank&amp;view=1"><?=T_('Base Rank Report')?></A>
          <LI><A HREF="admin_stats.php<?=$url_id2?>subjects=army&amp;start_date=0&amp;cols[s]=1&amp;cols[m]=1&amp;cols[r]=1&amp;order_by=cr&amp;report_type=rank_class&amp;view=1"><?=T_('Platoon Rank Report')?></A>
        </UL>
      <LI><?=T_('Mileage')?>
        <UL>
          <LI><A HREF="admin_stats.php<?=$url_id2?>subjects=all&amp;start_date=<?=chaiElul()?>&amp;cols[p]=1&amp;cols[y]=1&amp;cols[u]=1&amp;cols[w]=1&amp;order_by=.y&amp;registered_only=1&amp;report_type=points&amp;view=1"><?=T_('Base Mileage Report')?></A>
          <LI><A HREF="admin_stats.php<?=$url_id2?>subjects=all&amp;start_date=<?=chaiElul()?>&amp;cols[p]=1&amp;cols[y]=1&amp;cols[u]=1&amp;cols[w]=1&amp;order_by=cy&amp;report_type=points_class&amp;view=1"><?=T_('Platoon Mileage Report')?></A>
        </UL>
      <LI><?=T_('Create')?>
        <UL>
          <LI><A HREF="admin_stats.php<?=$url_id?>"><?=T_('Create your own Miles and Stats Report')?></A>
        </UL>
<!--       <LI><A HREF="admin_mission_report.php<?=$url_id?>"><?=T_('Mission Report')?></A> -->
<!--       <LI><A HREF="admin_medal_report.php<?=$url_id?>"><?=T_('Medal Report')?></A> -->
<!--       <LI><A HREF="admin_rank_report.php<?=$url_id?>"><?=T_('Rank Report')?></A> -->
    </UL>
</DL>
<?
    break;

  endswitch;
}

function ui_menu_end() {
  global $ui_type;
?>
<BR style="clear: both;">
</DIV>
</DIV>
<?
}

function ui_tail() {
  global $ui_type;
?>
</DIV>
</DIV>
<?
}

function school_selector($school_id) {


}
?>
