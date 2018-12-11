<?
require_once('calendar.php');
$id = $tasks_ae_row['task_id'];

$name = $tasks_ae_row['name'];

$repeat = $tasks_ae_row['rep_type'];
if($repeat == 'daily' && $tasks_ae_row['start_date'] == $tasks_ae_row['end_date']) $repeat = 'once';

$on_date_day = $tasks_ae_row['rep_param1'];
$on_date_month = $tasks_ae_row['rep_param2'];

$on_sunday = $tasks_ae_row['rep_param1'] & 1<<0;
$on_monday = $tasks_ae_row['rep_param1'] & 1<<1;
$on_tuesday = $tasks_ae_row['rep_param1'] & 1<<2;
$on_wednesday = $tasks_ae_row['rep_param1'] & 1<<3;
$on_thursday = $tasks_ae_row['rep_param1'] & 1<<4;
$on_friday = $tasks_ae_row['rep_param1'] & 1<<5;
$on_shabbos = $tasks_ae_row['rep_param1'] & 1<<6;

$start_date_cal = cal_from_jd($tasks_ae_row['start_date'], CAL_JEWISH);
$start_date_day = $start_date_cal['day'];
$start_date_month = $start_date_cal['month'];
$start_date_year = $start_date_cal['year'];

if(!empty($tasks_ae_row['end_date'])) {
  $end_date_cal = cal_from_jd($tasks_ae_row['end_date'], CAL_JEWISH);
  $end_date_day = $end_date_cal['day'];
  $end_date_month = $end_date_cal['month'];
  $end_date_year = $end_date_cal['year'];
} else {
  $end_date_day = '';
  $end_date_month = '';
  $end_date_year = '';
}

$every = $tasks_ae_row['every'];
?>
<TR>
  <TH><LABEL for="name"><?=T_('Name')?>:</LABEL></TH>
  <TD><INPUT type="text" name="tasks[<?=$id?>][name]" id="name" size=32 maxlength=255 value="<?= es($name) ?>"></TD>
</TR>
<TR>
  <TH><?=T_('Repeat')?>:</TH>
  <TD>
    <LABEL class="nobr"><INPUT type="radio" name="tasks[<?=$id?>][repeat]" value="once" <?= $repeat == 'once' ? 'CHECKED' : '' ?> onClick="shRepeat(this.value, <?=$id?>)"><?= T_('Once') ?></LABEL>
    <LABEL class="nobr"><INPUT type="radio" name="tasks[<?=$id?>][repeat]" value="daily" <?= $repeat == 'daily' ? 'CHECKED' : '' ?> onClick="shRepeat(this.value, <?=$id?>)"><?= T_('Daily') ?></LABEL>
    <LABEL class="nobr"><INPUT type="radio" name="tasks[<?=$id?>][repeat]" value="weekly" <?= $repeat == 'weekly' ? 'CHECKED' : '' ?> onClick="shRepeat(this.value, <?=$id?>)"><?= T_('Weekly') ?></LABEL>
    <LABEL class="nobr"><INPUT type="radio" name="tasks[<?=$id?>][repeat]" value="yearly" <?= $repeat == 'yearly' ? 'CHECKED' : '' ?> onClick="shRepeat(this.value, <?=$id?>)"><?= T_('Yearly') ?></LABEL>
    <BR>
    <LABEL class="nobr"><INPUT type="radio" name="tasks[<?=$id?>][repeat]" value="monthly_date" <?= $repeat == 'monthly_date' ? 'CHECKED' : '' ?> onClick="shRepeat(this.value, <?=$id?>)"><?= T_('Monthly&nbsp;by&nbsp;Date') ?></LABEL>
    <LABEL class="nobr"><INPUT disabled type="radio" name="tasks[<?=$id?>][repeat]" value="monthly_week" <?= $repeat == 'monthly_week' ? 'CHECKED' : '' ?> onClick="shRepeat(this.value, <?=$id?>)"><?= T_('Monthly&nbsp;by&nbsp;Week') ?></LABEL>
  </TD>
</TR>
<TR>
  <TH><?=T_('Start Date')?>:</TH>
  <TD DIR="rtl" ALIGN="<?= $align_start ?>">
    <SELECT NAME="tasks[<?=$id?>][start_date_day]" DIR='rtl'>
      <? selectDay($start_date_day); ?>
    </SELECT>
    <SELECT NAME="tasks[<?=$id?>][start_date_month]" DIR='rtl'>
      <? selectMonth($start_date_month); ?>
    </SELECT>
    <SELECT NAME="tasks[<?=$id?>][start_date_year]" DIR='rtl'>
      <? selectYear($start_date_year); ?>
    </SELECT>
  </TD>
