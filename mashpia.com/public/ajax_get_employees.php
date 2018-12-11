<?
require('db.php');
require('lang.php');

$camp_id = $_GET['camp_id'];

$employees_query = mq("SELECT * FROM admins AS a JOIN admin_auths AS aa ON (a.admin_id=aa.admin_id AND aa.auth='camp' AND aa.id=" . $camp_id . " AND role_id IS NULL)");
$num_rows = mysql_num_rows($employees_query);

$return_string = "<table class='pretty_grid'>";
if ($num_rows == 0) {
	$return_string = $return_string . "<th>" . T_('No Employees Found') . "</th>";
}
else {
	$return_string = $return_string . "<tr>";
	$return_string = $return_string . "<th>" . T_("First") . "</th>";
	$return_string = $return_string . "<th>" . T_("Last") . "</th>";
	$return_string = $return_string . "<th></th>";
	$return_string = $return_string . "<th></th>";	
	$return_string = $return_string . "<th></th>";
	$return_string = $return_string . "</tr>";
							
	while ($employee = mysql_fetch_assoc($employees_query)) {
		$return_string = $return_string . "<tr>";
		$return_string = $return_string . "<td>" . $employee["first"] . "</td>";
		$return_string = $return_string . "<td>" . $employee["last"] . "</td>";	
		$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"edit\"; document.getElementById(\"admin_id\").value=\"" . $employee['admin_id'] . "\"; document.employees_form.submit();'>" . T_("Edit")  . "</a></td>";
		$return_string = $return_string . "<td><a href='#' onclick='var del = confirm (\"" . T_('Are you sure that you want to delete this employee?') . "\"); if (del == true) { document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"admin_id\").value=\"" . $employee['admin_id'] . "\"; document.employees_form.submit(); } '>" . T_("Delete")  . "</a></td>";		
		$return_string = $return_string . "<td><a href='admin_employee_groups.php?camp_id=" . $camp_id . "&admin_id=" . $employee['admin_id'] . "'>" . T_("Groups")  . "</a></td>";
		$return_string = $return_string . "</tr>";
	}
}
$return_string = $return_string . "</table>";
	
echo $return_string;											
?>

