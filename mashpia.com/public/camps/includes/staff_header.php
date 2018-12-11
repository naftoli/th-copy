<?
$sql = "SELECT COUNT(*) AS total_staff FROM admins WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$total_staff = $row['total_staff'];

$sql = "SELECT COUNT(*) AS assigned_staff FROM admins WHERE camp_id=" . $camp_id . " AND staff_type_id IS NOT NULL";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$assigned_staff = $row['assigned_staff'];

$staff_types = array();
$sql = "SELECT * FROM staff_types";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$staff_type = new staff_type($row);
	$staff_type->get_no_of_staff($camp_id);
	array_push($staff_types, $staff_type);
}
?>
                        <div class="module_content">
                        	<h1>Staff Stats</h1>
                            <ul class="stats">
                            	<li>Staff<span><?=$total_staff;?></span></li>
                            	<li>Staff - Assigned<span><?=$assigned_staff;?></span></li>
                            	<li>Staff - Non-Assigned<span><?=($total_staff - $assigned_staff);?></span></li>
                            </ul>
							
							<ul class="stats">
							<? for ($stno = 0; $stno < count($staff_types); $stno++) : ?>
								<? $staff_type = $staff_types[$stno]; ?>							
								<li><?=$staff_type->type_name;?><span><?=$staff_type->no_of_staff;?></span></li>
							<? endfor; ?>
							</ul>
							
                            <div class="clear"></div>
                        </div>
