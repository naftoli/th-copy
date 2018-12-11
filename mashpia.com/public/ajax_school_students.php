<?php
include('db.php');
include('objects/user.php');

$school_id = $_GET['school_id'];

$users = array();
$sql = "SELECT * FROM users WHERE school_id=" . $school_id . " ORDER BY first, last";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$user = new user($row);
	$user->get_rank();
	$user->get_class();
	array_push($users, $user);
}
?>

<TABLE class="pretty_grid">
	<TR>
		<TH>Name</TH>
		<TH>Rank</TH>
		<TH>Class</TH>
	</TR>
	
	<? foreach ($users as $user) : ?>
	<TR>
		<TD><a href="javascript:void(0);" class="user_tag" data="<?=$user->user_id;?>"><?=$user->first;?> <?=$user->first;?></a></TD>
		<TD style="color:<?=$user->rank_color;?>"><?=$user->rank_name;?></TD>
		<TD><?=$user->class_grade;?> <? if ($user->class_sub != "") echo $user->class_sub; ?></TD>
	</TR>
	<? endforeach; ?>
</TABLE>

