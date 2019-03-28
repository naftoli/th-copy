<? $dual_auth = true; ?>
<? $admin_auth = array('school', 'class', 'team', 'user'); ?>
<? require('header.php'); ?>
<?
require_once('file_save.php');

$lines=5;
$cols=2;

$row = mysql_fetch_assoc(mq('SELECT auction_id FROM auctions WHERE auction_date < ' . unixtojd() . ' AND auction_ran = 0 AND (auction_run_date IS NULL OR auction_run_date > ' . unixtojd() . ') AND school_id IS NULL ORDER BY auction_date DESC LIMIT 1'));
if($row)
  $auction_id = $row['auction_id'];
else
  $auction_id = -1;

$prizes = mq("SELECT prize_id, prize_name, prize_number, prize_points, prize_image_id, min_grade, max_grade FROM prizes_auction JOIN auction_prizes USING (prize_id) JOIN auctions USING (auction_id) WHERE auction_id = $auction_id AND (max_prize_points IS NULL OR prize_points <= max_prize_points) AND prize_number IS NOT NULL AND prizes_auction.school_id IS NULL ORDER BY prize_number");

$sheets = gra('sheets');
$set = gri('set', 0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Print Auction Cards'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
.cards {
  margin: auto;
  page-break-after: always;
}

.cards td {
  width: 3.375in;
  height: 2.125in;
  text-align: center;
  vertical-align: middle;
  border: 1px dashed black;
}

.cards td td {
  width: auto;
  height: auto;
  border: none;
}

.cards td .barcode img {
  width: 2.43in;
  height: .3in;
}

.cards td img {
  height: .6in;
  max-width: 2.43in;
}

.cards div {
  margin: 0px .25in;
  padding: .1in 0px;
  height: 1.425in; /* 2.125 - 2 * .25 - 2 * .1 */
  position: relative;
}

.cards p {
  padding: 0px;
  margin-top: 0px;
  margin-bottom: .05in;
}

.border {
  border: .03in solid black !important;
  -webkit-border-radius: .04in;
  -moz-border-radius: .04in;
  border-radius: .04in;
}

.cards .points {
  position: absolute;
  width: 2.875in; /* 3.375 - 2 * .25 */
  margin: 0px;
  padding: 0px;
  font-size: 0.1in;
  line-height: .2in;
  bottom: -.13in; /* line-height/2 + border */
  height: auto;
  text-transform: uppercase;
}

.cards .points table {
  margin: auto;
}

.cards .points td {
  padding: 0px;
  font-weight: bold;
  font-size: 150%;
}

.cards .points td div {
  height: auto;
  margin: 0px;
  padding: 0px .1in;
  background-color: #e0f3d2;
}

@media print {
  .cards td, .backs td {
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
<H1><?=T_('Print Auction Cards')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<H2>Printing Instructions</H2>
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
<HR>

<?if($auction_id == -1):?>
<P>
<?=T_("Can't find any active auctions; that have not run, and have a cut-off date before today.")?>
</P>
<?else:?>
<FORM action="admin_auction_print.php" method="get" accept-charset="UTF-8">
<H2><?=T_('Print Full Set with 1 card of each prize')?></H2>
<P>
<INPUT type="hidden" name="set" value="1">
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<FORM action="admin_auction_print.php" method="get" accept-charset="UTF-8">
<H2><?=T_('Print Sheets with 10 cards per sheet')?></H2>
<TABLE class="pretty_grid">
<TR>
  <TH><?=T_('Prize Number')?></TH>
  <TH><?=T_('Points')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH>
    <?=T_('# of sheets (10 cards per sheet)')?><BR>
    <LABEL><?=T_('Change All')?> <SELECT onChange="if(this.selectedIndex) for(i=0; i&lt;this.form.elements.length; i++) if(this.form.elements[i].type=='select-one' &amp;&amp; this.form.elements[i].name.substring(0, 7) == 'sheets[') this.form.elements[i].selectedIndex = this.selectedIndex-1; this.selectedIndex=0;">
    <OPTION>
    <?for($i=0; $i<=10; $i++):?>
    <OPTION value="<?=$i?>"><?=$i?>
    <?endfor;?>
    </SELECT></LABEL>
  </TH>
</TR>
<?while($row = mysql_fetch_assoc($prizes)):?>
<TR>
  <TD><?=$row['prize_number']?></TD>
  <TD><?=$row['prize_points']?></TD>
  <TD><LABEL for="sheets_<?=$row['prize_id']?>"><?=es($row['prize_name'])?></LABEL></TD>
  <TD>
    <SELECT name="sheets[<?=$row['prize_id']?>]" id="sheets_<?=$row['prize_id']?>">
    <?for($i=0; $i<=10; $i++):?>
    <OPTION value="<?=$i?>" <?=(isset($sheets[$row['prize_id']]) && $sheets[$row['prize_id']] == $i) || (!isset($sheets[$row['prize_id']]) && $i == 1) ? 'selected' : ''?>><?=$i?>
    <?endfor;?>
    </SELECT>
  </TD>
</TR>
<?endwhile;?>
</TABLE>
<P>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>
<HR>
</DIV>
<?if($sheets || $set):?>
<P class="noprint" style="text-align: center;"><INPUT type="button" value="<?=T_('Print')?>" onClick="print();"></P>
<?
@mysql_data_seek($prizes, 0);
while($set && $row || ($row = mysql_fetch_assoc($prizes))):
if(!$set && !isset($sheets[$row['prize_id']])) continue;
$copies = $set ? 1 : min(max(intval($sheets[$row['prize_id']]), 0), 10);
$prize_number = '81' . str_pad($row['prize_number'], 8, '0', STR_PAD_LEFT) . '5406078406';
for($copy = 0; $copy < $copies; $copy++):
?>
<TABLE class="cards">
<? for($line=0; $line<$lines; $line++): ?>
<TR>
<? for($col=0; $col<$cols; $col++): ?>
<TD>
<? if($row): ?>
<DIV class="border">
<P>#<?=$row['prize_number']?> - <?=es($row['prize_name'])?></P>
<P><?=!is_null($row['prize_image_id']) ? linkImgFile($row['prize_image_id']) : ''?></P>
<P class="barcode"><IMG SRC="barcode.php/<?=$prize_number?>" alt=""><BR><?=$prize_number?></P>
<DIV class="points"><TABLE><TR><TD><DIV class="border"><?=$row['prize_points'], ' ', T_('Miles')?></DIV></TD></TR></TABLE></DIV>
</DIV>
<? endif; ?>
</TD>
<?
if($set) {
  $row = mysql_fetch_assoc($prizes);
  $prize_number = '81' . str_pad($row['prize_number'], 8, '0', STR_PAD_LEFT) . '5406078406';
}
?>
<? endfor; ?>
</TR>
<? endfor; ?>
</TABLE>
<HR>
<? endfor; ?>
<? endwhile; ?>
<? endif; ?>
</DIV>
<DIV class="noprint"><? include('admin_footer.php'); ?></DIV>
</BODY>
</HTML>
