<?
require('db.php');

$school_id = $_GET['school_id'];

$order_by = "";
if (isset($_GET['order_by']))
	$order_by = "ORDER BY " . $_GET['order_by'];
	
include("classes/user.php");
$users = array();

$sql = "SELECT * ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "LEFT JOIN classes AS c USING (class_id) ";
$sql = $sql . "WHERE u.school_id=" . $school_id . " ";

if ($order_by == "")		
	$sql = $sql . "ORDER BY u.first, u.last";
else
	$sql = $sql . $order_by;
	
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$user = new user($row);
	$user->set_class($row);
	array_push($users, $user);
}

$row_num = 0;
?>

<table style="font-size: 12px; width:100%;" class="list list_left">

	<thead>
		<tr>
			<th><a href="javascript:void(0);" id="order_by_first">First</a></th>
			<th><a href="javascript:void(0);" id="order_by_last">Last</a></th>
			<th><a href="javascript:void(0);" id="order_by_class">Platoon</a></th>
			<th></th>
		</tr>
	</thead>
									
	<tbody>
	
		<? foreach ($users as $user) : ?>
		<? $row_num++; $remainder = $row_num % 2; if ($remainder == 0) $class = "even"; else $class = "odd"; ?>
		
		<tr class="<?=$class;?>">
			<td>
				<?=$user->first;?>
			</td>
			
			<td>
				<?=$user->last;?>
			</td>
			
			<td>
				<?=$user->class_grade;?>
				<? if ($user->class_sub != "") : ?>
				- <?=$user->class_sub;?>
				<? endif; ?>
			</td>

			<td>
				<input id="mission_updater" type="button" value="UPDATE" style="height:25px;" data="<?=$user->user_id;?>">
			</td>
		</tr>
		<? endforeach; ?>
		
	</tbody>
	
</table>
