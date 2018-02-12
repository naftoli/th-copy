<?php
include('db.php');
include('calendar.php');

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

require_once('classes/school_class.php');
require_once('classes/user.php');
require_once('classes/rank_mark.php');
$sql = "SELECT * FROM classes WHERE class_id=" . $_GET['class_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$class = new school_class($row);
$class->get_students();
foreach ($class->students as $student) {
	$student->get_rank_marks($ranks);
}

require_once('classes/school.php');
$sql = "SELECT * FROM schools WHERE school_id=" . $class->school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new school($row);
?>

<CENTER>
	<input type="button" id="back_button" name="back_button" class="back_button" value="BACK">
</CENTER>

<H2><?=$school->school_name;?> <?=$class->class_grade;?> - <?=$class->class_sub;?><H2>	

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
		<? foreach ($class->students as $student) : ?>
		<TR>
			<TH><?=$student->first;?> <?=$student->last;?></TH>	
			<? foreach ($student->rank_marks as $rank_mark): ?>
				<? if ($rank_mark->date_promoted > 0) : ?>
				<TD><?=dateToHebrew($rank_mark->date_promoted);?></TD>	
				<? else : ?>
				<TD></TD>
				<? endif; ?>				
			<? endforeach; ?>			
		</TR>
		<? endforeach; ?>
	</TBODY>	
	
</TABLE>

<BR />

