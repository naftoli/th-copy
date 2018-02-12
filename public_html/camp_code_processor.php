<?
function process_code($scan_code) {
	global $user, $code_row, $code_subject, $camp_id;
	global $camp_name;
	global $camp_logo_id;
	
	$sql = "SELECT * FROM camp_card_codes WHERE code_id=" . $scan_code;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	if ($num_rows == 0) {
		return "Code could not be found or was already processed.";
	}
	else {
		$row = mysql_fetch_assoc($query);
		$sql = "INSERT INTO member_points (user_id, points, points_date) VALUES (" . $user['user_id'] . ", " . $row['points'] . ", CURDATE())";
		$query = mysql_query($sql);		
		if ($query) {
			$sql = "DELETE FROM camp_card_codes WHERE code_id=" . $scan_code;
			$query = mysql_query($sql);
			return array('code'=>substr($scan_code, 1), 'points'=>$row['points'], 'camp_id'=>$camp_id, 'camp_name'=>$camp_name, 'left_circle'=>$row['left_circle'], 'right_circle'=>$row['right_circle'], 'camp_logo_id'=>$camp_logo_id);
		}
				
	}	
}
if ($scan_code = gr('scan_code')) {
  $message = process_code($scan_code);
?>

	<SCRIPT type="text/javascript">
		$(function () {
		  $('#wrapper').css("opacity", 0.3);
		  $('#carddisplay').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px');
		  $(document).bind("keyup.carddisplay", function (e) {
			if(e.keyCode==27) {
			  $('#close_pop a').click();
			  $(document).unbind("keyup.carddisplay");
			}
		  });
		});
	</SCRIPT>
	
	<div id="carddisplay" style="position: fixed; z-index: 100; width: 100%;">
	
		<div class="cardpop" style="margin: auto; color: white; background-color: #314239; overflow: auto;">
		
			<div style="padding: 20px;">
			
				<div id="close_pop">
					<A HREF="#" onClick="$('#wrapper').fadeTo('normal', 1); document.getElementById('carddisplay').style.display = 'none'; if(document.getElementById('focus')) document.getElementById('focus').focus(); return false;">Close</A>
				</div>
				
				<div id="page_title">
					<?=T_('Card Scanned')?>
				</div>
				
		<? if(is_array($message)): ?>
		
			<? if(isset($message['prize_number'])): ?>
				<DIV class="message">
					<?=sprintf(T_('Available balance remaining for this auction: %s'), number_format($message['user_points'] - $message['auction_points'], 2));?>
				</DIV>
				
				<TABLE class="card" style="clear: both; margin: auto; float: none;">
					<TR>
						<TD>
							<DIV class="border">
								<P>#<?=$message['prize_number']?> - <?=es($message['prize_name'])?></P>
								<P><?=!is_null($message['prize_image_id']) ? linkImgFile($message['prize_image_id']) : ''?></P>
								<P class="barcode"><IMG SRC="barcode.php/<?=$message['code']?>" alt=""><BR><?=$message['code']?></P>
								
								<DIV class="points">
									<TABLE>	
										<TR>
											<TD>
												<DIV class="border"><?=$message['prize_points'], ' ', T_('Miles')?></DIV>
											</TD>
										</TR>
									</TABLE>
								</DIV>								
							</DIV>
						</TD>
					</TR>
				</TABLE>
				
				<DIV class="message" style="clear: both;">
					<?=T_('Your prize selection has been entered.');?>
				</DIV>
				
				<? $result = mq("SELECT prize_name, prize_points, quantity, prize_number FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = {$message['auction_id']} AND user_id = {$user['user_id']} ORDER BY prize_number"); ?>
				
				<BR style="clear: both;">
				
				<? if(mysql_num_rows($result)): ?>
				<BR>
				
				<TABLE class="transactions">
					<TR>
						<TH><?=T_('Prize #')?></TH>
						<TH><?=T_('Prize')?></TH>
						<TH><?=T_('Quantity')?></TH>
						<TH><?=T_('Miles Each')?></TH>
						<TH><?=T_('Miles Total')?></TH>
						<TH><?=T_('Balance')?></TH>
					</TR>
					<? $toggle = true; ?>
					<? $running_balance = $message['user_points'] - $message['auction_points'];?>
					<? while($row = mysql_fetch_assoc($result)): ?>
					<TR class="<?=($toggle = !$toggle) ? 'even' : 'odd'?>">
						<TD><?=$row['prize_number']?></TD>
						<TD><?=es($row['prize_name'])?></TD>
						<TD><?=number_format($row['quantity'])?></TD>
						<TD><?=number_format($row['prize_points'])?></TD>
						<TD><?=number_format($row['prize_points']*$row['quantity'])?></TD>
						<TD><?=number_format($running_balance, 2)?></TD>
					</TR>
					<? $running_balance += ($row['prize_points']*$row['quantity']); ?>
					<? endwhile; ?>
				</TABLE>				
				<? endif; ?>
				
			<? elseif(isset($message['line'])): ?>
				<DIV class="message">
					<?=sprintf(T_('You have recorded that you learned line <B>%d</B> of tanya:<BR><B>%s</B><BR>From page %d, פרק %s'), $message['line'], $message['text'], $message['page'], $message['perek'])?>
					<BR>
					<? if($message['line'] == $message['years_goal']): ?>
					<?=T_('<DIV>Mazal Tov</DIV> You have completed your yearly goal. It is time to review your growth planner with your base commander.')?>
					<? else: ?>
					<?=sprintf(T_('You need to learn %s lines per week for the next %s weeks in order to reach this years goal of %s lines.'), $message['lines_per_week'], round($message['days_left']/7, 1), $message['years_goal'])?>
					<? endif; ?>
				</DIV>			
			<? elseif(isset($message['new_pledge'])): ?>
				<DIV class="message">
					<?=sprintf(T_("Your pledges have been increased<BR>from %s to %s."), money_format('%n', $message['old_pledges']), money_format('%n', $message['old_pledges'] + $message['new_pledge']))?>
				</DIV>
			<? else: ?>
				<? if($user['registered']): ?>
				<?$row = mysql_fetch_assoc(mq("SELECT school_name, school_city, school_state, school_logo_id, school_number FROM schools WHERE school_id = {$message['school_id']}")); ?>
				<DIV style="float: left;" class="card_shadow">
					<?=display_card_back($message['code'], floatval($message['points']), '', $message['left_circle'], $message['right_circle'], $message['description'], $message['subject_name'], $message['subject_image_id'], $message['series'])?>
				</DIV>
				<DIV class="message">
					<?=sprintf(T_("<B>Mazal Tov on your achievement.</B><BR>You've earned<DIV>%s</DIV>Miles for %s."), floatval($message['points']), $message['subject_name'])?>
				<? else: ?>
				<DIV style="float: left;" class="card_shadow">
					<?=display_card_back($message['code'], floatval($message['points']), '', $message['camp_id'], $message['camp_name'], $message['left_circle'], $message['right_circle'], $message['camp_logo_id'],'')?>
				</DIV>
					<? if(floatval($message['points'] == 1)): ?>
					<?=sprintf(T_("<B>Mazal Tov on your achievement.</B><BR>You've earned<DIV>%s</DIV>Mile."), floatval($message['points']))?>
					<? else: ?>
					<?=sprintf(T_("<B>Mazal Tov on your achievement.</B><BR>You've earned<DIV>%s</DIV>Miles."), floatval($message['points']))?>
					<? endif; ?>
				<? endif; ?>

				<? if(isset($message['medal_name'])): ?>
				<BR>
					<? if($message['medal_ord'] == floor($message['medal_ord'])): ?>
					<?=sprintf(T_("And you have just been awarded the %s medal."), es($message['medal_name']))?>
					<? else: ?>
					<?=sprintf(T_("And you are %%%d of the way to the %s medal."), ($message['medal_ord']-floor($message['medal_ord']))*100, es($message['medal_name']))?>
					<? endif; ?>
				<? endif; ?>

			</DIV>
			<? endif; ?>
		<? else: ?>
			<P><?=$message;?></P>
		<? endif; ?>
		</div>
	</div>
	
</div>
<?
}
?>
