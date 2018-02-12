<? 
$admin_auth = array('school'); 
 
require('header.php'); 
require_once('calendar.php'); 

$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);

$prize_shipped = gri('prize_shipped', 0);

if (!empty($action)) {

	switch($action) {
		case 'mark':
			$store_purchase_id = gri('store_purchase_id', -1);
			mq("UPDATE store_purchases SET scan_date=NOW() WHERE store_purchase_id=" . $store_purchase_id);
		break;
		
		case 'unmark':
			$store_purchase_id = gri('store_purchase_id', -1);
			$sql = "UPDATE store_purchases SET scan_date=NULL WHERE store_purchases.store_purchase_id=" . $store_purchase_id;
			//echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n";
			mq($sql);
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Manage Store purchases'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<H1>
				<?=T_('Manage Store purchases')?>
			</H1>
			
			<? if(!empty($message)): ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>
			
			<? if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1): ?>
			
				<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

				<FORM action="admin_store.php" method="get" accept-charset="UTF-8">
					<P>
						<?=T_('Select Institution')?>: 
						
						<SELECT name="school_id">
							<? while($school_row = mysql_fetch_assoc($school_result)): ?>

								<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
								
							<?endwhile;?>
						</SELECT> 
						
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
					</P>
				</FORM>
				
				<HR>
			
			<?endif;?>

			<?if($school_id == -1):?>
			
				<?=T_('Please select an Institution.')?>
				
			<?else:?>

				<P>

				<? if ($prize_shipped == 0) : ?>
					<A HREF="admin_store.php?school_id=<?=$school_id?>&amp;prize_shipped=1">
						<?=T_('Show only un-shipped prizes')?>
					</A>
					<? $caption = T_('Shipped prizes'); ?>
					<? $action = "unmark"; ?>
					<? $message = "Mark as unshipped"; ?>								
				<? else: ?>
					<A HREF="admin_store.php?school_id=<?=$school_id?>&amp;prize_shipped=0">
						<?=T_('Show prizes that were already shipped')?>
					</A>
					<? $caption = T_('Un-Shipped prizes'); ?>					
					<? $action = "mark"; ?>
					<? $message = "Mark as shipped"; ?>					
				<? endif; ?>

				</P>

				<? 
					if ($prize_shipped == 0)
						$sql = "SELECT sp.*, u.first, u.last, c.class_grade, c.class_sub, ps.prize_name, sp.prize_date, TIME(sp.prize_date) AS prize_time FROM store_purchases AS sp JOIN prizes_store AS ps USING (prize_id) JOIN users AS u USING (user_id) LEFT JOIN classes AS c USING (class_id)  WHERE sp.code_id > 0 AND sp.scan_date AND u.school_id=" . $school_id; 
					else 
						$sql = "SELECT sp.*, u.first, u.last, c.class_grade, c.class_sub, ps.prize_name, sp.prize_date, TIME(sp.prize_date) AS prize_time FROM store_purchases AS sp JOIN prizes_store AS ps USING (prize_id) JOIN users AS u USING (user_id) LEFT JOIN classes AS c USING (class_id)  WHERE isnull(sp.scan_date) > 0 AND u.school_id=" . $school_id; 					
						
					$request = mq($sql);
				?>
				
				
				<TABLE class="pretty_grid">
					<CAPTION>
						<?=$caption?>
					</CAPTION>
					
					<TR>
						<TH><?=T_('Grade')?></TH>
						<TH><?=T_('Name')?></TH>
						<TH><?=T_('Prize')?></TH>
						<TH><?=T_('Purchase Date')?></TH>
						<TH><?=T_('Points')?></TH>
						<TH>&nbsp;</TH>
					</TR>

				<? while($row = mysql_fetch_assoc($request)): ?>
					<TR>
						<TD>
							<?=es($row['class_grade'])?>-<?=es($row['class_sub'])?>
						</TD>
						
						<TD>
							<?=es($row['first'])?> <?=es($row['last'])?>
						</TD>
						
						<TD>
							<?=es($row['prize_name'])?>
						</TD>
						
						<TD>							
							<?=$row['prize_date'];?> <?=$row['prize_time'];?>
						</TD>
						
						<TD STYLE="text-align: right;">
							<?=$row['prize_points']?>
						</TD>
						
						<TD>
							<A HREF="admin_store.php?store_purchase_id=<?=$row['store_purchase_id']?>&action=<?=$action;?>&school_id=<?=$school_id;?>"><?=$message;?></A>
						</TD>
					</TR>
				<? endwhile; ?>
										
				</TABLE>

					
			<?endif;?>

		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
