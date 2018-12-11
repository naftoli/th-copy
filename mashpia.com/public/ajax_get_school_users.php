<?
require('db.php');

$school_id = $_GET['school_id'];

$class_id = 0;
if (isset($_GET['class_id']))
	$class_id = $_GET['class_id'];
	
$user_serial = 0;
if (isset($_GET['user_serial']))
	$user_serial = $_GET['user_serial'];
	
$first = "";
if (isset($_GET['first']))
	$first = $_GET['first'];

$last = "";
if (isset($_GET['last']))
	$last = $_GET['last'];
	
$user_registered = "";
if (isset($_GET['user_registered']))
	$user_registered = $_GET['user_registered'];
	
$order_by = "";
if (isset($_GET['order_by']))
	$order_by = $_GET['order_by'];

	
include("classes/user.php");
$users = array();

$sql = "SELECT * ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "LEFT JOIN classes AS c USING (class_id) ";
$sql = $sql . "WHERE u.school_id=" . $school_id . " ";

if ($class_id > 0)
	$sql = $sql . "AND u.class_id=" . $class_id . " ";
	
if ($user_serial > 0)
	$sql = $sql . "AND u.user_serial=" . $user_serial . " ";
	
if ($first != "")
	$sql = $sql . "AND u.first LIKE '%" . $first . "%' ";
	
if ($last != "")
	$sql = $sql . "AND u.last LIKE '%" . $last . "%' ";
	
if ($user_registered != "")
	$sql = $sql . "AND u.user_registered IS NOT NULL ";

if ($order_by != "")
	$sql = $sql . "ORDER BY " . $order_by;
else	
	$sql = $sql . "ORDER BY c.class_grade, c.class_sub, u.last, u.first, u.username";

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

			<th><a id="order_by_last" href="javascript:void(0);">Last</a></th>
			<th><a id="order_by_first" href="javascript:void(0);">First</a></th>
			<th><a id="order_by_platoon" href="javascript:void(0);">Platoon</a></th>
			<th>Birthdate</th>
			<th></th>
			
		</tr>
		
	</thead>
									
	<tbody>
	
		<? foreach ($users as $user) : ?>
		<? $row_num++; $remainder = $row_num % 2; if ($remainder == 0) $class = "even"; else $class = "odd"; ?>
		
		<tr class="<?=$class;?>">
											
			<td>
				<!--<a href="admin_user_edit.php?user_id=<?//=$user->user_id;?>&school_id=<?//=$school_id;?>&class_id=<?//=$user->class_id;?>"><?//=$user->last;?></a>-->
				<a id="user_edit" data="<?=$user->user_id;?>" href="JavaScript:void(0);"><?=$user->last;?></a>
			</td>
			
			<td>
				<a id="user_edit" data="<?=$user->user_id;?>" href="JavaScript:void(0);"><?=$user->first;?></a>
			</td>
			
			<td>
				<?=$user->class_grade;?>
				<? if ($user->class_sub != "") : ?>
				- <?=$user->class_sub;?>
				<? endif; ?>
			</td>
											
			<td>
				<?=$user->dob;?>
			</td>
						
			<td>							
				<!--
				<a href="JavaScript:void(0);" id="delete_user" data="<?=$user->user_id;?>">Delete Soldier</a>
				<br>
				-->
				<a href="JavaScript:void(0);" id="remove_user" data="<?=$user->user_id;?>">Remove Soldier from school</a>
			</td>
			
		</tr>
		<? endforeach; ?>
		
	</tbody>
	
</table>
