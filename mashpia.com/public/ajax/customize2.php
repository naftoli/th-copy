<?
$school = isset( $_POST['school'] ) ? $_POST['school'] : 0;
$class = isset( $_POST['grade'] ) ? $_POST['grade'] : 0;
$user = $_POST['user'];
$start = $_POST['start'];
$end = $_POST['end'];
$exceptions = isset($_POST['exceptions']) ? $_POST['exceptions'] : array();

$campaigns = isset( $_POST['campaigns'] ) ? $_POST['campaigns'] : array();
$tasks = isset( $_POST['tasks'] ) ? $_POST['tasks'] : array();
$missions = isset( $_POST['missions'] ) ? $_POST['missions'] : array();

$campaignsAdded = isset( $_POST['campaignsAdded'] ) ? $_POST['campaignsAdded'] : array();
$tasksAdded = isset( $_POST['tasksAdded'] ) ? $_POST['tasksAdded'] : array();
$missionsAdded = isset( $_POST['missionsAdded'] ) ? $_POST['missionsAdded'] : array();
 
//tasks which show up in missions array should be removed from tasks array
function clean( &$missions, &$tasks ) {
    foreach( $missions as $mission ) {
        $missionInfo = explode('~', $mission);
        $task = $missionInfo[0];
        $key = array_search( $task, $tasks );
        if ( $key !== false ) {
            unset( $tasks[$key] );
        }
    }
    $tasks = array_values( $tasks );
}

clean( $missions, $tasks );
clean( $missionsAdded, $tasksAdded );

require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;

if ( !empty( $campaigns ) ) {
    if ( $user > 0 ) {
        $tc->unenroll( array( $user ), $campaigns );
    } else if ( $class > 0 ) {
        $users = $tc->getUsersInGrade( $class );
        $tc->unenroll( $users, $campaigns );
    } else if ( $school > 0 ) {
        //$tc->unenrollSchool( $school, $campaigns );
        $users = $tc->getAllUsers($school);
        $tc->unenroll($users, $campaigns);
    }	
}

if ( !empty( $campaignsAdded ) ) {
    if ( $user > 0 ) {
        $tc->enroll( array( $user ), $campaignsAdded );
        //unenroll all other users from campaign ???
        //$users = $tc->getOtherUsersInSchool($school, $user);
        //$tc->unenroll($users, $campaignsAdded);
    } else if ( $class > 0 ) {
        $users = $tc->getUsersInGrade( $class );
        $tc->enroll( $users, $campaignsAdded );
        //unenroll all other classes/users from campaign ???
        //$users = $tc->getOtherUsersInSchool($school, $class, 'class');
        //$tc->unenroll($users, $campaignsAdded);
    } else if ( $school > 0 ) {
        //tc->enrollSchool( $school, $campaignsAdded );
        $users = $tc->getAllUsers($school);
        $tc->enroll($users, $campaignsAdded); 
    }
}

print_r( $campaigns );
print_r( $tasks );
print_r( $missions );

print_r( $campaignsAdded );
print_r( $tasksAdded );
print_r( $missionsAdded );

$tc->setStart( $start );
$tc->setEnd( $end );
$tc->setType( $user, $class, $school );

if ( !empty( $tasksAdded ) ) {
    $tc->enrollIntoTasks( $tasksAdded );
}
 
if ( !empty( $missionsAdded ) ) {
    $tc->enrollIntoMissions( $missionsAdded );
}

if ( !empty( $tasks ) ) { 
    $tc->setTaskExceptions( $tasks );
}

if ( !empty( $missions ) ) {
    $tc->setMissionExceptions( $missions );
}

if (!empty($exceptions)) {
    $tc->enrollSchool($school, $exceptions);
    $tc->enroll($tc->getAllUsers($school), $exceptions);
}
?>