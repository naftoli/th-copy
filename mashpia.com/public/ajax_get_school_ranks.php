<?php
include("db.php");

require_once('classes/rank.php');
$ranks = array();
$sql = "SELECT ranks.*, COUNT(*) num ";
$sql = $sql . "FROM ranks ";
$sql = $sql . "LEFT JOIN rank_marks USING (rank_ord) ";
$sql = $sql . "WHERE rank_ord <= (SELECT MAX(rank_ord) FROM rank_marks) ";
$sql = $sql . "GROUP BY rank_ord, rank_name, rank_color ";
$sql = $sql . "ORDER BY rank_ord";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{
	$rank = new rank($row);
	array_push($ranks, $rank);
}

require_once('classes/school.php');
require_once('classes/school_class.php');
$school_id = $_GET['school_id'];
$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new \classes\school($row);
$school->get_classes();
foreach ($school->classes as $class)
{
	$class->get_class_rank_totals();
}
?>

<CENTER>
	<input type="button" id="back_button" name="back_button" class="back_button" value="BACK">
</CENTER>

<H2><?=$school->school_name;?><H2>	

<TABLE class="pretty_grid">
	
	<THEAD>
		<TR>
			<TH></TH>
			
			<? foreach ($ranks as $rank): ?>
			<TH <?php if ($rank->rank_color != "") echo "style='color:" . $rank->rank_color . ";'"; ?>>
			<?=$rank->rank_name?>
			</TH>
			<? endforeach; ?>
		</TR>
	</THEAD>
	
	<TBODY>					
		<? foreach ($school->classes as $class) : ?>
		<TR>
			<TH>
				<a href="javascript:void(0);" onclick="get_class_ranks(<?=$class->class_id;?>);" ><?=$class->class_grade;?><? if ($class->class_sub != "") echo " - " . $class->class_sub; ?></a>
			</TH>
			
			<? foreach ($class->ranks as $rank): ?>
			<TD>
				<? if ($rank->total_students > 0) : ?>
				<?=$rank->total_students;?>
				<? endif; ?>
			</TD>
			<? endforeach; ?>
			
		</TR>
		<? endforeach; ?>
	</TBODY>	
	
</TABLE>

<br />
