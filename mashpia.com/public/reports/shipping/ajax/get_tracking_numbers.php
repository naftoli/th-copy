<?/***************** DEBUGGING **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

require_once(dirname(__FILE__)."/../classes/Shipment.php");
use shipping\Shipment;

/***************** POST PARAMS **********************/
$shipment_id = isset($_POST['shipment_id']) ? mysql_real_escape_string($_POST['shipment_id']) : false;

if(!$shipment_id){echo json_encode(["success" => false, "error" => "Invalid Paramaters"]); die();}

/***************** GET TRACKING NUMBERS **********************/
$shipment = Shipment::load($shipment_id);
$shipment->get_tracking_numbers();

/***************** RENDER SUB-PAGE **********************/
if(count($shipment->tracking_numbers) > 0) {?>
    <h2>
        Tracking Numbers:
    </h2>
    
    <div>
        <?foreach($shipment->tracking_numbers as $tracking_number){?>
            <p class="tracking_number">
                <a href="<?=$tracking_number['tracking_link']?>" target="_blank">
                    <?=$tracking_number['tracking_number']?> (<?=$tracking_number['provider']?>)
                </a>
                <? if($admin_user['auth'] == 'super') {?>
                    <i class="fa fa-pencil tracking_number_edit" data-tracking_number_id="<?=$tracking_number['tracking_number_id']?>"
                    data-tracking_number="<?=$tracking_number['provider'] == "Amazon" ? $tracking_number['tracking_link'] : $tracking_number['tracking_number'];?>"
                    data-tracking_provider="<?=$tracking_number['provider']?>"
                    aria-hidden="true"></i>
                <?}?>
            </p>
        <?}?>
    </div>
<? };