</TR>
<TR id="tasks_<?=$id?>_end_date_tr">
  <TH><?=T_('End Date')?>:</TH>
  <TD DIR="rtl" ALIGN="<?= $align_start ?>">
    <SELECT NAME="tasks[<?=$id?>][end_date_day]" DIR='rtl'>
      <OPTION></OPTION>
      <? selectDay($end_date_day); ?>
    </SELECT>
    <SELECT NAME="tasks[<?=$id?>][end_date_month]" DIR='rtl'>
      <OPTION></OPTION>
      <? selectMonth($end_date_month); ?>
    </SELECT>
    <SELECT NAME="tasks[<?=$id?>][end_date_year]" DIR='rtl'>
      <OPTION></OPTION>
      <? selectYear($end_date_year); ?>
    </SELECT>
  </TD>
</TR>
<TR id="tasks_<?=$id?>_every_tr">
  <TH><LABEL for="every"><?=T_('Every')?>:</LABEL></TH>
  <TD>
    <SELECT name="tasks[<?=$id?>][every]" id="every">
    <?
      for($i = 1; $i <= 24; $i++) {
        echo "<OPTION value='$i'" . ($i == $every ? ' SELECTED' : '') . ">$i</OPTION>\n";
      }
    ?>
    </SELECT> <SPAN id="tasks_<?=$id?>_every_period"></SPAN>
  </TD>
</TR>
<TR id="tasks_<?=$id?>_on_tr">
  <TH><?=T_('On')?>:</TH>
  <TD>
    <SELECT name="tasks[<?=$id?>][on_date_day]" DIR="rtl">
      <? selectDay($on_date_day); ?>
    </SELECT> <SPAN id="tasks_<?=$id?>_on_date_day_chaser" DIR="<?=$dir?>"><?= T_('ל will repeat on כט if the month is חסר') ?></SPAN>
    <SELECT name="tasks[<?=$id?>][on_date_month]" DIR="rtl">
      <? selectMonth($on_date_month); ?>
    </SELECT> <SPAN id="tasks_<?=$id?>_on_date_month_chaser" DIR="<?=$dir?>"><?= T_('ל will repeat on the following א if the month is חסר that year') ?></SPAN>
    <SPAN id="tasks_<?=$id?>_on_weekdays">
    <LABEL class="nobr"><INPUT type="checkbox" name="tasks[<?=$id?>][on_sunday]" <?= (!empty($on_sunday) ? 'CHECKED' : '') ?>><?= T_('Sunday')?></LABEL>
    <LABEL class="nobr"><INPUT type="checkbox" name="tasks[<?=$id?>][on_monday]" <?= (!empty($on_monday) ? 'CHECKED' : '') ?>><?= T_('Monday')?></LABEL>
    <LABEL class="nobr"><INPUT type="checkbox" name="tasks[<?=$id?>][on_tuesday]" <?= (!empty($on_tuesday) ? 'CHECKED' : '') ?>><?= T_('Tuesday')?></LABEL>
    <BR>
    <LABEL class="nobr"><INPUT type="checkbox" name="tasks[<?=$id?>][on_wednesday]" <?= (!empty($on_wednesday) ? 'CHECKED' : '') ?>><?= T_('Wednesday')?></LABEL>
    <LABEL class="nobr"><INPUT type="checkbox" name="tasks[<?=$id?>][on_thursday]" <?= (!empty($on_thursday) ? 'CHECKED' : '') ?>><?= T_('Thursday')?></LABEL>
    <LABEL class="nobr"><INPUT type="checkbox" name="tasks[<?=$id?>][on_friday]" <?= (!empty($on_friday) ? 'CHECKED' : '') ?>><?= T_('Friday')?></LABEL>
    <BR>
    <LABEL class="nobr"><INPUT type="checkbox" name="tasks[<?=$id?>][on_shabbos]" <?= (!empty($on_shabbos) ? 'CHECKED' : '') ?>><?= T_('Shabbos')?></LABEL>
    </SPAN>
    <SCRIPT type="text/javascript">
      shRepeat('<?=$repeat?>', <?=$id?>);
    </SCRIPT>
  </TD>
</TR>
