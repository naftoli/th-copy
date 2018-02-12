<?
if (!isset($_POST["child_id"]) || !isset($_POST["school_id"])) {
	 header("Location: admin.php");
}

$admin_auth = array('user'); 
$ui_type = 'child';

 
require('header.php');
require('file_save.php');
require('calendar.php');

$user_id = gri('child_id', -1);
$child_id = gri('child_id', -1);

if ($user_id == -1) $user_id = $_POST["child_id"];
$school_id = $_POST["school_id"];

$message = "";
if (isset($_POST["action"])) {
	$action = $_POST["action"];
	
	if ($action == "update") {	
		if (strlen($_POST["dob_month"]) == 1)
			$dob_month = "0" . ($_POST["dob_month"] + 1);
		else 
			$dob_month = ($_POST["dob_month"] + 1);
			
		if (strlen($_POST["dob_day"]) == 1)
			$dob_day = "0" . $_POST["dob_day"];
		else 
			$dob_day = $_POST["dob_day"];
		
		$dob = $_POST["dob_year"] . "-" . $dob_month . "-" . $dob_day;

		$sql = "UPDATE users SET first='" . mysql_real_escape_string($_POST['first'])  . "', last='" . mysql_real_escape_string($_POST['last'])  . "', email='" . mysql_real_escape_string($_POST['email'])  . "', first_he='" . mysql_real_escape_string($_POST['first_he'])  . "', last_he='" . mysql_real_escape_string($_POST['last_he'])  . "', user_address1='" . mysql_real_escape_string($_POST['user_address1'])  . "', user_address2='" . mysql_real_escape_string($_POST['user_address2'])  . "', user_city='" . mysql_real_escape_string($_POST['user_city'])  . "', user_state='" . mysql_real_escape_string($_POST['user_state']) . "', user_postal='" . mysql_real_escape_string($_POST['user_postal']) . "', user_country='" . mysql_real_escape_string($_POST['user_country']) . "', user_phone='" . mysql_real_escape_string($_POST['user_phone']) . "', dob='" . $dob . "' WHERE user_id=" . $user_id;
		$query = mysql_query($sql);
		if (!$query)
			$message = "<span style='color:red;'>Child was not updated. Please try again.</span>";
	}
}


include("camps/includes/classes/user.php");
include("camps/includes/classes/school.php");
include("camps/includes/classes/school_class.php");
$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);
$user->get_school_class();

$classes = array();
$sql = "SELECT * FROM classes WHERE school_id=" . $school_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$class = new school_class($row);
	array_push($classes, $class);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=$user->first.' '.$user->last.'\'s '?><?=T_('To-Do List')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
		</style>
		
		<script>
			var month_days = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			
			function set_month_days(mnth_slctbx) {
				var max_days = month_days[mnth_slctbx.selectedIndex];
				var day_selectbox = document.getElementById("dob_day");
				
				if (max_days > day_selectbox.length) {
					for (dno = day_selectbox.length; dno < max_days; dno++) {
						day_selectbox.options[day_selectbox.length] = new Option((dno + 1), (dno + 1));
					}
				}
				else if (max_days < day_selectbox.length) {
					var difference = max_days - day_selectbox.length;
					for (dno = difference; dno < 0; dno++) {
						var index_number = day_selectbox.length - 1;
						day_selectbox.remove(index_number);
					}
				}
			}

		</script>		
	</HEAD>
	
	<BODY>
		<? include("admin_header.php"); ?>
		
		<DIV>
		
			<DIV class="body">
			
				<H1><?=$user->first.' '.$user->last.'\'s '?><?=T_('To-Do List')?></H1>
				
				<div class="content">
					
					
					<? if ($message != ""):?>
						<h1><?=$message;?></h1>
					<?endif;?>
					
						
<? foreach ($admin_user['auths']['user'] as $user_id) : 

	//-- if ($child_id == $user_id) --//
	if (($child_id > 0 && $child_id == $user_id) || ($child_id == 0)) :
	
	unset($report_id);
	
	if(($action = gr('action')) && (gri('todo_user_id') == $user_id || gri('code_user_id') == $user_id)) switch($action) {
	
		case 'report_print':
			if(!isset($do)) $do = 'print_date = NOW()';
			
		case 'report_unprint':
			if(!isset($do)) $do = 'print_date = NULL';
			
		case 'report_processed':
			if(!isset($do)) $do = 'process_date = NOW()';
			
		case 'report_unprocess':
			if(!isset($do)) $do = 'process_date = NULL';

			$report_id = gri('report_id', -1);
		
			if(mysql_result(mq("SELECT COUNT(*) FROM reports WHERE report_id = $report_id"), 0))
				mq("INSERT INTO report_marks SET report_id = $report_id, auth = 'user', id = $user_id, $do ON DUPLICATE KEY UPDATE $do");
				
			unset($do);
		break;

		case 'todo_mark':
			$todo_id = gri('todo_id', -1);
			if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
				mq("INSERT IGNORE INTO todo_list_marks SET todo_id = $todo_id, auth = 'user', id = $user_id");
		break;

		case 'todo_unmark':
			$todo_id = gri('todo_id', -1);
			if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
				mq("DELETE FROM todo_list_marks WHERE todo_id = $todo_id AND auth = 'user' AND id = $user_id");
		break;

		case 'del_code':
			mq("DELETE FROM user_codes USING user_codes JOIN admin_auths ON (user_id = id AND auth = 'user') WHERE admin_auths.admin_id = {$admin_user['admin_id']} AND user_id = " . gri('code_user_id', -1) . ' AND code_id = ' .  ms(gr('code_id')) . ' AND code_id_prefix = ' . gri('code_id_prefix', -1) . ' AND user_codes.admin_id = ' . gri('code_admin_id', -1));
		break;
}

