<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
$action = gr('action');
$lang = gr('lang');
$lines = gri('lines', 100);
$type = gr('type', 'varchar');

switch(gr('next')) {
  case T_("<< Save, and go to previous page"):
  case T_("Save, and go to next page >>"):
    $save = true;
    break;

  case T_("<< Don't save, and go to previous page"):
  case T_("Don't save, and go to next page >>"):
  default:
    $save = false;
    break;
}

switch(gr('next')) {
  case T_("<< Save, and go to previous page"):
  case T_("<< Don't save, and go to previous page"):
    $where = '< ' . ms(gr('first_line'));
    $reverse = true;
    break;

  case T_("Save, and go to next page >>"):
  case T_("Don't save, and go to next page >>"):
    $where = '> ' . ms(gr('last_line'));
    $reverse = false;
    break;

  default:
    $where = '';
    $reverse = false;
    break;
}

switch($type) {
  case 'text':
    $table = 'translations_text';
    break;

  case 'varchar':
  default:
    $table = 'translations_varchar';
    break;
}

if($save) {
  $delete = 0;
  $insert = 0;
  $update = 0;
  foreach(gra('text') as $entry) {
    if($entry['text_transl'] === '') {
      mq("DELETE FROM $table WHERE lang = " . ms($lang) . ' AND text = ' . ms($entry['text']));
      $delete++;
    } else {
      if(mysql_num_rows(mq("SELECT * FROM $table WHERE lang = " . ms($lang) . ' AND text = ' . ms($entry['text'])))) {
        mq("UPDATE $table SET text_transl = " . ms($entry['text_transl']) . ' WHERE lang = ' . ms($lang) . ' AND text = ' . ms($entry['text']));
        $update++;
      } else {
        mq("INSERT INTO $table (text, lang, text_transl) VALUES (" . ms($entry['text']) . ', ' . ms($lang) . ', ' . ms($entry['text_transl']) . ')');
        $insert++;
      }
    }
  }
  $message = sprintf(T_('Saved. %d lines deleted, %d added, and %d changed.'), $delete, $insert, $update);
}

// WARNING: Both of the two arrays below MUST have at least 2 entries
$text_list = array(
  array('date_tasks_missions', 'mission_description'),
  array('date_tasks', 'description'),
  array('chain_missions', 'mission_description'),
  array('chain_items', 'description'),
);
$varchar_list = array(
  array('missions', 'mission_name'),
  array('tasks', 'name'),
  array('tasks', 'quantity_name'),
  array('date_tasks_missions', 'mission_name'),
  array('date_tasks', 'name'),
  array('goals', 'goal_start'),
  array('goals', 'goal_end'),
  array('labels', 'label_name'),
  array('labels', 'label_description'),
  array('chain_missions', 'mission_name'),
  array('chain_items', 'name'),
);

$text_sql = '';
foreach($type == 'text' ? $text_list : $varchar_list as $tbl_col) {
  if($text_sql !== '') $text_sql .= ' UNION DISTINCT ';
  $text_sql .= "(SELECT {$tbl_col[1]} text FROM {$tbl_col[0]}" . ($where ? " WHERE {$tbl_col[1]} $where": '') . ')';
}

