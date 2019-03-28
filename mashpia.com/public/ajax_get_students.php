<?
include("db.php");

$school_id = $_GET['school_id'];

$class_id = 0;
if (isset($_GET['class_id']))
	$class_id = $_GET['class_id'];

$sql = "SELECT u.* ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "WHERE school_id=" . $_GET['school_id'];
if ($class_id > 0)
	$sql = $sql . " AND class_id=" . $class_id;
$sql = $sql . " ORDER BY first, last";	
$query = mysql_query($sql);

?>

	<li>
		<a data="0" href="JavaScript:void(0);" class="hiLite" onclick="anchor_tag_click(this);">All Soldiers</a>
	</li>
	
<? while ($row = mysql_fetch_assoc($query)) : ?>
	<li>
		<a data="<?=$row['user_id'];?>" id="select_anchor_tag" href="JavaScript:void(0);" onclick="anchor_tag_click(this);"><?=$row['first'];?> <?=$row['last'];?></a>
	</li>
<? endwhile?>