if (($codes = gra('code')) && gri('user_id') == $user_id) foreach($codes as $code) {
	mq("INSERT IGNORE INTO user_codes (user_id, code_id, code_id_prefix, admin_id) VALUES ($user_id, " . preg_replace('/\D/', '', substr($code, 1)) . ', ' . intval(substr($code, 0, 1)) . ", {$admin_user['admin_id']})");
	$stored_code = true;
} 
else {
  $stored_code = false;
}
?>


							<? $user_row = mysql_fetch_assoc(mq("SELECT school_settings FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (school_id, class_id) LEFT JOIN admin_auths ON (user_id = id) LEFT JOIN roles USING (role_id) WHERE admin_id = {$admin_user['admin_id']} AND user_id=" . $user->user_id)); ?>



							<? $school_settings = explode(',', $user_row['school_settings']); ?>


							<? if(in_array('home_school', $school_settings)): ?>
							<H2 id="todo_list_user_<?=$user_id?>"><?=$user->first.' '.$user->last.'\'s '?><?=T_('To-Do list')?></H2>
							<? $view_all = gri('view_all', 0); ?>

							<P>
							<A HREF="admin_parent_todo.php?view_all=<?=!$view_all?>#todo_list_user_<?=$user_id?>"><?= $view_all ? T_('List only unfinished to-do items') : T_('List all to-do items') ?>&raquo;</A>
							</P>
							<TABLE class="list list_<?=$align_start?>">
							<THEAD>
							<TR>
							  <TH><?=T_('Priority')?></TH>
							  <TH><?=T_('Due Date')?></TH>
							  <TH><?=T_('Description')?></TH>
							  <TH><?=T_('View/Print')?></TH>
							  <TH><?=T_('Complete')?></TH>
							</TR>
							</THEAD>

							<? $result = mq("SELECT reports.report_id, report_name, report_type, start_date, end_date, print_date, process_date, visibility FROM reports LEFT JOIN report_marks ON (reports.report_id = report_marks.report_id AND id = $user_id AND auth = 'user') WHERE (report_type = 'WWTC' OR report_type = 'mission_cover_sheet') AND visibility != 'none'" . ($view_all ? '' : " AND ((print_date IS NULL AND visibility != 'process') OR process_date IS NULL)") . ' ORDER BY creation_date, report_name, reports.report_id'); ?>

							<TBODY>
							  <TR>
							    <TH colspan="4"><A HREF="#cat_user_<?=$user_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" onClick="var el = document.getElementById('cat_user_<?=$user_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN><?=isset($report_id) ? '&minus;' : '+'?></SPAN> &lt;<?=T_('Reports')?>&gt;</A></TH>
							    <TH><?=sprintf(T_('%d items'), mysql_num_rows($result))?></TH>
							  </TR>
							</TBODY>

							<TBODY id="cat_user_<?=$user_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" style="<?=!isset($report_id) ? 'display: none;' : ''?> border-top: none;">
							<?while($row=mysql_fetch_assoc($result)):?>

							<TR>
							<TD></TD>
							<TD></TD>
							<TD><?=es($row['report_name'])?></TD>
							<TD>
								<? if (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel') : ?>
									<? if ($row['visibility'] != 'process') : ?>
										<A HREF="<?=reportTypeURL_view($row['report_type'], 'user', $user_id, $row['start_date'], $row['end_date'])?>"><?=T_('View and print Report')?></A>
									<? else: ?>
										<!--<?=T_('Not available for printing')?><BR>-->
									<? endif; ?> <!-- if ($row['visibility'] != 'process') : -->
									
							  <? endif; ?> <!-- if (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel') : -->
  
								<? if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : ?>
									<A HREF="<?=reportTypeURL_mark($row['report_type'], 'user', $user_id, $row['start_date'], $row['end_date'])?>">
										<?=T_('Mark Mission')?>
									</A>
								<? endif; ?> <!-- if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : -->
							</TD>
							<TD>
								<? if ($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')) : ?>
									<?=is_null($row['print_date']) ? "<A HREF='admin_parent_todo.php?action=report_print&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Mark as printed') . '</A>' : T_('Printed on') . " {$row['print_date']}<BR><A HREF='admin_parent_todo.php?action=report_unprint&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Unmark as printed') . '</A>' ?>
									<BR>
								<? endif; ?> <!-- if ($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')) :  -->
								<? if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : ?>
									<?=is_null($row['process_date']) ? "<A HREF='admin_parent_todo.php?action=report_processed&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Mark as done') . '</A>' : T_('Processed on') . " {$row['process_date']}<BR><A HREF='admin_parent_todo.php?action=report_unprocess&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Unmark as done') . '</A>' ?>
								<? endif; ?> <!-- if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : -->
							</TD>
							</TR>

							<?endwhile;?>
							</TBODY>

<?
$result = mq("SELECT todo_list.todo_id, todo_text, todo_priority, category_name, category_id, subject_name, todo_due_date, todo_file_id, todo_url, mark_date, todo_list.recip_id FROM users JOIN todo_list LEFT JOIN todo_categories USING (category_id) LEFT JOIN subjects USING (subject_id) LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'user' AND todo_list_marks.id = $user_id) WHERE (subject_id IN (SELECT subject_id FROM user_tracks WHERE user_id = $user_id AND enrolled = 1) OR subject_id IS NULL) AND user_id = $user_id AND visibility != 'none' AND todo_list.school_id = users.school_id AND todo_list.recip = 'user' AND (todo_list.recip_id = $user_id OR todo_list.recip_id IS NULL)" . ($view_all ? '' : ' AND mark_date IS NULL') . ' ORDER BY subject_name, category_name, todo_priority, todo_due_date, todo_text, creation_date, todo_list.todo_id');

$row = $old_row = mysql_fetch_assoc($result);

if($row) do {
  $count = 0;
  ob_start();

  do {
    $count++;
    if(isset($todo_id) && $row['todo_id'] == $todo_id) $this_todo = true;
    $old_row = $row;
    $cat = $row['category_id'];
?>
<TR>
<TD><?=$row['todo_priority']?></TD>
<TD><?=es(dateToHebrew($row['todo_due_date']))?></TD>
<TD><?=is_null($row['recip_id']) ? '' : $s = '* ', es($row['todo_text'])?></TD>
<TD><?if(!is_null($row['todo_file_id'])):?><A HREF="file_view.php?id=<?=$row['todo_file_id']?>&amp;m=d"><?=T_('View/Print File')?>&raquo;</A><?endif;?> <?if($row['todo_url']):?><A HREF="<?=es($row['todo_url'])?>"><?=T_('Goto Link')?>&raquo;</A><?endif;?></TD>
<TD><?=is_null($row['mark_date']) ? "<A HREF='admin_parent_todo.php?action=todo_mark&amp;todo_id={$row['todo_id']}&amp;todo_user_id=$user_id&amp;cat=$cat#todo_list_user_$user_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Marked on') . " {$row['mark_date']}<BR><A HREF='admin_parent_todo.php?action=todo_unmark&amp;todo_id={$row['todo_id']}&amp;todo_user_id=$user_id&amp;cat=$cat#todo_list_user_$user_id'>" . T_('Unmark as done') . '&raquo;</A>' ?></TD>
</TR>
<?
    $row = mysql_fetch_assoc($result);
  } while($row && $row['category_id'] == $old_row['category_id']);
?>
<? $out = ob_get_clean(); ?>
<TBODY>
  <TR>
    <TH colspan="4"><A HREF="#cat_user_<?=$user_id, '_', $old_row['category_id']?>" onClick="var el = document.getElementById('cat_user_<?=$user_id, '_', $old_row['category_id']?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN><?=$old_row['category_id'] == gr('cat') ? '&minus;' : '+'?></SPAN> <?=es($old_row['subject_name']), ' / ', es($old_row['category_name'])?></A></TH>
    <TH><?=sprintf(T_('%d items'), $count)?></TH>
  </TR>
</TBODY>
<TBODY id="cat_user_<?=$user_id, '_', $old_row['category_id']?>" style="<?=$old_row['category_id'] == gr('cat') ? '' : 'display: none;'?> border-top: none;">
<?=$out?>
</TBODY>
<?
} while($row);
unset($out);
?>

</TABLE>
<?if(isset($s)):?><P>* <?=T_('This Todo is for you only.')?></P><?unset($s);?><?endif;?>
<HR>

<? endif; ?>

	<? endif; ?> 
	<!-- if ($child_id == $user_id) -->
	
<? endforeach; ?>
<!-- foreach ($admin_user['auths']['user'] as $user_id) -->
	
<? unset($user_id); ?>
					

				</div>
			
			</DIV>
			
		</DIV>
		
		<? include("admin_footer.php"); ?>
	</BODY>
	
</HTML>
