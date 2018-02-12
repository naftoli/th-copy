<?
require_once '../db.php';

require_once '../class.parshos.php';
$parshos = Parshos::getParshos( 5775 );

$sql = "select school_id from schools where school_era is null";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	$schools[] = $row;
}

require_once '../class.schoolsUsers.php';
foreach ( $schools as $school ) {
	$s = new SchoolsUsers( $school['school_id'] );
	$users[$school['school_id']] = $s->getUsers( true, true );
}

//create array of userIDs
$userIDs = array();
foreach ( $users as $schoolID => $info ) {
	foreach ( $info as $user ) {
		$userIDs[$schoolID][] = $user['user_id'];
	}
}

require_once 'class.raffleTicket.php';
foreach ( $parshos as $parsha ) {
	if ( $parsha['start'] > unixtojd() ) break;
	foreach ( $userIDs as $users ) {
		$rt = new RaffleTicket( $users, $parsha['start'], $parsha['end'] );
		$rt->checkForTickets();
	}
}
?>