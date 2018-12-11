<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/user.php");
$campers = array();
$sql = "SELECT * FROM users WHERE camp_id=" . $camp_id . " AND camp_registered IS NULL";
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
while ($row = mysql_fetch_assoc($query)) {
	$camper = new user($row);
	array_push($campers, $camper);	
}
?>
			 <script>
                 $(document).ready(function() {
                    $(".register .button").click(
                        function(event) {
							var list_item = $(this).parents('li');
							var info = $(list_item).attr("id").split("_");
							var user_id = info[1];							
							var function_name = "register_camper";				
							var parameters = [user_id];
							var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							
							$.getJSON(url, function(success) {
								if (success == false)  {
									alert("Could not register camper. Please try again");
								}
								else {
									$(list_item).addClass("selected");
									$(list_item).find('.action').append('<span class="progress">Progress</span>').find('.progress').show().delay(500).fadeOut(500,function(){
										$(list_item).slideUp('fast',function(){
											$(list_item).remove();
										});								
									});
								}
							});
							
							//$.get('./includes/registerCamper.php',{id:$(this).attr('title')}, function(data){ alert(data); });
							//event.preventDefault();
                        }
                    );
                });
            </script>


			
			<div class="slider">
			
				<div class="col_title">
					<span>Campers</span>
				</div>
				
				<div class="col_content">
				
					<? include ("campers_header.php"); ?>
				
					<div class="module lists" id="lists-grouptypes">
					
						<div class="module_content">
						
							<? if ($num_rows > 0) : ?>
								<h1>Register Campers</h1>
							<? else : ?>
								<h1>No Unregistered Campers Found</h1>
							<? endif; ?>
							
							<ul>
							
								<? for ($cmpr = 0; $cmpr < count($campers); $cmpr++) : ?>	
								<? $camper = $campers[$cmpr]; ?>
								
								<li id="u_<?=$camper->user_id;?>">
									<a class="link" href="content.php?output=camper_profile&user_id=<?=$camper->user_id;?>">
										<div class="image">
										<? if ($camper->user_photo_id > 0) : ?>
											<img src="includes/file_view.php?id=<?=$camper->user_photo_id;?>" height="32" />
										<? else :?>
											<img src="images/generic_user_small.png" height="32" />
										<? endif; ?>
										</div>
										
										<div class="name"><?=$camper->first . " " . $camper->last; ?></div>
										<div class="dropdowns"></div>
									</a>
									
									<span class="action">
                                    </span>
									
									<span class="action">
										<span class="register">
											<a href="#" title="<?=$camper->user_id;?>" class="button">
												<span class="icon"></span>Register
											</a>
										</span>
                                    </span>
								</li>								
								<? endfor; ?>
								
							</ul>
							
						</div> <!-- <div class="module_content"> -->
						
					</div> <!-- <div class="module lists" id="lists-grouptypes"> -->
					
				</div> <!-- <div class="col_content"> -->
				
			</div> <!-- <div class="slider"> -->
