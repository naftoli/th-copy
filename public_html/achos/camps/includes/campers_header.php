<?php
$sql = "SELECT 	count(*) AS total_campers, SUM(IF(camp_registered IS NOT NULL,1,0)) AS total_registered, SUM(IF(camp_registered IS NULL,1,0)) AS total_non_registered FROM users WHERE camp_id=" . $camp_id . " AND user_start_date IS NOT NULL";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$total_campers = $row['total_campers'];
$total_registered = $row['total_registered'];
$total_non_registered = $row['total_non_registered'];

$sql = "SELECT COUNT(*) AS assigned_campers FROM users AS u ";
$sql = $sql . "JOIN member_groups AS mg USING (user_id) ";
$sql = $sql . "WHERE u.camp_id=" . $camp_id . " AND u.user_start_date IS NOT NULL ";
$sql = $sql . "GROUP BY user_id";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$assigned_campers = $row['assigned_campers'];

include ("classes/group_type.php");
$group_types = array();
$sql = "SELECT * FROM group_types WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$group_type = new group_type($row);
	$group_type->get_number_of_campers($camp_id);
	array_push($group_types, $group_type);
}
?>

                    <div class="module" id="module-info">
					
                        <div class="module_content">
						
                        	<h1>Camper Stats</h1>
							
                            <ul class="stats">
                            	<li>Campers<span><?=$total_campers;?></span></li>
                            	<li>Campers - Registered<span><?=$total_registered;?></span></li>
                            	<li>Campers - Non-Registered<span><?=$total_non_registered;?></span></li>
                            </ul>
							
                            <ul class="stats">
                            	<li>Campers - Assigned<span><?=$assigned_campers;?></span></li>
								<li>Campers - UnAssigned<span><?=($total_campers - $assigned_campers);?></span></li>
                            </ul>
							
							<ul class="stats">
							<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>							
							<li><?=$group_types[$gtno]->group_type_name;?><span><?=$group_types[$gtno]->no_of_campers;?></span></li>
							<? endfor; ?>
							</ul>
							
                            <div class="clear"></div>
                        </div>
						
                    </div>
					
