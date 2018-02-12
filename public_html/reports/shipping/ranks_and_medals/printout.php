<?php
include(dirname(__FILE__)."/../parts/shipping_header.php");
include(dirname(__FILE__)."/../parts/printout_rendering_functions.php");

// increment the shipping totals
function update_shipment_total($shipment_array, &$totals, $schoolsUsers, $shipping_status){ // totals is PASS BY VALUE
    if(!isset($shipment_array)) return false;
    foreach($shipment_array as $user_id => $shipments){ // go through each user
        if(!isset($schoolsUsers[$user_id])) continue; // skip a user if he is not in the list
        $shipments = filter_shipping_status($shipments, $shipping_status); // filter his shipments....
        foreach($shipments as $shipment) { // and each shipment
            isset($totals[$shipment['shipment']]) ? $totals[$shipment['shipment']] += 1 : $totals[$shipment['shipment']] = 1; // incrament the totals
        }
    }
}

// calculate the totals for a single school....
function claculate_shipping_totals($school_id, $shipping_status){
    global $medals; global $ranks;
    // go through all the medals....
    foreach($medals[$school_id] as $user_id => $shipments) {
        foreach(filter_shipping_status($shipments, $shipping_status) as $shipment) { // filter the shipments based on the shipping status...
            isset($totals[$shipment['item']]) ? $totals[$shipment['item']] += 1 : $totals[$shipment['item']] = 1; // update the totals...
        }
    }
    // go through all the rank card/book shipments....
    foreach($ranks[$school_id] as $user_id => $shipments) {
        foreach(filter_shipping_status($shipments, $shipping_status) as $shipment) { // filter the shipments based on the shipping status...
            isset($totals[$shipment['item']]) ? $totals[$shipment['item']] += 1 : $totals[$shipment['item']] = 1; // update the totals...
        }
    }
    return $totals;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Student Shipping Report Printout</title>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="../css/printout.css" rel="stylesheet" type="text/css"/>
    </head>
    <body onload='setTimeout(function(){window.print();}, 250);'>
<?
/***************** RENDER THE PAGE **********************/
foreach($schools as $school_id => $school_name) {
    // get the  medals and ranks for this school
    $school_medals = $medals[$school_id]; $school_ranks = $ranks[$school_id];
    // get the school specific info from the database
    $totals = claculate_shipping_totals($school_id, $shipping_status);
    $school = get_school_shipping_info($school_id); $admin = get_school_admin($school_id);
    // render the school's shipping header?>
    <h1><?=$school_name?></h1>
    <h2>
        <?=$school['school_address1']?><br/>
        <?=$school['school_city'].", ".$school['school_state']." ".$school['school_postal'].", ".$school['school_country']?>
    </h2>
    <h3 class="quarter"><strong>Method:</strong> <?=$school['shipping_method'] == "deliver" ? "Delivery" : "Pickup"?></h3>
    <h3 class="quarter"><strong>Type:</strong> <?=$school_gender_types[$school_id]?></h3>
    <h3 class="quarter"><strong>Principal:</strong> <?=$school['principal']?></h3>
    <h3 class="quarter"><strong>Admin:</strong> <?=$admin['first']." ".$admin['last']?></h3>
    <?if ($school['shipping_requests']) { ?><h3><strong>Requests:</strong> <?=$school['shipping_requests']?></h3><? }?>
    
    <h2 class="details">Totals</h2>
    
    <? // sort the totals and put them in the right order...
    ksort($totals); if ($order == "DESC") $totals = array_reverse($totals);// sort the totals\
    /******************** RENDER THE TOTALS ***********************/
    printout_totals($totals);?>
    <h2 class="details">Shipments - Students</h2>
    
    <? foreach($schoolsUsers[$school_id] as $user) {
        $user_shipments = []; // basic array for the users shipments
        // merge all the shipments together
        if(isset($school_medals[$user['user_id']])) $user_shipments = array_merge($user_shipments, $school_medals[$user['user_id']]);
        if(isset($school_ranks[$user['user_id']])) $user_shipments = array_merge($user_shipments, $school_ranks[$user['user_id']]);
        // sort them based on ascending or decending order
        usort($user_shipments, "sortByItem");
        if ($order == "DESC"){$user_shipments = array_reverse($user_shipments);}
        
        // filter according to the shipping status
        $user_shipments = filter_shipping_status($user_shipments, $shipping_status); // update the user shipments
        
        // do not show the user if there are not shipments
        if (count($user_shipments) == 0) continue; // if there are no shipments for this user skip him
        
        printout_shipment_row($user_shipments, $user); // render the shipment row for the student
    } // end foreach student
    echo '<div class="page-break"></div>';
} // end foreach school



