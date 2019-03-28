<?php
$admin_auth = array( 'school' ); // only schools are allowed here
require( __DIR__ . '/header.php'); // connect to the database and preform the authentication

require_once( __DIR__ . '/api/tools/functions/create_task.php' );

// get POST data
$subject_id = $_POST['campaign'];
$short_name = $_POST['shortName'];
$task = $_POST['name'];
$lang_id = $_POST['lang'];
$label_id = $_POST['label'];
// set $mission_marking and $grid_marking to 1 or 0 depending on if they are set in the post header
$mission_marking = !!$_POST['mission_marking'];
$grid_marking = !!$_POST['grid_marking'];
$grades = $_POST['classes'];
$school_id = $_POST['school_id'];
$parsha_ids = $_POST['parsha_ids'];
$school_type_id = $_POST['school_type_id'];

echo '<pre>';
print_r( $parsha_ids );

try {
	create_task(
		$subject_id,    $short_name,    $task,
		$lang_id,       $label_id,      $mission_marking,
		$grid_marking,  $grades,        $parsha_ids,
		$school_id,     $school_type_id
	);

	$msg = urlencode("Congratulations! You have successfully created a new Task.");
	header("Location: newTask.php?msg=$msg");

} catch ( Exception $e ) {
	echo $e->getMessage();
	// $msg = urlencode( 'Server Error: ' . $e->getMessage() );
	// header("Location: newTask.php?msg=$msg");
}
?>