switch($action) {
  case 'new':
    $text = mq(($reverse ? 'SELECT * FROM (' : '') . "SELECT text.text, text_transl FROM ($text_sql) text LEFT JOIN $table ON (text.text = $table.text AND $table.lang = " . ms($lang) . ") WHERE text.text != '' AND $table.text IS NULL ORDER BY text.text" . ($reverse ? ' DESC' : '') . " LIMIT $lines" . ($reverse ? ') r ORDER BY text': ''));
    break;

  case 'review':
    $text = mq(($reverse ? 'SELECT * FROM (' : '') . "SELECT text.text, text_transl FROM ($text_sql) text JOIN $table ON (text.text = $table.text AND $table.lang = " . ms($lang) . ") ORDER BY text.text" . ($reverse ? ' DESC' : '') . " LIMIT $lines" . ($reverse ? ') r ORDER BY text': ''));
    break;

  case 'old':
    $text = mq(($reverse ? 'SELECT * FROM (' : '') . "SELECT $table.text, text_transl FROM $table LEFT JOIN ($text_sql) text ON (text.text = $table.text) WHERE text.text IS NULL" . ($where ? " AND $table.text $where" : '') . " AND $table.lang = " . ms($lang) . " ORDER BY $table.text" . ($reverse ? ' DESC' : '') . " LIMIT $lines" . ($reverse ? ') r ORDER BY text': ''));
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Translation'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
pre {
  background-color: white;
  border: 1px dotted black;
  padding: 2px 1px;
  margin: 0px;
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-size: 14px;
  font-weight: normal;
  white-space: pre-wrap;
}

input, textarea {
  font-size: 14px;
}
</STYLE>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Translation')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<FORM action="admin_translate.php" method="post" accept-charset="UTF-8">
<P>

<?=T_('Action')?>:<BR>
  <LABEL><INPUT type="radio" name="action" value="new" <?=$action == 'new' ? 'CHECKED' : ''?>><?=T_('View Untranslated Phrases')?></LABEL><BR>
  <LABEL><INPUT type="radio" name="action" value="review" <?=$action == 'review' ? 'CHECKED' : ''?>><?=T_('Review Translated Phrases')?></LABEL><BR>
  <LABEL><INPUT type="radio" name="action" value="old" <?=$action == 'old' ? 'CHECKED' : ''?>><?=T_('View Unused (old) Translations')?></LABEL><BR>
<BR>

<?=T_('Type')?>:<BR>
  <LABEL><INPUT type="radio" name="type" value="varchar" <?=$type == 'varchar' ? 'CHECKED' : ''?>><?=T_('Regular Fields')?></LABEL><BR>
  <LABEL><INPUT type="radio" name="type" value="text" <?=$type == 'text' ? 'CHECKED' : ''?>><?=T_('Large Fields (description fields mainly)')?></LABEL><BR>
<BR>

<LABEL><?=T_('Language')?>:
  <SELECT name="lang">
    <?
      foreach($langs as $lang_id => $lang_name) {
        if($lang_id == 'en') continue; //default is english to don't try to translate it
        echo "<OPTION value='$lang_id'" . ($lang_id == $lang ? ' SELECTED' : '') . ">" . es($lang_name);
      }
    ?>
  </SELECT></LABEL><BR>

<LABEL><?=T_('Lines per page')?>:
  <SELECT name="lines">
    <OPTION <?=$lines == 10 ? 'SELECTED' : ''?>>10
    <OPTION <?=$lines == 25 ? 'SELECTED' : ''?>>25
    <OPTION <?=$lines == 50 ? 'SELECTED' : ''?>>50
    <OPTION <?=$lines == 100 ? 'SELECTED' : ''?>>100
    <OPTION <?=$lines == 200 ? 'SELECTED' : ''?>>200
  </SELECT></LABEL><BR>

<INPUT type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<? if(isset($text)): ?>
<? if(mysql_num_rows($text)): ?>
<FORM action="admin_translate.php" method="post" accept-charset="UTF-8">
<TABLE class="plain_grid align_th_<?=$align_start;?>">
<? $line = 1; ?>
<? while($row = mysql_fetch_assoc($text)): ?>
<? if(!isset($first_line)) $first_line = $row['text']; ?>
<TR>
  <TD>
    <INPUT type="hidden" name="text[<?=$line?>][text]" value="<?=es($row['text'])?>"><BR>
    <? if($type == 'text'): ?>
      <TEXTAREA name="text[<?=$line?>][text_transl]" rows="3" cols="50"><?=es($row['text_transl'])?></TEXTAREA>
    <? else: ?>
      <INPUT type="text" name="text[<?=$line?>][text_transl]" value="<?=es($row['text_transl'])?>" size="50" maxlength="255">
    <? endif; ?>
    <BR><BR>
  </TD>
  <TH><BR><PRE><?=es($row['text'])?></PRE></TH>
</TR>
<? $line++; ?>
<? $last_line = $row['text']; ?>
<? endwhile; ?>
<TR><TD style="text-align: center;" colspan="2">
<INPUT type="hidden" name="action" value="<?=es($action)?>">
<INPUT type="hidden" name="type" value="<?=es($type)?>">
<INPUT type="hidden" name="lang" value="<?=es($lang)?>">
<INPUT type="hidden" name="lines" value="<?=$lines?>">

<INPUT type="hidden" name="first_line" value="<?=es($first_line)?>">
<INPUT type="hidden" name="last_line" value="<?=es($last_line)?>">

<INPUT type="submit" name="next" value="<?=es(T_("<< Save, and go to previous page"))?>">
&nbsp; &nbsp; &nbsp; &nbsp;
<INPUT type="submit" name="next" value="<?=es(T_("Save, and go to next page >>"))?>">
<BR><BR><BR>
<INPUT type="submit" name="next" value="<?=es(T_("<< Don't save, and go to previous page"))?>">
&nbsp; &nbsp; &nbsp; &nbsp;
<INPUT type="submit" name="next" value="<?=es(T_("Don't save, and go to next page >>"))?>">
</TD></TR>
</TABLE>
</FORM>
<? else: ?>
<P>
<?=$where ? T_('No results at this page, press Go to start from the beginning.') : T_('No results.')?>
</P>
<? endif; ?>
<? else: ?>
<P style="color: red;">
<?=T_('Choose an Action.')?>
</P>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
