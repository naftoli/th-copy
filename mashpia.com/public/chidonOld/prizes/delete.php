<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

use Illuminate\Database\Capsule\Manager as Capsule;

$id = isset($_POST['id']) ? $_POST['id'] : false;
$prize_picture = Capsule::table('chidon_prizes')->select("prize_picture")->where('prize_id', $id)->first()->prize_picture;
if ($prize_picture) unlink($_SERVER["DOCUMENT_ROOT"].$prize_picture);
$deleted = Capsule::table('chidon_prizes')->where('prize_id', $id)->delete();

http_response_code(302);
header('Location: ./index.php');
