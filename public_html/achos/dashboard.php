<? 
header('Location: statement.php');
$req_school_settings = array('home_school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_code, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('My Dashboard'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<LINK href="card_printer.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY class="dashboard">

<DIV id="header">
<DIV>
<?=!is_null($user_row['school_logo_kiosk_id']) ? linkImgFile($user_row['school_logo_kiosk_id']) : (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id']) : '')?>
<P>
    <B><?=es($user_row['school_name'])?></B><BR>
    <B><?=T_('Base')?> # <?=$user_row['school_number']?></B><BR>
    <?=T_('Base Mileage')?>: <?=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']}")), 0), 2)?><BR>
    <?=T_('Base Average')?>: <?=number_format($base_points / mysql_result(mq("SELECT COUNT(*) base_count FROM users WHERE school_id = {$user['school_id']}"), 0), 2)?><BR>
    <?=T_('Army Average')?>: <?= number_format(mysql_result(mq(totalMarks()), 0) / mysql_result(mq("SELECT COUNT(*) base_count FROM users WHERE user_start_date IS NOT NULL"), 0), 2) ?> <BR>

</P>
</DIV>
</DIV>

<DIV id="body">
<TABLE>
<TR id="user_display">
<TD class="photo"><?=!is_null($user_row['user_photo_id']) ? linkImgFile($user_row['user_photo_id']) : ''?><BR><A HREF="logout.php?n=dashboard.php"><?=T_('Logout')?></A></TD>
<TD>
    <EM><?=es(firstInitial($user_row['first']))?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es(firstInitial($user_row['first_he']))?> <?=es($user_row['last_he'])?></EM><BR>
    <?=T_('Rank')?>: <B><?=es($user_row['rank_name'])?></B><BR>
    <?=T_('Serial')?> #: <B><?=$user_row['user_serial']?></B><BR>
    <?=T_('Platoon')?>: <B><?=$user_row['class_grade'], $user_row['class_grade']!=='' && $user_row['class_sub']!=='' ? '-' : '', $user_row['class_sub']?></B><BR>
    <?=T_('Teacher')?>: <B><?=$user_row['class_teacher']?></B><BR>
    <?=T_('Platoon Average')?>: <B><?=is_null($user_row['class_id']) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0), 2)?></B><BR>
    <EM><?=T_('Total Miles')?>: <B><?=number_format($user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']}")), 0), 2)?></B></EM><BR>
</TD>
<TD><?=!is_null($user_row['rank_image_id']) ? linkImgFile($user_row['rank_image_id'], NULL, NULL, 'class="rank"') : ''?></TD>
</TR>

<? if($user['registered']): ?>
<TR>
<TD id="worktable" colspan="3">
<SCRIPT type="text/javascript">
var openButton = 'Commands';
function changeButton(id) {
  document.getElementById(openButton).style.display = 'none';
  document.getElementById(id).style.display = '';
  openButton = id;
}
</SCRIPT>
<TABLE id="tabs">
<TR>
<TH style="background-image: url(images/Camouflage-Background-Purple.png);"><A HREF="#" onClick="changeButton('Commands'); return false;"><?=T_('Command Center')?></A></TH>
<TH style="background-image: url(images/Camouflage-Background-Red.png);"><A HREF="#" onClick="changeButton('Card'); return false;"><?=T_('Achievement Card Inbox')?></A></TH>
<TH style="background-image: url(images/Camouflage-Background-Blue.png);"><A HREF="#" onClick="changeButton('Training'); return false;"><?=T_('Training Center')?></A></TH>
<TH style="background-image: url(images/Camouflage-Background-Yellow.png);"><A HREF="#" onClick="changeButton('Reporting'); return false;"><?=T_('Reporting Center')?></A></TH>
<TH style="background-image: url(images/Camouflage-Background-Orange.png);"><A HREF="#" onClick="changeButton('Awards'); return false;"><?=T_('Awards and Miles')?></A></TH>
</TR>
</TABLE>
<H1>My Dashboard</H1>

<DIV id="Commands" style="background-image: url(images/Camouflage-Background-Purple.png);">

<H3><?=T_('Todo List')?></H3>
<?
if(($action = gr('action'))) switch($action) {
  case 'todo_mark':
    $todo_id = gri('todo_id', -1);
    if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
      mq("INSERT IGNORE INTO todo_list_marks SET todo_id = $todo_id, auth = 'end_user', id = {$user['user_id']}");
    break;

  case 'todo_unmark':
    $todo_id = gri('todo_id', -1);
    if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
      mq("DELETE FROM todo_list_marks WHERE todo_id = $todo_id AND auth = 'end_user' AND id = {$user['user_id']}");
    break;
}
?>
<? $view_all = gri('view_all', 0); ?>

<P>
<A HREF="dashboard.php?view_all=<?=!$view_all?>"><?= $view_all ? T_('List only unfinished to-do items') : T_('List all to-do items') ?>&raquo;</A>
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

<?
$result = mq("SELECT todo_list.todo_id, todo_text, todo_priority, category_name, category_id, subject_name, todo_due_date, todo_file_id, todo_url, mark_date, todo_list.recip_id FROM todo_list LEFT JOIN todo_categories USING (category_id) LEFT JOIN subjects USING (subject_id) LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'end_user' AND todo_list_marks.id = {$user['user_id']}) WHERE visibility != 'none' AND todo_list.school_id = {$user['school_id']} AND todo_list.recip = 'end_user' AND (todo_list.recip_id = {$user['user_id']} OR todo_list.recip_id IS NULL)" . ($view_all ? '' : ' AND mark_date IS NULL') . ' ORDER BY subject_name, category_name, todo_priority, todo_due_date, todo_text, creation_date, todo_list.todo_id');

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
<TD><?=is_null($row['mark_date']) ? "<A HREF='dashboard.php?action=todo_mark&amp;todo_id={$row['todo_id']}&amp;cat=$cat'>" . T_('Mark as done') . '&raquo;</A>' : T_('Marked on') . " {$row['mark_date']}<BR><A HREF='dashboard.php?action=todo_unmark&amp;todo_id={$row['todo_id']}&amp;cat=$cat'>" . T_('Unmark as done') . '&raquo;</A>' ?></TD>
</TR>
<?
    $row = mysql_fetch_assoc($result);
  } while($row && $row['category_id'] == $old_row['category_id']);
