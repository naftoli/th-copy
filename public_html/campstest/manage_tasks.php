<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');
$camp_mission_id = gri('camp_mission_id');

$group_types = "";
$divisions = "";
$groups = "";

$prev_group_type_id = "";
$prev_division_id = "";	
	
$group_types = "<select onchage='check_divisions(this);' id='group_type_id' name='group_type_id' class='select' style='display:none;'>";		
$group_types = $group_types . "<option value='0'>" . T_("All Group Types") . "</option>";
	
$divisions = "<select onchage='check_groups(this);' id='division_id' name='division_id' class='select' style='display:none;'>";		
$divisions = $divisions . "<option value='0' selected>" . T_("All Divisions") . "</option>";
	
$groups = "<select id='group_id' name='group_id' class='select' style='display:none;'>";		
$groups = $groups . "<option value='0'>" . T_("All Groups") . "</option>";
	
$sql = "";
$sql = $sql . "SELECT * "; 
$sql = $sql . "FROM group_types ";
$sql = $sql . "JOIN divisions USING (group_type_id) ";
//$sql = $sql . "JOIN groups USING (division_id) ";
$sql = $sql . "WHERE camp_id=" . $camp_id . " ";
$sql = $sql	. "ORDER BY group_type_id, division_id ";
		
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	
	if ($prev_group_type_id != $row['group_type_id']) {
		$group_types = $group_types . "<option value='" . $row['group_type_id'] . "'>" . $row['group_type_name'] . "</option>";
		$divisions = $divisions . "<optgroup id='" . $row['group_type_id'] . "' label='" . $row['group_type_name'] . "'>";
	}
		
	if ($prev_division_id != $row['division_id']) {		
		$divisions = $divisions . "<option value='" . $row['division_id'] . "'>" . $row['division_name'] . "</option>";
		//$groups = $groups . "<optgroup label'" . $row['group_type_name'] . "-" . $row['division_name'] . "'>";
		$groups = $groups . "<optgroup label='" . $row['group_type_name'] . "\n" . $row['division_name'] . "'>";
		
		$sql2 = "SELECT * FROM groups WHERE division_id=" . $row['division_id'];
		$query2 = mysql_query($sql2);
		while ($row2 = mysql_fetch_assoc($query2)) {
			$groups = $groups . "<option value='" . $row2['group_id'] . "'>" . $row2['group_name'] . "</option>";
		}
	}
		
	$prev_group_type_id = $row['group_type_id'];
	$prev_division_id = $row['division_id'];
}
	
$group_types = $group_types . "</select>";
$divisions = $divisions. "</select>";	
$groups = $groups. "</select>";	
?>

		<!--<link href="styles/new_styles.css" rel="stylesheet" type="text/css" />-->
		<script src="scripts/jquery.styleselect.js"></script>

		 <script>
			$(document).ready(function() {
               // $(".checklist input:checked").parent().addClass("selected");
				
                //$(".checklist .checkbox-select").click(
               //     function(event) {
               //         event.preventDefault();
               //         $(this).parents('.checklist').addClass("selected");
               //         $(this).parents('.checklist').find(":checkbox").attr("checked","checked");
               //         $(this).parents('li').css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
               //         $(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
               //     }
               // );
				
               // $(".checklist .checkbox-deselect").click(
               //     function(event) {
               //         event.preventDefault();
               //         $(this).parents('.checklist').removeClass("selected");
               //         $(this).parents('.checklist').find(":checkbox").removeAttr("checked");
               //         $(this).parents('li').css({ backgroundColor: '#e19494' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
               //         $(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
               //     }
               // );
				
				// ***** This creates the drop down ***** //
				$('select.select').sSelect();
				
            });
        </script>
		
		<script type="text/javascript">	
			function getHTTPObject() {
				var xmlhttp;

				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				}
				else if (window.ActiveXObject){ 
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
								
					if (!xmlhttp) {
						xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
					}
				}
							
				return xmlhttp; 
			}			
		</script>
		
		
		<div class="module"> 
			
            <h1>Tasks for Line Up</h1>
			
            <div class="module_content">
                				
				<div class="list">
				
                    <ul>
					
						<? $query = mq("SELECT * FROM camp_tasks WHERE camp_mission_id=" . $camp_mission_id); ?>
						<? while ($row = mysql_fetch_assoc($query)) : ?>						
						<li>
				
                            <span class="action">
                                <span class="checklist selected">
                                    <input type="checkbox" checked="checked" class="checkbox" id="Mission-72">
                                    <span class="activate"><a class="buttonHover checkbox-select" title="Activate" href="#"><span class="icon activate"></span>Activate</a></span>
                                    <span class="deactivate"><a class="buttonHover checkbox-deselect" title="Deactivate" href="#"><span class="icon deactivate"></span>Deactivate</a></span>
                                    <span class="progress">Progress</span>
                                </span>
                            </span>
							
                            <span class="icon bullet">
							</span>
							
                            <span class="label">
								<span class="label title"><?=T_("Task");?></span>
								<?=$row['task_name'];?>
							</span>
							
                            <span class="label points">
								<span class="label title">Points
								</span><?=$row['points'];?>
							</span>
				
						
							<div>
								<!-- ***** GROUP TYPE ***** -->
								<? //echo $group_types; ?>
								<!-- ***** GROUP TYPE ***** -->	
							</div>
							
							<div>
								<!-- ***** DIVISIONS ***** -->
								<? //echo $divisions; ?>
								<!-- ***** DIVISIONS ***** -->	
							</div>
							
							<div>
								<? echo $groups; ?>
							</div>
							
							<div class="clear"></div>							
						
						</li>						
						<? endwhile; ?>
						
						
						<li>
                            <span class="icon done"></span>
                            <span class="label"><a class="close" href="#"><?=T_("Done");?></a></span>
                            <div class="clear"></div>
                        </li>
					
					</ul>
					
                </div> <!-- <div class="list"> -->
				
            </div> <!-- <div class="module_content"> -->
     
		</div> <!-- <div class="module"> -->