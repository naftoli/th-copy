<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'school';
require_once('admin_ui.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);

if($school_id != -1 && $action = gr('action')) switch($action) {
  case 'add_classes':
    $added = 0;
    $failed = 0;
    foreach(gra('classes') as $class) {
      mq("INSERT IGNORE INTO classes SET school_id = $school_id, class_grade = " . ms($class['class_grade']) .  ', class_sub = ' . ms($class['class_sub']) . ', class_teacher = ' . ms($class['class_teacher']) . ', default_level = ' . max(3, min(intval($class['default_level']), 14)));
      if(mysql_affected_rows()) $added++; else $failed++;
    }
    $message = sprintf($failed ? T_('Created %d new Platoons, but %d failed because the grade/sub combination already existed.') : T_('Created %d new Platoons.'), $added, $failed);
    break;

  case 'move':
    if(gr('to_class', false)) {
      $new_class_id =  gri('new_class_id', -1);
      if(mysql_num_rows(mq("SELECT * FROM classes WHERE school_id = $school_id AND class_id = $new_class_id"))) 
          foreach(gra('users') as $user_id) {
            $user_id = intval($user_id);
            mq("UPDATE users SET class_id = $new_class_id WHERE school_id = $school_id AND user_id = $user_id");
            //update years for all campaigns for user
            $sql1 = "select * from user_tracks where user_id = " . $user_id;
            $res1 = mysql_query($sql1);
            while ($row1 = mysql_fetch_assoc($res1)) {
                $campaign = $row1['subject_id'];
                $level = $row1['level'];
                $level++;
                $sql2 = "update user_tracks set level = " . $level . "
                        where user_id = " . $user_id . " and subject_id = " . $campaign;
                mysql_query($sql2);
            }
            /*
            header_update_icorpa_student(array(
                "legacy_user_id" => $user_id
            ));
            */
      }
      $message = T_('Moved soldiers to new platoon.');
    } elseif(gr('no_school', false)) {
      foreach(gra('users') as $user_id) {
        $user_id = intval($user_id);
        mq("UPDATE users SET school_id = NULL, class_id = NULL, team_id = NULL WHERE school_id = $school_id AND user_id = $user_id");
      }
      $message = T_('Removed soldiers from school.');
    } elseif(gr('show_class', false)) {
      ; //no-op
    }
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Platoon Transition'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript">
class_row_id = 0;
function addClass(table) {
  class_row_id--;
  row = table.insertRow(table.rows.length-1);
  row.innerHTML = "<TD><SELECT name='classes[" + class_row_id + "][class_grade]'> \
      <?foreach(mysql_enum_values('classes', 'class_grade') as $grade):?><OPTION><?=es($grade)?></OPTION><?endforeach;?> \
      </SELECT></TD><TD><INPUT type='text' name='classes[" + class_row_id + "][class_sub]' maxlength=255 size=20 value=''></TD> \
      <TD><INPUT type='text' name='classes[" + class_row_id + "][class_teacher]' maxlength=255 size=40 value=''></TD> \
      <TD><INPUT type='text' name='classes[" + class_row_id + "][default_level]' maxlength=2 size=2 value='1' onChange='this.value = Math.max(3, Math.min(parseInt(\'0\'+this.value, 10), 14));'></TD> \
      <TD><A HREF='#' onClick='this.parentNode.parentNode.parentNode.deleteRow(this.parentNode.parentNode.rowIndex); return false;' title='<?=T_('Delete')?>'>&times;</A></TD>";
}

function checkDup(form) {
  var pairs = {};
  var seen = {};
  var grade = /classes\[(-?\d+)\]\[class_grade\]/;
  var sub = /classes\[(-?\d+)\]\[class_sub\]/;

  for(var i = 0; i < form.elements.length; i++) {
    if(grade.test(form.elements[i].name)) {
      if(pairs[RegExp.lastParen] === undefined) pairs[RegExp.lastParen] = {};
      pairs[RegExp.lastParen]['grade'] = form.elements[i].value;
    }
    if(sub.test(form.elements[i].name)) {
      if(pairs[RegExp.lastParen] === undefined) pairs[RegExp.lastParen] = {};
      pairs[RegExp.lastParen]['sub'] = form.elements[i].value;
    }
  }

  for(var i in pairs) {
    if(seen[pairs[i]['grade'] + ':' + pairs[i]['sub']] === undefined) {
      seen[pairs[i]['grade'] + ':' + pairs[i]['sub']] = true;
    } else {
      alert('<?=T_('Unable to save, you have Platoons with the same Grade and sub.')?>')
      return false;
    }
  }
  return true;
}

/*
 value =
   1: set on
   0: set off
  -1: toggle
*/
function setCheckboxes(form, nameRegex, value) {
  var pattern = new RegExp(nameRegex);

  for(var i = 0; i < form.elements.length; i++) {
    if(pattern.test(form.elements[i].name) && form.elements[i].type == 'checkbox') {
      form.elements[i].checked = (value == -1 ? !form.elements[i].checked : value);
    }
  }
}
</SCRIPT>
<script src="camps/scripts/jquery.tools.min.js"></script>
<script type="text/javascript">
    $(function() {
        $("#move").click(function() {
            if ($("#newClassId").val() == -1) {
                alert('<?=T_('Please select the platoon to move to.')?>'); 
                return false;
            }
            /*
            var classID = $("#newClassId").val();
            var email = $("#email").val();
            var cell = $("#cell").val();
            if (email == "" || cell == "") {
                 alert("You must enter an email and phone number for this teacher.");
                 return false;
            } else {
                //alert(classID);
                $.post('ajax/setTeacherInfo.php', {email : email, cell : cell, id : classID});
            }
            */
        });
        /*
        $("#newClassId").change( function() {
            $("#email").val('');
            $("#cell").val('');
            var id = $(this).val();
            $.post('ajax/getTeacherInfo.php', {class_id : id}, function(data) {
                var index = data.indexOf(':');
                var email = data.substring(0, index);
                var cell = data.substring(index+1);
                $("#email").val(email);
                $("#cell").val(cell);
            });
        });
        */
        $("#selectAll").click(function() {
            $(".users").attr('checked', true);
        });
        
        $("#selectNone").click(function() {
            $(".users").attr('checked', false);
        });
        
        $("#toggleSelect").click(function() {
            $(".users").each( function() {
                $(this).attr('checked', !$(this).is(":checked"));
            });
        });
    });
</script>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Base Management')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_class_transition.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<H2><?=T_('Platoon Transition')?></H2>
<?
$classes = mq("SELECT class_id, class_grade, class_sub, class_teacher, email, cell, default_level FROM classes WHERE class_era = 0 AND school_id = $school_id ORDER BY class_grade, class_sub");
mq("DELETE FROM classes WHERE school_id = $school_id AND class_era != 0 AND NOT EXISTS (SELECT * FROM users WHERE users.class_id = classes.class_id)");
$old_classes = mq("SELECT class_id, class_grade, class_sub, class_teacher, email, cell, default_level FROM classes WHERE class_era != 0 AND school_id = $school_id ORDER BY class_grade, class_sub");
?>



<? if (!mysql_num_rows($old_classes)) : ?>
	<P><?=T_("You don't have any old classes to transition.")?></P>
<? else: ?>

	<? if (mysql_num_rows($classes)) : ?>
	<BR>
<div class="infobox">
<p><?=T_('Below you will find your platoons (classes) from last year. Please update your platoons (classes) for the coming school year. 
Each grade is set to a default age based on the American School System. This affects the level missions the children will be receiving.')?></p>
</div>
<FIELDSET style="width: 674px; margin: auto; padding: 30px;">
<FORM action="admin_class_transition.php" method="post" accept-charset="UTF-8" name="transition">

<DIV style="float: <?=$align_start?>; width: 300px; margin-<?=$align_end?>: 15px; border: 1px solid black; height: 400px; overflow: auto; padding: 10px; text-align: center;">
<H4 style="text-align: <?=$align_start?>;">1. <?=T_('Select Platoon')?>:</H4>
<? $old_class_id = gri('old_class_id', -1); $found_class = false; ?>
<SELECT name="old_class_id" id="oldClassId">
<OPTGROUP label="<?=T_('Last-years Platoons')?>">
<? while($row = mysql_fetch_assoc($old_classes)): ?>
<? if(!isset($first_class_id)) $first_class_id = $row['class_id']; ?>
<? if($old_class_id == $row['class_id']) $found_class = true; ?>
<OPTION value="<?=$row['class_id']?>" <?=$old_class_id == $row['class_id'] ? 'SELECTED' : ''?>><?=$row['class_grade'],'-',es($row['class_sub']),' : ',es($row['class_teacher'])?>
<? endwhile; ?>
</OPTGROUP>
<OPTION disabled>------------
<OPTGROUP label="<?=T_('Current Platoons')?>">
<? while ($row = mysql_fetch_assoc($classes)) : ?>
    <OPTION value="<?=$row['class_id']?>" <?=$old_class_id == $row['class_id'] ? 'SELECTED=' : ''?>><?=$row['class_grade'],'-',es($row['class_sub']),' : ',es($row['class_teacher'])?>
<? endwhile; ?>
</OPTGROUP>
</SELECT>
<? if(!$found_class) $old_class_id = $first_class_id; ?>
<br />
<INPUT type="submit" name="show_class" value="<?=T_('Select &gt;&gt;')?>">
<BR> 
<BR><HR><BR>
<H4 style="text-align: <?=$align_start?>;">2. <?=T_('Select Soldiers')?></H4> 
<? if ($found_class) : ?>
<? $users = mq("SELECT user_id, first, last, user_serial FROM users WHERE school_id = $school_id AND class_id = $old_class_id"); ?>
<!--<A HREF="#" onClick="setCheckboxes(document.forms['transition'], 'users\\[\\]', 1); return false;"><?=T_('Select All')?></A>-->
<A HREF="#" id="selectAll"><?=T_('Select All')?></A>
&bull;
<!--<A HREF="#" onClick="setCheckboxes(document.forms['transition'], 'users\\[\\]', 0); return false;"><?=T_('Select None')?></A>-->
<A HREF="#" id="selectNone"><?=T_('Select None')?></A>
<!--&bull;
<A HREF="#" onClick="setCheckboxes(document.forms['transition'], 'users\\[\\]', -1); return false;"><?=T_('Toggle Selections')?></A>
<A HREF="#" id="toggleSelect"><?=T_('Toggle Selections')?></A>-->
<TABLE class="pretty_grid" style="margin: auto;">
<THEAD>
<TR>
<TH><?=T_('Serial #')?></TH>
<TH><?=T_('Name')?></TH>
<TH><?=T_('Select')?></TH>
</TR>
</THEAD>
<TBODY>
<? while($row = mysql_fetch_assoc($users)): ?>
<TR>
<TD><?=$row['user_serial']?></TD>
<TD><?=es($row['first'] . ' ' . $row['last'])?></TD>
<TD><INPUT type="checkbox" class="users" name="users[]" value="<?=$row['user_id']?>"></TD>
</TR>
<? endwhile; ?>
</TBODY>
</TABLE>
<? endif; ?>
<BR>
</DIV>

<DIV style="float: <?=$align_start?>; width: 300px; margin-<?=$align_start?>: 15px; border: 1px solid black; height: 400px; overflow: auto; padding: 10px; text-align: center;">
<INPUT type="hidden" name="action" value="move">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<H4 style="text-align: <?=$align_start?>;">3. <?=T_('Move selected soldiers into Platoon')?>:</H4>
<SELECT name="new_class_id" id="newClassId">
<OPTION value="-1">&lt;<?=T_('Select Platoon')?>&gt;
<? mysql_data_seek($classes, 0); ?>
<? while($row = mysql_fetch_assoc($classes)): ?>
<OPTION value="<?=$row['class_id']?>"><?=$row['class_grade'],'-',es($row['class_sub']),' : ',es($row['class_teacher'])?>
<? endwhile; ?>
</SELECT>
<br />
<!--
<span style="font-size: 12px">
So that we can save you much unnecessary effort by being directly in touch with your teachers, please fill in:<br /> 
1. Teachers email address preferably gmail so they can be shared on Google Docs.<br />
2. Teachers cell phone numbers in order to receive updates and reminders through text.<br /></span>
* Email: <input type="text" name="email" id="email" size="30" value="" /><br />
* Cell Phone: <input type="text" name="cell" id="cell" size="24" value="" />
<br />
-->
<INPUT type="submit" name="to_class" id="move" value="<?=T_('Move')?>" /> 
<BR><BR><HR><BR>
<H4 style="text-align: <?=$align_start?>;"><?=T_('Or, mark the selected soldiers as no longer in your school')?></H4>
<INPUT type="submit" name="no_school" value="<?=T_('Remove')?>">
<P>(<?=T_('This will not delete them, it will only remove them from your school.')?>)</P>
</DIV>

</FORM>
</FIELDSET>
<!--
<BR><BR>
<FIELDSET style="width: 720px; margin: auto;">
<LEGEND><?=T_('Add a Platoon')?></LEGEND>
<FORM action="admin_class_transition.php" method="get" accept-charset="UTF-8">
<TABLE>
<TR>
<TH><?=T_('Grade')?></TH>
<TH><?=T_('Sub')?></TH>
<TH><?=T_('Teacher')?></TH>
<TH><?=T_('Average Age of Soldiers')?> (6 - 14)</TH>
</TR>
<TR>
<TD><SELECT name="classes[-1][class_grade]">
<?foreach(mysql_enum_values('classes', 'class_grade') as $grade):?>
<OPTION><?=es($grade)?></OPTION>
<?endforeach;?>
</SELECT></TD>
<TD><INPUT type="text" name="classes[-1][class_sub]" maxlength=255 size=10 value=""></TD>
<TD><INPUT type="text" name="classes[-1][class_teacher]" maxlength=255 size=25 value=""></TD>
<TD><INPUT type="text" name="classes[-1][default_level]" maxlength=2 size=2 value="1" onChange="this.value = Math.max(3, Math.min(parseInt('0'+this.value, 10), 14));"></TD>
<TD>
  <INPUT type="hidden" name="action" value="add_classes">
  <INPUT type="hidden" name="school_id" value="<?=$school_id?>">
  <INPUT type="submit" value="<?=es(T_('Add'))?>">
</TD>
</TR>
</TABLE>
</FORM>
-->
<P>(<?=T_('To edit or delete platoons please use the Manage Platoons option from the menu.')?>)</P>
</FIELDSET>

<? else: ?>
<FORM action="admin_class_transition.php" method="get" accept-charset="UTF-8" onSubmit="return checkDup(this);">
<TABLE class="list" style="margin: auto;">
<CAPTION style="padding: 2em 0px; text-align: <?=$align_start?>;"><?=T_('Please setup your platoons. Your platoons from last year are shown.')?></CAPTION>
<THEAD>
<TR>
<TH><?=T_('Grade')?></TH>
<TH><?=T_('Sub')?></TH>
<TH><?=T_('Teacher')?></TH>
<TH><?=T_('Average Age of Soldiers')?> (3 - 14)</TH>
<TH><?=T_('Actions')?></TH>
</TR>
</THEAD>
<? while($row = mysql_fetch_assoc($old_classes)): ?>
<TR>
<TD><SELECT name="classes[<?=$row['class_id']?>][class_grade]">
<?foreach(mysql_enum_values('classes', 'class_grade') as $grade):?>
<OPTION <?=$grade == $row['class_grade'] ? 'SELECTED' : ''?>><?=es($grade)?></OPTION>
<?endforeach;?>
</SELECT></TD>
<TD><INPUT type="text" name="classes[<?=$row['class_id']?>][class_sub]" maxlength=255 size=10 value="<?=es($row['class_sub'])?>"></TD>
<TD><INPUT type="text" name="classes[<?=$row['class_id']?>][class_teacher]" maxlength=255 size=25 value="<?=es($row['class_teacher'])?>"></TD>
<TD><INPUT type="text" name="classes[<?=$row['class_id']?>][default_level]" maxlength=2 size=2 value="<?=es($row['default_level'])?>" onChange="this.value = Math.max(3, Math.min(parseInt('0'+this.value, 10), 14));"></TD>
<TD><A HREF="#" onClick="this.parentNode.parentNode.parentNode.deleteRow(this.parentNode.parentNode.rowIndex); return false;" title="<?=T_('Delete')?>">&times; <?=T_('Remove this Platoon')?></A></TD>
</TR>
<? endwhile; ?>
<TR>
<TD colspan="4">
  <INPUT type="hidden" name="action" value="add_classes">
  <INPUT type="hidden" name="school_id" value="<?=$school_id?>">
  <INPUT type="submit" value="<?=es(T_('Save >>'))?>">
</TD>
<TD><A HREF="#" onClick="addClass(this.parentNode.parentNode.parentNode); return false;">&uArr; <?=T_('Add another Platoon')?></A></TD>
</TR>
</TABLE>
</FORM>

<? endif; ?>
<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
