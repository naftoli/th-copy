<?php
include(dirname(__FILE__)."/../../parts/shipping_header.php");
include(dirname(__FILE__)."/../../parts/report_rendering_functions.php");

function get_yearly_prize_shipping_method($school_id) {
    $query = mysql_query("SELECT yearly_prize_shipping_method FROM schools WHERE school_id = " . $school_id);
    return mysql_fetch_assoc($query)['yearly_prize_shipping_method'];
}

/***************** RENDER THE PAGE **********************/
foreach($schools as $school_id => $school_name) {
    $yearly_prize_shipping_method = get_yearly_prize_shipping_method($school_id);
    $school_prizes = isset($prizes[$school_id]) ? $prizes[$school_id] : [];
    $school_students_gifts = isset($gifts['students'][$school_id]) ? $gifts['students'][$school_id] : [];
    $totals = [];?>
    
<div id="school_<?=$school_id?>" class="school" data-school_id="<?=$school_id?>">
    <h2><?=$school_name?></h2>
<!--    Master toggle -->
    <div class="toggles">
        <? if ($admin_user['auth'] == 'super') : ?>
        <div class="toggle-3rd">
            Toggle Shipped
            <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" title="This toggle will check/uncheck all the shipped checkboxes on the page.
            Please double check your filters and use responsibly"></i>
            <label class="slider-container">
                <input type="checkbox" class="shipped_toggle_bulk"/>
                <span class="slider-span"></span>
            </label>
        </div>
        <? endif; ?>
        
        <div class="<?= $admin_user['auth'] == 'super' ? "toggle-3rd" : "";?>">
            <strong>Tehillim Shipping Method:</strong><?= $admin_user['auth'] == 'super' ? "<br/>" : "";?>
            <input type="radio" class="yearly_gift_shipping" data-school_id="<?=$school_id?>" value="pickup" name="yearly_gift_shipping_<?=$school_id?>" 
                <?=$yearly_prize_shipping_method == "pickup" ? "checked" : ""?>/>Pickup
            <input type="radio" class="yearly_gift_shipping" data-school_id="<?=$school_id?>" value="deliver" name="yearly_gift_shipping_<?=$school_id?>" 
                <?=$yearly_prize_shipping_method == "deliver" ? "checked" : ""?>/>Delivery
        </div>
        
        <? if ($admin_user['auth'] == 'super') : ?>
       <div class="toggle-3rd">
            Shipment (All):
            <div class="bulk-shipment-select"></div>
        </div>
        <? endif; ?>
    </div>
<!--    Generated report.... -->
    <table id="report_table">
        <thead>
            <tr>
                <th>Name</th><th>Grade</th><th>Item</th><th>Status</th><th>Missing?</th>
                <? if ($admin_user['auth'] == 'super') { ?><th>Shipped</th><? } ?>
                <th>Shipment</th>
            </tr>
        </thead>
        <tbody>
            <tr><th colspan="7">Students</th></tr>
            <? // combine all the shipments....
            /*********************** RENDER THE STUDENTS.... *********************/
            if ($shipping_status == "all" && $sort == "status") {
                $users_shipments = []; // the users_shipments_structure
                foreach($schoolsUsers[$school_id] as $user) { // get the shipments for each user....
                    $tmp = isset($school_prizes[$user['user_id']]) ? $school_prizes[$user['user_id']] : []; // add the first one to the tmp shipments array
                    if(isset($school_students_gifts[$user['user_id']])) $tmp = array_merge($tmp, $school_students_gifts[$user['user_id']]); // add the gifts to the tmp shipments array
                    $users_shipments[] = [$user, $tmp]; // put them in the correct order for the render_by_status function....
                }
                render_by_status($users_shipments, $order); // render them by their status....
            } else {
                foreach($schoolsUsers[$school_id] as $user) {
                    $user_shipments = isset($school_prizes[$user['user_id']]) ? $school_prizes[$user['user_id']] : []; // get the prizes...
                    if(isset($school_students_gifts[$user['user_id']])) $user_shipments = array_merge($user_shipments, $school_students_gifts[$user['user_id']]); // merge it with the gifts...
                    $user_shipments = filter_shipping_status($user_shipments, $shipping_status); // filter by the shipping status....
                    render_shipments($user, $user_shipments); // and render the shipments....
                } // end foreach student
            } // end the if for rendering by status....
            
            // render the staff heading
            if ($shipments['gifts']) {?>
                <tr class="heading"><th>Staff</th><th colspan="6">Position</th></tr>
            <? }
            // flip the staff based on sorting....
            if ($order == "DESC"){$gifts['staff'][$school_id] = array_reverse($gifts['staff'][$school_id]);}
            
            if ($shipping_status == "all" && $sort == "status") { // if we are ordering by the status....
                $staff_shipments = []; // the array for the staff shipments
                foreach($gifts['staff'][$school_id] as $staff_shipment_list){$staff_shipments[] = [false, $staff_shipment_list];} // there is no user so set it to false...
                render_by_status($staff_shipments, $order, true); // render them by their status....
            } else { // if we are not jumping into insanity....
                foreach($gifts['staff'][$school_id] as $staff_shipments) {
                    $staff_shipments = filter_shipping_status($staff_shipments, $shipping_status);
                    // sort and flip...
                    usort($staff_shipments, "sortByShipment");
                    if ($order == "DESC"){$staff_shipments = array_reverse($staff_shipments);}
                    // render
                    render_shipments(false, $staff_shipments, true);
                } // end for each staff member....
            }
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
            <?} ?>
            <th>Grand Total</th>
            <th><?=array_sum($totals)?></th>
        </tbody>
    </table>
</div>
<?} // end foreach school?>
