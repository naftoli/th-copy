<?
$admin_auth = array('school'); 
require('header.php');  
require('calendar.php');

require('classes/school.php');
$schools = array();
$sql = "SELECT * FROM schools ORDER BY school_name"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$school = new \classes\school($row);
	array_push($schools, $school);
}


require('auction/auction.php');
$auctions = array();
$sql = "SELECT * FROM auctions WHERE auction_ran=1 AND approved=0 ORDER BY auction_id DESC"; 
///////////$sql = "SELECT * FROM auctions WHERE auction_id=26 ORDER BY auction_id DESC"; 
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
		<? include('admin_header.php'); ?>
				
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="scripts/jquery.tools.min.js"></SCRIPT>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		<script type="text/javascript">	
			$(document).ready(function() {
				var url = "http://mashpia.com/auction_ajax_numbers.php?auction_id=" + $(".auction_id select").val() + "&school_id=0";
				//alert(url);
				
				$.ajax({
					url: url,
					success: function(data) {
					    //alert( data );
						$('#auction_numbers').html(data);
					}
				});			
			
			});		
			
			$(function() {
			
				$('#export_winners').live('click', function() {
					
					
					var sql = "SELECT users.first, users.last, user_id, user_serial, school_name, school_number, classes.class_grade, classes.class_sub, prizes_auction.prize_name, prize_number, prize_id, auction_winners.quantity ";
					sql = sql + "FROM auction_winners ";
					sql = sql + "JOIN users USING (user_id) ";
					sql = sql + "JOIN prizes_auction USING (prize_id) ";
					sql = sql + "JOIN auctions USING (auction_id) ";
					sql = sql + "LEFT JOIN schools ON (users.school_id= schools.school_id) ";
					sql = sql + "LEFT JOIN classes USING (class_id) ";
					sql = sql + "WHERE auction_id=" + $("#auction_id").val() + " ";
					if ($("#school_id").val() != "0")
						sql = sql + "AND users.school_id=" + $("#school_id").val() + " ";
					sql = sql + "AND auctions.auction_ran=1 ";
					sql = sql + "ORDER BY prizes_auction.prize_number, auction_winners.prize_id, school_name, classes.class_grade, classes.class_sub, class_id, users.last, users.first, auction_winners.auction_id";
					
					var url = "http://mashpia.com/export_winners.php?school_id=" +  $("#school_id").val() + "&auction_id=" + $("#auction_id").val();
										
					$.ajax({
						url: url,
						success: function(data) 
						{
							window.location = "http://mashpia.com/exports/auction_winners.csv";
						}
					});			
					
				});
				
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
			
				$(".school_id select").sSelect().change(function () {

					var url = "http://mashpia.com/auction_ajax_numbers.php?auction_id=" + $("#auction_id").val() + "&school_id=" + $(this).val();
					
					$.ajax({
						url: url,
						success: function(data) {
							$('#auction_numbers').html(data);
						}
					});			
					
				})
			
				$(".auction_id select").sSelect().change(function () {
					
					var url = "http://mashpia.com/auction_ajax_numbers.php?auction_id=" + $(this).val() + "&school_id=" + $("#school_id").val();
					
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
					
						<!-- ********** SCHOOL ID ********** -->
						<div class="school_id select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"></span>
							</a>
														
							<SELECT name="school_id" id="school_id" class="sSelect">
								<OPTION value="0">All Schools</OPTION>
								<? foreach ($schools as $school) : ?>
									<? if ($school_id == $school->school_id) : ?>
									<OPTION selected value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
									<? else : ?>
									<OPTION value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
									<? endif; ?>						
								<? endforeach; ?>
							</SELECT> 
						
							<a class="next button">
								<span class="icon"></span>
								<span class="label"></span>
							</a>						
						
						</div>
						<!-- ********** SCHOOL ID ********** -->
					
						<!-- ********** AUCTION ID ********** -->
						<div class="auction_id select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"></span>
							</a>
														
							<SELECT name="auction_id" id="auction_id" class="sSelect">
								<? foreach ($auctions as $auction_select) : ?>
									<? if ($auction_id == $auction_select->auction_id) : ?>
									<OPTION selected value="<?=$auction_select->auction_id;?>"><?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
									<? else : ?>
									<OPTION value="<?=$auction_select->auction_id;?>"><?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
									<? endif; ?>						
								<? endforeach; ?>
							</SELECT> 
						
							<a class="next button">
								<span class="icon"></span>
								<span class="label"></span>
							</a>						
						
						</div>
						<!-- ********** AUCTION ID ********** -->

					</div>
					
				</FORM>
			</P>
				
			<br />

			<DIV name="auction_numbers" id="auction_numbers">
			</DIV>
			
		</DIV>
		
	</BODY>
	
</HTML>
