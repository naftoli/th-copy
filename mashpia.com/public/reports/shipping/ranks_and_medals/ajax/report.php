<?php
include(dirname(__FILE__)."/../../parts/shipping_header.php");
include(dirname(__FILE__)."/../../parts/report_rendering_functions.php");

// $report is only defined for the medals report in shipping_header.php and is an instance of the Report class. use responsiblily ?>
<div class="report_dates">
    Report Dates: From <?=$report->getHeReportDates()['start_he']?> To <?=$report->getHeReportDates()['end_he']?>
</div>
<?
/***************** RENDER THE PAGE **********************/
foreach($schools as $school_id => $school_name) {
    $school_medals = $medals[$school_id]; $school_ranks = $ranks[$school_id];
    $totals = [];?>
    
<div id="school_<?=$school_id?>" class="school" data-school_id="<?=$school_id?>">
    <h2><?=$school_name?></h2>

    <? if ($admin_user['auth'] == 'super') { ?>
    <div class="toggles">
        <div class="toggle-3rd">
            Toggle Shipped
            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" title="This toggle will check/uncheck all the shipped checkboxes on the page.
            Please double check your filters and use responsibly"></i>
            <label class="slider-container">
                <input type="checkbox" class="shipped_toggle_bulk"/>
                <span class="slider-span"></span>
            </label>
        </div>
        <div class="toggle-3rd">
            Shipment (All):
            <div class="bulk-shipment-select"></div>
        </div>
    </div>
    <? } // end if admin us superperuser ?>
    
    <table id="report_table">
        <thead>
            <tr>
                <th>Name</th><th>Grade</th><th>Item</th><th>Status</th><th>Missing?</th>
                <? if ($admin_user['auth'] == 'super') { ?><th>Shipped</th><? } ?>
                <th>Shipment</th>
            </tr>
        </thead>
        <tbody>
            <? //********************** RENDER THE ROWS FOR EACH STUDENT ***********************//
            if ($shipping_status == "all" && $sort == "status") {
                $users_shipments = []; // the users_shipments_structure
                foreach($schoolsUsers[$school_id] as $user) { // get the shipments for each user....
                    $tmp = isset($school_medals[$user['user_id']]) ? $school_medals[$user['user_id']] : []; // add the first one to the tmp shipments array
                    if(isset($school_ranks[$user['user_id']])) $tmp = array_merge($tmp, $school_ranks[$user['user_id']]); // add the gifts to the tmp shipments array
                    $users_shipments[] = [$user, $tmp]; // put them in the correct order for the render_by_status function....
                }
                render_by_status($users_shipments, $order); // render them by their status....
            } else {
                foreach($schoolsUsers[$school_id] as $user) {
                    // get the user shipments....
                    $user_shipments = [];
                    if(isset($school_medals[$user['user_id']])) $user_shipments = array_merge($user_shipments, $school_medals[$user['user_id']]);
                    if(isset($school_ranks[$user['user_id']])) $user_shipments = array_merge($user_shipments, $school_ranks[$user['user_id']]);
                    // sort and order (ASC/DESC)
                    usort($user_shipments, "sortByItem");
                    if ($order == "DESC"){$user_shipments = array_reverse($user_shipments);}
                    
                    $user_shipments = filter_shipping_status($user_shipments, $shipping_status); // filter by the shipping status....
                    render_shipments($user, $user_shipments); // and render the shipments....
                } // end foreach student
            } // end the if for rendering by status....
            ?>
        </tbody>
    </table>
    <? ksort($totals) ?>
    <h2><?=$school_name?> Totals</h2>
    <table id="totals">
        <thead>
            <th>Shipment</th><th>Total</th>
        </thead>
        <tbody>
            <? foreach($totals as $shipment => $total){?>
            <tr>
                <td><?=$shipment?></td>
                <td><?=$total?></td>
            </tr>
            <?}?>
            <th>Grand Total</th>
            <th><?=array_sum($totals)?></th>
        </tbody>
    </table>
</div>
<?} // end foreach school?>