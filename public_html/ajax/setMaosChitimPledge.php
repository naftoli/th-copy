<?
$user_id = $_POST['user_id'];
$scan_code = $_POST['number'];

require_once '../db.php';
require_once '../class.maosChittim.php';

$m = new MaosChittim(5774);
if ($m->setStudentPledge($user_id, $scan_code)) {
	echo 'Your pledge was added to your account.';
} else {
	echo 'There was an error adding your pledge.';
}
?>