<?php
include_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
       school_name, school_number, school_city, school_state, school_logo_id, school_makeup_id, school_logo_kiosk_id, inst_logo_id, school_type_id, 
       rank_ord, rank_name, rank_image_id, rank_color
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

if($user_row['school_makeup_id'] != 4)
    header('Location: ../statement.php');

include("includes/header.php");

?>
<style>
.list thead tr {
  background-color: #fdf8ea;
}
#worktable .list thead tr {
  background-color: blue;  background-image: url(images/Camouflage-Background-Blue.png);  background-position: center center;
}
.list td, .list th {
  border-bottom: 1px solid #cccccc;  padding: 15px;
}
.list table a, .list table a:visited {
  text-decoration: underline;  color: #1c1c1c;
}
.list_left th {
  text-align: left;
}
.list_righ th {
  text-align: right;
}
.dashboard {
  margin: 0px;  padding: 0px;
 -webkit-border-radius:.06in; -moz-border-radius:.06in;border-radius: .06in;text-align: center;
  color: #008B8B;font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;font-size: 14px; 
}
.dashboard table {
  margin: auto; border-collapse: collapse;
}
.dashboard a, .dashboard .link_button {
  text-decoration: none;font-weight: bold;line-height: 40px;padding: 7px 0px;
}
.dashboard a:link, .dashboard a:visited, .dashboard .link_button {
  color: green;
}

.dashboard a:hover, .dashboard a:active, .dashboard .link_button:hover {
  color: white;
}

</style>
<body class="blue">
    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">ToDo List</div>
            <div class="three_column padding_top">
              <div class="">
                    <div class="dashboard">
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
                        <A HREF="todolist.php?view_all=<?=!$view_all?>"><?= $view_all ? T_('List only unfinished to-do items') : T_('List all to-do items') ?>&raquo;</A>
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
                        $result = mq(
                        'SELECT todo_list.todo_id, todo_text, todo_priority, category_name, category_id, subject_name, todo_due_date, todo_file_id, todo_url, mark_date, todo_list.recip_id '.
                        'FROM todo_list '.
                        'LEFT JOIN todo_categories USING (category_id) '.
                        'LEFT JOIN subjects USING (subject_id) '.
                        "LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'end_user' AND todo_list_marks.id = {$user['user_id']}) ".
                        "WHERE visibility != 'none' ".
                            "AND todo_list.school_id = {$user['school_id']} ".
                            "AND todo_list.recip = 'end_user' ".
                            "AND (todo_list.recip_id = {$user['user_id']} ".
                            "OR todo_list.recip_id IS NULL)" . 
                            ($view_all ? '' : ' AND mark_date IS NULL') . 
                        ' ORDER BY subject_name, category_name, todo_priority, todo_due_date, todo_text, creation_date, todo_list.todo_id');                        
                        $row = $old_row = mysql_fetch_assoc($result);                       
                        if($row) 
                            do 
                            {
                              $count = 0;
                              ob_start();
                        
                              do 
                              {
                                $count++;
                                if(isset($todo_id) && $row['todo_id'] == $todo_id) 
                                    $this_todo = true;
                                $old_row = $row;
                                $cat = $row['category_id'];//33710627850554651136
                            ?>                      
                            <TR>
                                <TD><?=$row['todo_priority']?></TD>
                                <TD><?=es(dateToHebrew($row['todo_due_date']))?></TD>
                                <TD><?=is_null($row['recip_id']) ? '' : $s = '* ', es($row['todo_text'])?></TD>
                                <TD><?if(!is_null($row['todo_file_id'])):?><A HREF="../file_view.php?id=<?=$row['todo_file_id']?>&amp;m=d"><?=T_('View/Print File')?>&raquo;</A><?endif;?> <?if($row['todo_url']):?><A HREF="<?=substr($row['todo_url'], 0, 7) == 'http://' ? $row['todo_url'] : '../' . $row['todo_url'] ?>"><?=T_('Goto Link')?>&raquo;</A><?endif;?></TD>
                                <TD><?=is_null($row['mark_date']) ? "<A HREF='todolist.php?action=todo_mark&amp;todo_id={$row['todo_id']}&amp;cat=$cat'>" . T_('Mark as done') . '&raquo;</A>' : T_('Marked on') . " {$row['mark_date']}<BR><A HREF='todolist.php?action=todo_unmark&amp;todo_id={$row['todo_id']}&amp;cat=$cat'>" . T_('Unmark as done') . '&raquo;</A>' ?></TD>
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
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
