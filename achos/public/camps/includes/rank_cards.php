<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$campers = array();	
$sql = "SELECT * FROM users WHERE camp_id=" . $camp_id . " AND camp_registered IS NOT NULL ORDER BY first, last";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$user_id = $row['user_id'];
	$first = $row['first'];
	$last = $row['last'];
	$user_photo_id = $row['user_photo_id'];
		
	$element = compact('user_id', 'first', 'last', 'user_photo_id');
	array_push($campers, $element);	
}

$ranks = array();
$sql = "SELECT rank_ord, rank_name FROM ranks WHERE rank_image_id IS NOT NULL";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$rank_ord = $row['rank_ord'];
	$rank_name = $row['rank_name'];
		
	$element = compact('rank_ord', 'rank_name');
	array_push($ranks, $element);
}
?>
	<script>
		var window_name = 0;
		
		$(function() {

			$('#camper_select').change(function () 
			{
				var url = 'content.php?output=print_rank_cards&user_id=' + $(this).val();
				$('#print_button').attr('href',url);
			});

			$('form a.submit').click(function(e){
				e.preventDefault();
				slideForward(this);
				//$(this).click();
			});
			$('#group_bunk_select').hide();
			$('form #group_bunk').click(function(e){
				$('#group_bunk_select').toggle();
			});
		});
		
		function new_window(url) {		
			window_name++;			
			var user_select = document.getElementById("user_id");
			var rank_select = document.getElementById("rank_ord");
			url = url + "?user_id=" + user_select.options[user_select.selectedIndex].value + "&rank_ord=" + rank_select.options[rank_select.selectedIndex].value;

			window.open(url, window_name, 'height=760,width=760,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,status=no');
		}
	</script>

	<div class="slider">
	
		<div class="col_title">
			<span>Print Rank Cards</span>
		</div>
		
		<div class="col_content">
					
			<form id="rank_cards_form">
					
				<div class="module lists forms" id="lists-group-staff">
					
					<div class="module_content">
					
						<ul>
							<li>
								<span class="icon">
								</span>
								
								<span class="title">
									Print cards for	
								</span>
									
								<span class="title">
									Campers	
								</span>
								<span class="title">
									Rank	
								</span>
								<span class="title">
								                     	
								</span>


								<span>
									<select class="select" name="user_id" id="user_id">
										<option value="0">All</option>
										<? foreach ($campers as $c) : ?>
										<option value="<?=$c['user_id'];?>"><?=$c['first'] . " " . $c['last'];?></option>
										<? endforeach; ?>
									</select>
								</span>	

								<span>
									<select class="select" name="rank_ord" id="rank_ord">
										<option value="0">All</option>
										<? foreach ($ranks as $rank) : ?>
											<option value="<?=$rank['rank_ord'];?>"><?=$rank['rank_name'];?></option>
										<? endforeach; ?>
									</select>
								</span>
								
							</li>
							<li>
								<input type="hidden" name="view" value="rankcards" />
								<a class="overlay2 button" onclick="new_window('http://www.mashpia.com/camps/includes/print_rank_cards.php');" href="#">PRINT</a>
						</li>
							
							
						</ul>

					</div> <!-- <div class="module_content"> -->
						
				</div> <!-- <div class="module lists forms" id="lists-group-staff"> -->
					
			</form>
					
		</div> <!-- <div class="col_content"> -->
				
	</div> <!-- <div class="slider"> -->
