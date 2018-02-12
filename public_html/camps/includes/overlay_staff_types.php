<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$admin_id = $_GET['admin_id'];
?>		 
		 <script>
			var admin_id = <?=$admin_id ;?>;
			
             $(document).ready(function() {
			 
                $(".radio input:checked").parent().addClass("selected");
								
                $(".radio .radio-select").click(
                    function(event) {
						// ***** Remove the other checks ***** //
						for (cntr = 0; cntr < $("#ul_list").find('li').size(); cntr++) {
							var li = $("#ul_list").find('li').get(cntr);
							$(li).find("a").get(0).className = "radio-select";
						}
						// ***** Remove the other checks ***** //
						
						event.preventDefault();
						$(this).parents('li').addClass("selected").css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})}).siblings().removeClass('selected').find('.progress').show().delay(500).fadeOut(500);
						$(this).parents('li').find("input").attr("checked","checked");							
					
						var info = $(this).parents('li').attr("id").split("_");
						var staff_type_id = info[1];
						
						//alert("admin_id:" + admin_id + " staff_type_id:" + staff_type_id);
						
						action = "assign_staff_type";				
						params = [admin_id, staff_type_id];
						var url = "appInterface.php?action=" + action + "&params=" + params;
						//alert(url);
						$.getJSON(url, function(error_code) {
							if (error_code == 1)
								alert("Could not assign tasks. Please try again.");
						});
						
                    }
                );
								
				$('select.select').sSelect();				
				
            });	
        </script>
		
			<div class="slider">
			
                <div class="col_title">
					Staff Types
				</div>
				
				<div class="col_content">
				
					<div class="module"> 
		
						<div class="module_content lists">
			
							<ul class="radio" id="ul_list">
								<? $query = mysql_query("SELECT st.*, a.admin_id FROM staff_types AS st LEFT JOIN admins AS a ON a.admin_id=" . $admin_id . " AND a.staff_type_id=st.staff_types_id") ; ?>
								<? while ($row = mysql_fetch_assoc($query)) : ?>
								<li id="sti_<?=$row['staff_types_id'];?>">
									<a href="#" class="radio-select">
										<? if ($row['admin_id'] > 0)  : ?>
										<input checked type="radio" name="staff_type" value="<?=$row['staff_types_id'];?>" />
										<? else : ?>
										<input type="radio" name="staff_type" value="<?=$row['staff_types_id'];?>" />
										<? endif; ?>
										<div class="name"><?=$row['type_name'];?></div>
										<div class="icon"></div>
									</a>
								</li>											
								<? endwhile; ?>												
							</ul>
				
						</div>
     
					</div> 
					
				</div>	
        </div>
