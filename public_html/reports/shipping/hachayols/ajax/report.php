<?php
/***************** GET THE DETAILS FROM THE REQUEST **********************/
include(dirname(__FILE__)."/../../parts/shipping_header.php");
include(dirname(__FILE__)."/../rendering/report_rendering.php");
/***************** RENDER THE PAGE **********************/
foreach($schools as $school_id => $school_name) {
    $school_hachayols = $hachayols[$school_id];
    $totals = [];
    
/***************** LIST THE SCHOOL IN A DIV **********************/?>
<div id="school_<?=$school_id?>" class="school" data-school_id="<?=$school_id?>">
    <h2><?$hachayol_name = get_school_hachayol_name($school_id); echo $hachayol_name ? $hachayol_name : $school_name?></h2>
    
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
    <?/***************** ACTUAL REPORT **********************/?>
    <table id="report_table">
        <thead>
            <tr>
                <th>Item</th><th>S/T</th>
                <?if(get_extra_hachayols($school_id) != 0) { ?><th># Extra</th><? } ?>
                <th>Total</th><th>Status</th><th>Report Missing</th><th># Shipped</th>
                <th>Shipment</th>
            </tr>
        </thead>
        <tbody>
            <? // combine all the shipments....
            /*********************** RENDER THE SHIPMENTS.... *********************/
            foreach($school_hachayols as $hachayol_shipment) {
                if($shipping_status == "shipped" && $hachayol_shipment['shipped'] == 0) continue;
                if($shipping_status == "not-shipped" && $hachayol_shipment['shipped'] != 0) continue;
                render_row($hachayol_shipment, get_extra_hachayols($school_id, $hachayol_shipment['hachayol_qty']));
            }
            ?>
        </tbody>
    </table>
</div>
<?} // end foreach school?>
