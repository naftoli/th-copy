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

/***************** IMPORTS **********************/
require_once(dirname(__FILE__)."/../classes/Shipment.php");
use shipping\Shipment;

$shipment_id = mysql_real_escape_string($_GET['id']);

// if no id was provided...
if(!$shipment_id) {
    header('Location: https://mashpia.com/reports/shipping/shipments/'); die(); // redirect back to the index page....
}

$shipment = Shipment::load($shipment_id);
$shipment->get_details();
$shipment->get_school();
$shipment->get_tracking_numbers();

if($debug) print_r($shipment);
if($debug) echo "</pre>";?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Tehillim and Raffle Shipping Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="../css/shipping_form.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css"/>
        <style>
            #shipment_info p, #tracking_numbers p {display: inline-block;width: 49%;}
            #tracking_numbers p {text-align: center;}
            .tracking_number_edit, #tracking_numbers p a {transition: .25s}
            #tracking_numbers p a {font-size: .85em;}
            #tracking_numbers p a:hover {text-decoration: underline;font-size: .95em;}
            #tracking_numbers p .tracking_number_edit:hover{cursor: pointer; transform: rotate(45deg);}
        </style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Shipment Detail Page</h1>
        
        <div class="buttons" style="height: 32px; text-align: center;">
            <a class="button" href="../shipments/" style="float: left;">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Back
            </a>
            <? if($admin_user['auth'] == 'super') {?>
            <a class="button" style="display: inline-block" id="add_tracking_number">Add Tracking Number</a>
            <a class="button" id="edit_shipment" style="float: right;"
               data-shipment_id="<?=$shipment->shipment_id?>" data-name="<?=$shipment->name?>" data-description="<?=$shipment->description?>"
               data-date_shipped="<?=$shipment->date_shipped ? $shipment->date_shipped->format("Y-m-d") : ""?>">
                Edit <i class="fa fa-pencil" aria-hidden="true"></i>
            </a>
            <?}?>
        </div>

        <h2>Shipment Details:</h2>
        
        <div id="shipment_info">
            <p><strong>Shipment Name:</strong> <?=$shipment->name?></p>
            <p><strong>To School:</strong> <?=$shipment->school['school_name']?></p>
            <? if($shipment->date_shipped) {?>
                <p><strong>Shipped At:</strong> <?=$shipment->date_shipped->format('m/d/y');?></p>
            <?}?>
            <p><strong>Item Count:</strong> <?=$shipment->get_detail_count();?></p>
            <p><strong>Status:</strong> <?=$shipment->get_status();?></p>
            <p><strong>Number of packages:</strong> <?=count($shipment->tracking_numbers);?></p>
            <p><strong>Created At:</strong> <?=$shipment->date_created->format('m/d/y g:i a');?></p>
        </div>
        
        <? if ($shipment->description) { ?>
        <h2>Shipment Description:</h2>
        <p>
            <?=$shipment->description?> 
        </p>
        <? } ?>
        
        
        <div id="tracking_numbers"></div>
        
        <div class="shipment_details">
            <h2>Items in Shipment</h2>
            
            <table>
                <thead>
                    <tr><th>Recipient</th><th>Shipped Item</th></tr>
                </thead>
                <tbody>
                <? foreach($shipment->details as $detail) {?>
                    <tr>
                        <td><?=$detail['name']?></td>
                        <td><?=$detail['item']?></td>
                        <!--<td>
                            <a class="button" style="display: inline-block;" id="missing_shipment">Report Missing</a>
                        </td>-->
                    </tr>
                <? } ?>
                </tbody>
            </table>
            
        </div>
        <script>
            var shipment_id = <?=$shipment->shipment_id?>;
            $(document).ready(function(){
                $("#edit_shipment").click(function(event){
                    var data = event.target.classList.contains("fa") ? event.target.parentElement.dataset : event.target.dataset; // get the dataset even if they click the edit icon rather then the button
                    data = Object.assign({}, data); // cast the dataset to a plain object (not a DomStringMap);
                    shipment_modal.show(data);
                });
            });
        </script>
        <? include(dirname(__FILE__)."/../parts/shipment_modal.php"); ?>
        <? include(dirname(__FILE__)."/../parts/tracking_number_modal.php"); ?>
    </body>
</html>
