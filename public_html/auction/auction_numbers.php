<?
$admin_auth = array('school'); 
require('../header.php');  
require('../calendar.php');

require('auction.php');
$auctions = array();
///////////$sql = "SELECT * FROM auctions WHERE auction_ran=1 AND approved=1 ORDER BY auction_id DESC"; 
$sql = "SELECT * FROM auctions WHERE auction_id=26 ORDER BY auction_id DESC"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$auction_select = new auction($row);
	array_push($auctions, $auction_select);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<TITLE>
			Auction Numbers - Tzivos Hashem Management System
		</TITLE>
		
		<LINK href="../admin_styles.css" rel="stylesheet" type="text/css">		
	</HEAD>
	
	<BODY>
		<? include('../admin_header.php'); ?>
				
		<SCRIPT type="text/javascript" src="../jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="../jquery-ui.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="../scripts/jquery.tools.min.js"></SCRIPT>
		<script type="text/javascript" src="../scripts/jquery.styleselect.js"></script>

				
		<script type="text/javascript">	
			$(document).ready(function() {

				var url = "http://mashpia.com/auction/auction_ajax_numbers.php?auction_id=" + $(".auction_id select").val();
				
				$.ajax({
					url: url,
					success: function(data) {
						$('#auction_numbers').html(data);
					}
				});			
			
			});		
			
			$(function() {
			
				$('.marking_list div select').each(function() {
					if (!$(this).find('option:selected').next().val()) $(this).siblings('a.next').addClass('disabled');
					if (!$(this).find('option:selected').prev().val()) $(this).siblings('a.prev').addClass('disabled');
				});
			
				$('.marking_list div a.next').click(function(){
					$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
				});
				
				$('.marking_list div a.prev').click(function(){
					$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
				});
			
				$(".auction_id select").sSelect().change(function () {
					
					var url = "http://mashpia.com/auction/auction_ajax_numbers.php?auction_id=" + $(this).val();
					
					$.ajax({
						url: url,
						success: function(data) {
							$('#auction_numbers').html(data);
						}
					});			
					
				})
			
			});
		</script>
		
		<DIV CLASS="body">
			<H1>Auction Numbers</H1>
			
			<P>
				<FORM method="post" action="auction_numbers.php">
					
					<div class="infobox2 marking_list clearfix">
					
						<div class="auction_id select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous School')?></span>
							</a>
							
							<!-- ********** AUCTION ID ********** -->
							<SELECT name="auction_id" id="auction_id" class="sSelect">
								<? foreach ($auctions as $auction_select) : ?>
									<? if ($auction_id == $auction_select->auction_id) : ?>
									<OPTION selected value="<?=$auction_select->auction_id;?>"><?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
									<? else : ?>
									<OPTION value="<?=$auction_select->auction_id;?>"><?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
									<? endif; ?>						
								<? endforeach; ?>
							</SELECT> 
							<!-- ********** AUCTION ID ********** -->
						
							<a class="next button">
								<span class="icon"></span>
								<span class="label"><?=T_('Next School')?></span>
							</a>						
						
						</div>

					</div>
					
				</FORM>
			</P>
				
			<br />

			<DIV name="auction_numbers" id="auction_numbers">
			</DIV>
			
		</DIV>
		
	</BODY>
	
</HTML>
