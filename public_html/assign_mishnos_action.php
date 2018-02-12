<?php
ini_set('display_errors',1);
require_once 'db.php';
//echo "<pre>";
//print_r( $_POST );
//echo "</pre>";
//exit;

$school = mysql_real_escape_string($_POST['school']);
$grade 	= mysql_real_escape_string($_POST['grade']);
$seder 	= mysql_real_escape_string($_POST['seder']);
$student 	= mysql_real_escape_string($_POST['student']);
$mesechtos 	= $_POST['mesechtos'];

$mSedorim = array();
$sql = "select mesechto_id, seder_id from mesechtos where mesechto_id in (" . implode(',', $mesechtos) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$mSedorim[$row['mesechto_id']] = $row['seder_id'];
}

//first delete what's already been there
$sql = "delete from mishna_assigned 
		where school_id = $school";
if ($grade > 0) {
	$sql .= " and class_id = " . $grade;
}
if ($student > 0) {
	$sql .= " and user_id = " . $student;
}
@mysql_query($sql);

//if we are assigning to entire school we need to assign to all grades in school
$grades = array();
if ($grade > 0) {
	$grades = array( $grade );
} else {
	require_once 'class.schoolClasses.php';
	$s = new SchoolClasses( $school );
	$grades = $s->getClassIDs();
}

if ($student > 0) {
	foreach ($mesechtos as $mesechto) {
		$m = mysql_real_escape_string($mesechto);
		$sql = "insert into mishna_assigned   
				set seder_id = " . $mSedorim[$m] . ", 
				mesechto_id = $m, 
				school_id = $school, 
				class_id = $grade, 
				user_id = $student";
		@mysql_query( $sql );
	}
} else {
	foreach ($grades as $class_id) {
		foreach ($mesechtos as $mesechto) {
			$m = mysql_real_escape_string($mesechto);
			$sql = "insert into mishna_assigned   
					set seder_id = " . $mSedorim[$m] . ", 
					mesechto_id = $m, 
					school_id = $school, 
					class_id = $class_id";
			@mysql_query( $sql );
		}
	}
}

if (isset($_POST['parent']) && $_POST['parent'] == 1) {
	header("Location: parent_assign_mishnos.php");
	exit;
} else {
	if ($student > 0) $user = $student;
	else $user = 0;
	header("Location: assign_mishnos.php?school=$school&grade=$grade&user=$user&success=1");
	exit;
}
?>