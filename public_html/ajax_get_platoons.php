<?
include("db.php");
include("classes/school_class.php");

$school_id = $_GET['school_id'];

$classes = array();
$sql = "SELECT * FROM classes WHERE school_id=" . $_GET['school_id'] . " ORDER BY class_era, class_grade, class_sub";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$class = new school_class($row);
	$class->get_number_of_soldiers();
	$class->get_points();
	array_push($classes, $class);
}
?>


<table style="font-size: 12px;" class="list">
	<thead>
		<tr>
			<th>Grade</th>
			<th>Sub</th>	
			<th>Teacher</th>
			<th>Default Year</th>
			<th># Soldiers</th>
			<th>Points</th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	
	<tbody>
		<? foreach ($classes as $class) : ?>
		<tr>
			<td>
				<?=$class->class_grade;?>
			</td>
			
			<td>
				<?=$class->class_sub;?>
			</td>
			
			<td>
				<?=$class->class_teacher;?>
			</td>
			
			<td>
				<?=$class->default_level;?>
			</td>
			
			<td>
				<?=$class->number_of_soldiers;?>
			</td>
			
			<td>
				<?=$class->points;?>
			</td>
			
			<td><a href="javascript:void(0);" id="edit_platoon" data="<?=$class->class_id;?>">Edit Platoon</a></td>
			
			<? if ($class->number_of_soldiers > 0) : ?>
			<td>
				Can't delete, has soldiers
			</td>
			<? else : ?>
			<td>
				<a href="javascript:void(0);" id="delete_platoon" data="<?=$class->class_id;?>">Delete Platoon</a>
			</td>
			<? endif; ?>
			
			<td><a href="admin_user.php?search_class_id=<?=$class->class_id;?>&school_id=<?=$school_id;?>">Manage Soldiers</a></td>
		</tr>
		<? endforeach?>
	</tbody>
</table>