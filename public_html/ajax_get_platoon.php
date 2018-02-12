<?
include("db.php");
include("classes/school_class.php");

$class_id = $_GET['class_id'];

$classes = array();
$sql = "SELECT * FROM classes WHERE class_id=" . $class_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$class = new school_class($row);
?>

<P CLASS="rows">

	<input type="hidden" name="class_id" value="<?=$class->class_id;?>">
	
	<LABEL>
		Grade
		<BR>
		<INPUT type="text" name="class_grade" maxlength=255 value="<?=$class->class_grade;?>">
	</LABEL>
	
	<BR>
										
	<LABEL>
		Sub
		<BR>
		<INPUT type="text" name="class_sub" maxlength=255 value="<?=$class->class_sub;?>">
	</LABEL>
		
	<BR>
	
	<LABEL>
		Teacher
		<BR>
		<INPUT type="text" name="class_teacher" maxlength=255 value="<?=$class->class_teacher;?>">
	</LABEL>
		
	<BR>
	
	<LABEL>
		Default Year
		<BR>
		<SELECT name="default_level" id="default_level">
			<? for ($level = 3; $level < 15; $level++) : ?>
			<OPTION value="<?=$level;?>"><?=$level;?></OPTION>
			<? endfor; ?>
		</SELECT>
	</LABEL>
	
	<BR>
	
	<INPUT type="button" id="save_button" value="Save">
</P>