<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
if($action = gr('action')) switch($action) {
  case 'add_cat':
    $value = '';
    $category_id = -1;
    $subject_id = gri('subject_id', -1);
    break;

  case 'add_cat2':
    $subject_id = gri('subject_id', -1);
    if(mysql_num_rows(mq('SELECT 1 FROM todo_categories WHERE category_name = ' . ms(gr('name')) . ($subject_id != -1 ? " AND subject_id = $subject_id" : ' AND subject_id IS NULL')))) {
      $value = gr('name');
      $category_id = -1;
      $message = T_('Unable to save. This category already exists.');
      $action = 'add_cat';
    } else {
      mq('INSERT INTO todo_categories (subject_id, category_name) VALUES (' . nullif($subject_id, -1)  . ', ' . ms(gr('name')) . ')');
      $message = T_('Category added.');
    }
    break;

  case 'edit_cat':
    $result = mq('SELECT category_name, category_id, subject_id FROM todo_categories WHERE category_id = ' . gri('category_id', -1));
    if($row = mysql_fetch_assoc($result)) {
      $value = $row['category_name'];
      $category_id = $row['category_id'];
      $subject_id = $row['subject_id'];
    }
    break;

  case 'edit_cat2':
    $category_id = gri('category_id', -1);
    $subject_id = gri('subject_id');
    if(mysql_num_rows(mq('SELECT 1 FROM todo_categories WHERE subject_id' . ($subject_id != -1 ? " = $subject_id" : ' IS NULL') . " AND category_id != $category_id AND category_name = " . ms(gr('name'))))) {
      $value = gr('name');
      $id = gri('id', -1);
      $message = T_('Unable to save. This category already exists.');
      $action = 'edit_cat';
    } else {
      mq('UPDATE todo_categories SET category_name = ' . ms(gr('name')) . ', subject_id = ' . nullif($subject_id, -1) . " WHERE category_id = $category_id");
      $message = T_('Category edited.');
    }
    break;

  case 'del_cat':
    mq('DELETE FROM todo_categories WHERE category_id = ' . gri('category_id', -1));
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

$sql = "SELECT subject_name, subject_id, inst_name FROM subjects JOIN institutions USING (inst_id) UNION ALL SELECT '&lt;No Subject&gt;' subject_name, NULL subject_id, '' inst_name ORDER BY inst_name, subject_name";
$subjects = mysql_query( $sql );

//$subjects = mq("SELECT subject_name, subject_id, inst_name FROM subjects JOIN institutions USING (inst_id) UNION ALL SELECT '&lt;No Subject&gt;' subject_name, NULL subject_id, '' inst_name ORDER BY inst_name, subject_name");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('To-do List Categories'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('To-do List Categories')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if(isset($value)):?>
<FORM action="admin_todo_category.php" method="post" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Name')?>: <INPUT type="text" name="name" value="<?=es($value)?>"></LABEL><BR>
<INPUT type="hidden" name="category_id" value="<?=$category_id?>">
<LABEL><?=T_('Subject')?>: <SELECT name="subject_id">
<? while($row = mysql_fetch_assoc($subjects)): ?>
<OPTION value="<?=is_null($row['subject_id']) ? '-1' : $row['subject_id']?>" <?=$row['subject_id']==$subject_id ? 'SELECTED' : ''?>><?=es($row['inst_name']), ' - ', $row['subject_name']?>
<? endwhile; ?>
</SELECT></LABEL>
<? @mysql_data_seek($subjects, 0); ?>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="submit" value="Save">
</P>
</FORM>
<?endif;?>
<DL>
<? while($row = mysql_fetch_assoc($subjects)): ?>
<? $cats = mq('SELECT category_id, category_name, (SELECT COUNT(*) FROM todo_list WHERE todo_list.category_id = todo_categories.category_id) todos FROM todo_categories WHERE subject_id' . (is_null($row['subject_id']) ? ' IS NULL' : " = {$row['subject_id']}") . ' ORDER BY category_name'); ?>
<DT><?=es($row['inst_name']), ' - ', $row['subject_name']?> &#10023; <A HREF="admin_todo_category.php?action=add_cat&amp;subject_id=<?=is_null($row['subject_id']) ? '-1' : $row['subject_id']?>"><?=T_('Add Category')?></A>
<DD>
<? if(mysql_num_rows($cats)): ?>
<UL>
  <? while($cat_row = mysql_fetch_assoc($cats)): ?>
  <LI>
  <?=es($cat_row['category_name'])?>
  &#10023; <A HREF="admin_todo_category.php?action=edit_cat&amp;category_id=<?=$cat_row['category_id']?>"><?=T_('Edit')?></A>
  &#10023; <?=$cat_row['todos'] ? T_("Can't delete has todo items") : "<A HREF='admin_todo_category.php?action=del_cat&amp;category_id={$cat_row['category_id']}'>Delete</A>" ?>
  &#10023;
  <? endwhile; ?>
</UL>
<? else: ?>
<?=T_('No categories')?>
<? endif; ?>
<? endwhile; ?>
</DL>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