?>
<? $out = ob_get_clean(); ?>
<TBODY>
  <TR>
    <TH colspan="4"><A HREF="#cat_<?=$old_row['category_id']?>" onClick="var el = document.getElementById('cat_<?=$old_row['category_id']?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN>+</SPAN> <?=es($old_row['subject_name']), ' / ', es($old_row['category_name'])?></A></TH>
    <TH><?=sprintf(T_('%d items'), $count)?></TH>
  </TR>
</TBODY>
<TBODY id="cat_<?=$old_row['category_id']?>" style="<?=$old_row['category_id'] == gr('cat') ? '' : 'display: none;'?> border-top: none;">
<?=$out?>
</TBODY>
<?
} while($row);
unset($out);
?>

</TABLE>
<?if(isset($s)):?><P>* <?=T_('This Todo is for you only.')?></P><?unset($s);?><?endif;?>
<HR>

</DIV>

<DIV id="Card" style="display: none; background-image: url(images/Camouflage-Background-Red.png);">
<H3><?=T_('Achievement Card Inbox')?></H3>
<? $codes = mq("SELECT first, last, code_id, code_id_prefix, grant_date FROM user_codes LEFT JOIN admins USING (admin_id) WHERE user_id = {$user['user_id']} ORDER BY grant_date"); ?>
<? if(!mysql_num_rows($codes)): ?>
  <P><?=T_('No cards.')?></P>
