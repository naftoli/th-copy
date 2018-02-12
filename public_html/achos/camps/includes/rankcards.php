<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();
$action = "get_camp_members";
$params = $camp_id;
$campers = getJson($action, $params);
?>
	<script>
		$(function() {

			$('#camper_select').change(function () 
			{
				var url = 'content.php?output=print&camper=' + $(this).val();
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
								<span class="icon"></span>
								<span class="title">Print cards for</span>
									
         				                           <span>
										<select class="select" name="camper" id="camper_select">
										<option>All</option>
										<?php foreach ($campers as $c) { ?>
										<option value="<?php echo $c['user_id']; ?>"><?php echo $c['first'] . " " . $c['last']; ?></option>
										<?php } ?>
										</select>
<!--
										<select class="select" name="bunk" id="group_bunk_select">
                                        	<option value="1">1</option>
                                        	<option value="2">2</option>
                                        	<option value="3">3</option>
										</select>-->
                                    </span>									
							</li>
							<!--
							<li>
								<span class="icon"></span>
								<div class="title"><input type="radio" name="display" value="unprinted" checked="checked" />Unprinted Cards Only</div>
								<div class="title"><input type="radio" name="display" value="all" />All Cards</div>
							</li>-->
							<li>
								<input type="hidden" name="view" value="rankcards" />
								<a class="overlay2 button" id="print_button" href="content.php?output=print" target="_blank">PRINT</a>
							</li>
							
						</ul>

					</div> <!-- <div class="module_content"> -->
						
				</div> <!-- <div class="module lists forms" id="lists-group-staff"> -->
					
			</form>
					
		</div> <!-- <div class="col_content"> -->
				
	</div> <!-- <div class="slider"> -->
