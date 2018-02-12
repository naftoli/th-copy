<? // enable debuging
$debug = false; // default debugging is false
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
// header('Content-type: application/json'); // tell the client that the response is a JSON response....

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** IMPORTS **********************/
require_once(dirname(__FILE__)."/../../classes/Shipment.php");
use shipping\Shipment;

/***************** PARAMATERS **********************/
// get the school_id
$school_id = isset($_POST['school_id']) ? mysql_real_escape_string($_POST['school_id']) : false;
// get the status options
$status = []; // get the status options
$status['planned']      = isset($_POST['status']['planned'])    ? $_POST['status']['planned']       == "true" : false;
$status['archived']     = isset($_POST['status']['archived'])   ? $_POST['status']['archived']      == "true" : false;
$status['delivered']    = isset($_POST['status']['delivered'])  ? $_POST['status']['delivered']     == "true" : false;
$status['in_transit']   = isset($_POST['status']['in_transit']) ? $_POST['status']['in_transit']    == "true" : false;
// get the dates....
$start_date = isset($_POST['start_date']) && $_POST['start_date'] ? new DateTime($_POST['start_date']) : false;
$end_date = isset($_POST['end_date']) && $_POST['end_date'] ? new DateTime($_POST['end_date']." 23:59:59") : false; // add 23:59:59 in order to include the end date....

/***************** GENERATE SQL FILTER **********************/
$filter = [];
if($school_id) $filter[] = "school_id=$school_id"; // limit to a single school_id.
// make sure that the package shipped after the start date...
if($start_date) $filter[] = "(date_shipped > '".$start_date->format('Y-m-d H:i:s').($status['planned'] ? "' OR date_shipped IS NULL)" : "')"); // if we are showing planned shipments then blank date_shipped should be included
// and before the end date
if($end_date) $filter[] = "(date_shipped < '".$end_date->format('Y-m-d H:i:s').($status['planned'] ? "' OR date_shipped IS NULL)" : "')"); // if we are showing planned shipments then blank date_shipped should be included
// filter based on the shipping status...
if(!$status['planned']) $filter[] = "date_shipped IS NOT NULL";
if(!$status['delivered']) $filter[] = "delivered = 0";
if(!$status['archived']) $filter[] = "archived = 0";
if(!$status['in_transit']) $filter[] = "(date_shipped IS NULL OR delivered != 0)";
// combine all the filters into one SQL line
$filter = "WHERE ".implode(" AND ", $filter);
// and use it to load the shipments

if ($debug) echo $filter;
if ($debug) echo "<pre>";
if($debug) print_r($_POST);
if ($debug) echo "</pre>";

/***************** LOAD SHIPMENTS **********************/
$shipments = Shipment::loadAll($filter);

/***************** RENDER PAGE **********************/?>

<h1>Shipments</h1>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <?if(!$school_id && $admin_user['auth'] == 'super') {?>
                <th>School</th>
            <? } ?>
            <th>Shipped At</th><th style="padding: 4px 0px;"># Of Items</th><th>Status</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?foreach($shipments as $shipment){?>
            <tr>
                <td><a href="detail.php?id=<?=$shipment->shipment_id?>"><?=$shipment->name?></a></td>
                <?if(!$school_id && $admin_user['auth'] == 'super') {?>
                    <td><?=$shipment->get_school()['school_name']?></td>
                <? } ?>
                
                <td><?=$shipment->date_shipped ? $shipment->date_shipped->format("m/d/y") : "TBD" ?></td>
                <td><a href="detail.php?id=<?=$shipment->shipment_id?>"><?=$shipment->get_detail_count()?></a></td>
                <td><?=$shipment->get_status()?></td>
                <td>
                <? if ($admin_user['auth'] == 'super' && !$shipment->date_shipped) {?>
                    <a class="button shipped" data-shipment_id="<?=$shipment->shipment_id?>">
                        <i class="fa fa-truck" aria-hidden="true"></i> In Transit
                    </a>
                <?} if ($shipment->date_shipped && !$shipment->delivered) {?>
                    <a class="button delivered" data-shipment_id="<?=$shipment->shipment_id?>">
                        <i class="fa fa-archive" aria-hidden="true"></i> Delivered
                    </a>
                <?} if ($shipment->delivered && !$shipment->archived && $admin_user['auth'] == 'super') {?>
                    <a class="button archived" data-shipment_id="<?=$shipment->shipment_id?>">
                        <i class="fa fa fa-file-archive-o" aria-hidden="true"></i> Archive
                    </a>
                <?} ?>
                </td>
            </tr>
        <?} // end foreach shipment ?>
    </tbody>
</table>