<? else: ?>
  <P class="noprint"><A HREF="#Card" onClick="document.documentElement.className='print_mode'; this.nextSibling.style.display=''; this.style.display= 'none'; print();"><?=T_('Print')?></A><A HREF="#tabs" onClick="document.documentElement.className=''; this.previousSibling.style.display=''; this.style.display='none';" style="display: none;"><?=T_('Cancel Print View')?></A></P>
  <TABLE class="card">
  <?while($row = mysql_fetch_assoc($codes)):?>
    <TR>
    <TH colspan="2">
      <?=sprintf(T_('Granted %s by %s %s'), $row['grant_date'], es($row['first']), es($row['last']))?>
      &nbsp; &bull; &nbsp;
      <FORM action="statement.php#scan" method="post" accept-charset="UTF-8" style="display: inline;"><DIV style="display: inline;"><INPUT type="hidden" name="scan_code" value="<?=$row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT)?>"><INPUT type="submit" class="link_button" value="<?=T_('Deposit to Account')?>"></DIV></FORM>
    </TH>
    </TR>
    <? $code_details = code_details($row['code_id_prefix'], $row['code_id'], $user['user_id']); ?>
    <?if(!$code_details):?>
    <TR>
    <TD colspan="2"><?=sprintf(T_('Card %s is missing'), $row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT))?></TD>
    </TR>
    <?else:?>
    <TR>
    <TD><?=display_card_front($code_details['expires'], $code_details['school_number'], $code_details['school_name'], $code_details['school_city'], $code_details['school_state'], $code_details['school_logo_id'])?></TD>
    <TD><?=display_card_back($row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT), $code_details['points'], $code_details['bonus'], $code_details['left_circle'], $code_details['right_circle'], $code_details['description'], $code_details['subject_name'], $code_details['subject_image_id'], $code_details['series'])?></TD>
    </TR>
    <?endif;?>
  <?endwhile;?>
  </TABLE>
<?endif;?>
</DIV>

<DIV id="Training" style="display: none; background-image: url(images/Camouflage-Background-Blue.png);">
<H3><?=T_('Training Center')?></H3>

<UL>
<LI><A HREF="http://anash.com/th.html">Recruitment film</A>
<LI><A HREF="http://tzivoshashem.org/cth/MY%20SHLIACH%20WWTC.pdf" target="_blank">WWTC: Training slide show</A>
<LI><A HREF="http://anash.com/tanya5.html" target="_blank">Tanya: Tanya film</A>
</UL>

</DIV>

<DIV id="Reporting" style="display: none; background-image: url(images/Camouflage-Background-Yellow.png);">
<H3><?=T_('Reporting Center')?></H3>

<UL>
<LI><A HREF="statement.php">Scan a card</A>
<LI><A HREF="admin_card_print.php" target="_blank"><?=T_('Temporary Rank Card')?></A>
</UL>

</DIV>

<DIV id="Awards" style="display: none; background-image: url(images/Camouflage-Background-Orange.png);">
<H3><?=T_('Awards and Miles')?></H3>
<UL>
<!-- <LI><A HREF="admin_report_hakhel.php?mission=current" target="_blank"><?=T_('Your current Hakhel Report')?></A> -->
<!-- <LI><A HREF="admin_print_pdf.php?type=tbp_progress_report&amp;week_num=-1" target="_blank"><?=T_('Tanya Baal Peh Weekly Quota Report')?></A> -->
<!-- <LI><A HREF="admin_print_pdf.php?type=tbp_yearly_progress" target="_blank"><?=T_('Tanya Baal Peh Yearly Progress Chart')?></A> -->
<!-- <LI><A HREF="admin_tanya_lines_print.php" target="_blank"><?=T_('Tanya Lines')?></A> -->
<!-- <LI><A HREF="admin_auction_print.php" target="_blank"><?=T_('Chinese Auction Prize Cards')?></A> -->
<!-- <LI><A HREF="admin_report_auction.php" target="_blank"><?=T_('Auction Prize Entry Sheet')?></A> -->
</UL>
</DIV>
<BR>
<? else: ?>
<TR>
<TD colspan="3" id="no_scan">
<H1>
  <?=T_('You are not currently registered in Tzivos Hashem.')?><BR>
  <?=T_('Please see the program director at your school.')?>
</H1>
<? endif; ?>

</TD>
</TR>
</TABLE>


</DIV>
</BODY>
</HTML>
