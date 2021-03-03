<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

use Illuminate\Database\Capsule\Manager as Capsule;

$id = isset($_POST['id']) ? $_POST['id'] : false;
$sweater_picture = Capsule::table('chidon_sweaters')->select("sweater_picture")->where('sweater_id', $id)->first()->sweater_picture;
if ($sweater_picture) unlink($_SERVER["DOCUMENT_ROOT"].$sweater_picture);
$deleted = Capsule::table('chidon_sweaters')->where('sweater_id', $id)->delete();

http_response_code(302);
header('Location: ./index.php');
