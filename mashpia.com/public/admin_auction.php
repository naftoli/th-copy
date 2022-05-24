<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');

$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    //$prize_ids = array();
    $result = mq('SELECT -1 auction_id, NULL school_id, \'\' auction_name, NULL auction_points_start_date, ' . unixtojd() . ' auction_date, NULL auction_points_trigger_date, NULL auction_run_date, \'\' auction_message, NULL max_prize_points');
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $auction_points_start_date = nullif_max(gr('auction_points_start_date'), 4000075);
    $auction_date = gri('auction_date', unixtojd());
    $auction_points_trigger_date = nullif_max(gr('auction_points_trigger_date'), 4000075);
    $auction_run_date = nullif_max(gr('auction_run_date'), 4000075);

    $result = mq("SELECT 1 FROM auctions WHERE auction_date = $auction_date AND school_id <=> " . nullif($school_id, -1));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new auction, this date is already used.');
      $result = mq('SELECT -1 auction_id, ' . ms(gr('auction_name')) . ' auction_name, ' . nullif($school_id, -1) . " school_id, $auction_points_start_date auction_points_start_date, $auction_date auction_date, $auction_points_trigger_date auction_points_trigger_date, $auction_run_date auction_run_date, " . ms(gr('auction_message')) . ' auction_message, ' . nullif_max(gr('max_prize_points'), 4294967295) . ' max_prize_points');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';

      $prize_ids = array();
      foreach(gra('prizes') as $prize_id => $data) {
        if(!isset($data['active'])) continue;
        $prize_ids[intval($prize_id)] = $data['available'];
      }

    } else {
      mq('INSERT INTO auctions SET auction_name = ' . ms(gr('auction_name')) . ", auction_points_start_date = $auction_points_start_date, auction_date = $auction_date, auction_points_trigger_date = $auction_points_trigger_date, auction_run_date = $auction_run_date, school_id = " . nullif($school_id, -1) . ', auction_message = ' . ms(gr('auction_message')) . ', max_prize_points = ' . nullif_max(gr('max_prize_points'), 4294967295));

      foreach(gra('prizes') as $prize_id => $data) {
        if(!isset($data['active'])) continue;
        $prize_id = intval($prize_id);
        $available = nullif_max($data['available'], 65535);

        mq("INSERT IGNORE INTO auction_prizes (auction_id, prize_id, available) SELECT LAST_INSERT_ID() auction_id, prize_id, $available available FROM prizes_auction WHERE school_id <=> " . nullif($school_id, -1) . " AND prize_id = $prize_id");
      }
      $message = T_('Auction added');
    }
    break;

  case 'delete':
    mq('DELETE FROM auctions WHERE auction_id = ' . gri('auction_id', -1));
    mq('DELETE FROM auction_prizes WHERE auction_id = ' . gri('auction_id', -1));
    $message = T_('Auction deleted');
    break;

  case 'edit':
    $auction_id = gri('auction_id', -1);
    $result = mq("SELECT prize_id, available FROM auction_prizes WHERE auction_id = $auction_id");
    $prize_ids = mysql_fetch_column($result);
    $result = mq("SELECT auction_id, school_id, auction_name, auction_points_start_date, auction_date, auction_points_trigger_date, auction_run_date, auction_message, max_prize_points FROM auctions WHERE auction_id = $auction_id");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $auction_id = gri('auction_id', -1);
    $auction_points_start_date = nullif_max(gr('auction_points_start_date'), 4000075);
    $auction_date = gri('auction_date', unixtojd());
    $auction_points_trigger_date = nullif_max(gr('auction_points_trigger_date'), 4000075);
    $auction_run_date = nullif_max(gr('auction_run_date'), 4000075);

    $result = mq("SELECT 1 FROM auctions WHERE auction_date = $auction_date AND school_id <=> " . nullif($school_id, -1) . " AND auction_id != $auction_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit auction, this date is already used.');
      $result = mq("SELECT $auction_id auction_id, $auction_points_start_date auction_points_start_date, $auction_date auction_date, $auction_points_trigger_date auction_points_trigger_date, $auction_run_date auction_run_date, " . ms(gr('auction_name')) . ' auction_name, ' . ms(gr('auction_message')) . ' auction_message, ' . nullif_max(gr('max_prize_points'), 4294967295) . ' max_prize_points');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      mq('UPDATE auctions SET auction_name = ' . ms(gr('auction_name')) . ", auction_points_start_date = $auction_points_start_date, auction_date = $auction_date, auction_points_trigger_date = $auction_points_trigger_date, auction_run_date = $auction_run_date, auction_message = " . ms(gr('auction_message')) . ', max_prize_points = ' . nullif_max(gr('max_prize_points'), 4294967295) . " WHERE auction_id = $auction_id AND auction_ran = 0 AND school_id <=> " . nullif($school_id, -1));

      $prize_ids_list = '-1';

      foreach(gra('prizes') as $prize_id => $data) {
        if(!isset($data['active'])) continue;
        $prize_id = intval($prize_id);
        $available = nullif_max($data['available'], 65535);
        $prize_ids_list .= ",$prize_id";

        mq("INSERT INTO auction_prizes (auction_id, prize_id, available) SELECT auction_id, prize_id, $available available FROM prizes_auction JOIN auctions WHERE auction_id = $auction_id AND auction_ran = 0 AND (auctions.school_id = $school_id OR auctions.school_id IS NULL) AND (prizes_auction.school_id = $school_id OR prizes_auction.school_id IS NULL) AND prize_id = $prize_id ON DUPLICATE KEY UPDATE available = $available");
      }
      mq("DELETE FROM auction_prizes USING auction_prizes JOIN prizes_auction USING (prize_id) JOIN auctions USING (auction_id) WHERE auction_id = $auction_id AND auction_ran = 0 AND (auctions.school_id = $school_id OR auctions.school_id IS NULL) AND (prizes_auction.school_id = $school_id OR prizes_auction.school_id IS NULL) AND prize_id NOT IN ($prize_ids_list)");
      $message = T_('Auction edited');
    }
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Auctions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<script type="text/javascript" src="scripts/jquery.min.js"></script>
		<script type="text/javascript">
		    $( function() {       
		        function total() {
		            var t = 0;
    		        $(".qty").each( function() {
    		            val = $(".qty").val();
    		            if ( val ) {
    		                //alert( val );
    		            }
    		            t += val;
    		        });
    		        //$(".totalQty").html( t );
    		    }
    		    total();
		        
		        $(".qty").change( function() {
		            total();
		        });
		    });
		</script> 
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<H1>
				<?=T_('Auctions')?>
			</H1>
			
			<? if (!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>
			
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
			
	<? $school_result = mq('SELECT school_id, school_name FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name'); ?>
	
			<FORM action="admin_auction.php" method="get" accept-charset="UTF-8">
				<P>
					<LABEL>
						<?=T_('Select Institution')?>: 
						<SELECT name="school_id">
							<? if($admin_user['auth'] == 'super') : ?>
							<OPTION value="-1">&lt;<?=T_('Multi institution auctions')?>&gt;
							<? endif; ?>
							<? while($school_row = mysql_fetch_assoc($school_result)) : ?>
							<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
							<? endwhile; ?>
						</SELECT>
					</LABEL> 
					<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
				</P>
			</FORM>
			
			<HR>
<?endif;?>

<?if($edit_row):?>

			<FORM action="admin_auction.php" method="post" accept-charset="UTF-8">
				<P>
					<INPUT type="hidden" name="action" value="<?=$action?>2">
					<INPUT type="hidden" name="auction_id" value="<?=$edit_row['auction_id']?>">
					<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
					<LABEL>
						<?=T_('Auction Name')?>
						<BR>
						<INPUT type="text" name="auction_name" maxlength="255" value="<?=es($edit_row['auction_name'])?>">
					</LABEL>
					<BR>
					<LABEL>
						<?=T_('Auction Points Start Date')?>
						<BR>
						<SPAN>
							<INPUT type="text" name="auction_points_start_date_disp" READONLY value="<?=es(dateToHebrew($edit_row['auction_points_start_date']))?>" onClick="getDate(this.form, 'auction_points_start_date');">
						</SPAN>
					</LABEL>
					<INPUT type="hidden" name="auction_points_start_date" value="<?=$edit_row['auction_points_start_date']?>"> <?=T_('This is the start cut-off date (inclusive) for points. Leave blank for all points.')?>
					<BR>
					<LABEL>
						<?=T_('Auction Points End Date')?>
						<BR>
						<SPAN>
							<INPUT type="text" name="auction_date_disp" READONLY value="<?=es(dateToHebrew($edit_row['auction_date']))?>" onClick="getDate(this.form, 'auction_date', true);">
						</SPAN>
					</LABEL>
					<INPUT type="hidden" name="auction_date" value="<?=$edit_row['auction_date']?>"> <?=T_('This is the end cut-off date (inclusive) for points. Required.')?>
					<BR>
					<LABEL>
						<?=T_('Auction Points Trigger Date')?>
						<BR>
						<SPAN>
							<INPUT type="text" name="auction_points_trigger_date_disp" READONLY value="<?=es(dateToHebrew($edit_row['auction_points_trigger_date']))?>" onClick="getDate(this.form, 'auction_points_trigger_date');">
						</SPAN>
					</LABEL>
					<INPUT type="hidden" name="auction_points_trigger_date" value="<?=$edit_row['auction_points_trigger_date']?>"> <?=T_('This is the trigger date for points. Soldiers must earn enough points after this date in order to activate their previous points. Leave blank to not use this option.')?>
					<BR>
					<LABEL>
						<?=T_('Auction Run Date')?>
						<BR>
						<SPAN>
							<INPUT type="text" name="auction_run_date_disp" READONLY value="<?=es(dateToHebrew($edit_row['auction_run_date']))?>" onClick="getDate(this.form, 'auction_run_date');">
						</SPAN>
					</LABEL>
					<INPUT type="hidden" name="auction_run_date" value="<?=$edit_row['auction_run_date']?>"> <?=T_('This is the auction date as displayed on forms.')?>
					<BR>
					<LABEL>
						<?=T_('Auction Message')?>
						<BR>
						<INPUT type="text" name="auction_message" maxlength="255" size="70" value="<?=es($edit_row['auction_message'])?>">
					</LABEL> (<?=T_('Displayed as-is, including HTML')?>)
					<BR>
					<LABEL>
						<?=T_('Maximum prize points')?>
						<BR>
						<INPUT type="text" name="max_prize_points" maxlength="10" value="<?=es($edit_row['max_prize_points'])?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 4294967295));">
					</LABEL>  
					<?=T_('Maximum points (most expensize item) that can be used on a prize for this auction. Leave blank for no limit.')?>
					<BR>
					
					<? //$prizes = mq('SELECT prize_id, prize_name, prize_points FROM prizes_auction WHERE school_id <=> ' . nullif(gri('school_id', -1), -1) . ' ORDER BY prize_points, prize_name'); ?>
					<? $prizes = mq('SELECT prize_id, prize_name, prize_points FROM prizes_auction WHERE school_id <=> ' . nullif(gri('school_id', -1), -1) . ' ORDER BY ord, prize_points, prize_name'); ?>
					
					<TABLE class="pretty_grid">
						<CAPTION>
							<?=T_('Prizes')?> 
							- Total Qty: <span class='totalQty'></span>
						</CAPTION>
						
						<TR>
							<TH><INPUT type="checkbox" onClick="for(i=0; i &lt; this.form.elements.length; i++) if(this.form.elements[i].type == 'checkbox' &amp;&amp; this.form.elements[i].name.search(/prizes\[\d+\]\[active\]/) != -1) this.form.elements[i].checked = !this.form.elements[i].checked; this.checked = false;"></TH>
							<TH><?=T_('Name')?></TH>
							<TH><?=T_('Points')?></TH>
							<TH><?=T_('Quantity Available')?><BR><?=T_('Leave blank to use ratio')?></TH>
						</TR>
						
						<? while($prize = mysql_fetch_assoc($prizes)): ?>
						<TR class="input">
							<TD><INPUT type="checkbox" id="prize_<?=$prize['prize_id']?>" name="prizes[<?=$prize['prize_id']?>][active]" <?=!isset($prize_ids) || array_key_exists($prize['prize_id'], $prize_ids) ? 'CHECKED' : '' ?>></TD>
							<TD><LABEL for="prize_<?=$prize['prize_id']?>"><?=es($prize['prize_name'])?></LABEL></TD>
							<TD><LABEL for="prize_<?=$prize['prize_id']?>"><?=$prize['prize_points']?></LABEL></TD>
							<TD><INPUT type="text" class="qty" name="prizes[<?=$prize['prize_id']?>][available]" value="<?=isset($prize_ids[$prize['prize_id']]) ? $prize_ids[$prize['prize_id']] : ''?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));" maxlength="5" size="5"></TD>
						</TR>
						<? endwhile; ?>
					</TABLE>
					
					<P>
						<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
					</P>
					
			</FORM>

			<A HREF="admin_auction.php?school_id=<?=$school_id?>"><?=T_('Cancel')?></A>
			
<? else: ?>

			<?$result = mq('SELECT auction_id, auction_name, auction_points_start_date, auction_date, auction_points_trigger_date, auction_run_date, max_prize_points, auction_ran, school_name FROM auctions LEFT JOIN schools USING (school_id) WHERE school_id <=> ' . nullif($school_id, -1) . ' ORDER BY auction_date');?>

			<A HREF="admin_auction.php?action=add&amp;school_id=<?=$school_id?>"><?=T_('Add new Auction')?></A>

			<TABLE CLASS="list">
				<TR>
					<TH><?=T_('Ran?')?></TH>
					<TH><?=T_('Auction Name')?></TH>
					<TH><?=T_('Auction Points Start Date')?></TH>
					<TH><?=T_('Auction Points End Date')?></TH>
					<TH><?=T_('Auction Points Trigger Date')?></TH>
					<TH><?=T_('Auction Run Date')?></TH>
					<TH><?=T_('Max Prize Points')?></TH>
					<?if($admin_user['auth'] == 'super'):?>
						<TH><?=T_('Institution')?></TH>
					<?endif;?>
					<TH></TH>
					<TH></TH>
				</TR>
				
				<? while($row = mysql_fetch_assoc($result)): ?>
				<TR>
					<TD><?=$row['auction_ran'] ? T_('Yes') : ''?></TD>
					<TD><?=es($row['auction_name'])?></TD>
					<TD><?=es(dateToHebrew($row['auction_points_start_date']))?></TD>
					<TD><?=es(dateToHebrew($row['auction_date']))?></TD>
					<TD><?=es(dateToHebrew($row['auction_points_trigger_date']))?></TD>
					<TD><?=es(dateToHebrew($row['auction_run_date']))?></TD>
					<TD><?=$row['max_prize_points']?></TD>
					<?if($admin_user['auth'] == 'super'):?>
					<TD><?=es($row['school_name'])?></TD>
					<?endif;?>
					<TD><?=!$row['auction_ran'] ? "<A HREF=\"admin_auction.php?action=edit&amp;auction_id={$row['auction_id']}&amp;school_id={$school_id}\">" . T_('Edit Auction') . '</A>' : T_("Can't edit") ?></TD>
					<TD><?=!$row['auction_ran'] ? "<A HREF=\"admin_auction.php?action=delete&amp;auction_id={$row['auction_id']}&amp;school_id={$school_id}\" onClick=\"return confirm('<?=es(T_('Are you sure?'))?>')\">" . T_('Delete Auction') . '</A>' : T_("Can't delete") ?></TD>
				</TR>
				<? endwhile; ?>
			</TABLE>
<? endif; ?>

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
