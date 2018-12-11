<?
include("db.php");
$sql = "SELECT subject_id, subject_name FROM subjects WHERE school_id=" . $_GET['school_id'];
echo $sql . "<br >";
$query = mysql_query($sql);
?>

	<li>
		<a data="0" href="JavaScript:void(0);" class="hiLite">All Subjects</a>
	</li>
	
<? while ($row = mysql_fetch_assoc($query)) : ?>

	<li>
		<a data="<?=$row['subject_id'];?>" id="select_anchor_tag" href="JavaScript:void(0);" onclick="anchor_tag_click(this, 'subject_id');"><?=$row['subject_name'];?></a>
	</li>
	
<? endwhile;?>
