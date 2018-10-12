<?php
include(dirname(__FILE__)."/../parts/shipping_header.php");
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
    // get the hachayols for the students and the teachers
    $school_hachayols = $hachayols[$school_id];
    // get the school specific info from the database
    $school = get_school_shipping_info($school_id); $admin = get_school_admin($school_id);
    // render the school's shipping header ?>
    <h1><?=$school['hachayol_name'] ? $school['hachayol_name'] : $school['school_name'];?></h1>
    <h2>
        <?=$school['shipping_address1']?>, <?=$school['shipping_address2']?><br/>
        <?=$school['shipping_city'].", ".$school['shipping_state']." ".$school['shipping_postal'].", ".$school['shipping_country']?>
    </h2>
    <h3 class="quarter"><strong>Method:</strong> <?=$school['shipping_method'] == "deliver" ? "Delivery" : "Pickup"?></h3>
    <h3 class="quarter"><strong>Type:</strong> <?=$school_gender_types[$school_id]?></h3>
    <h3 class="quarter"><strong>Principal:</strong> <?=$school['principal']?></h3>
    <h3 class="quarter"><strong>Admin:</strong> <?=$admin['first']." ".$admin['last']?></h3>
    <?if ($school['shipping_requests']) { ?><h3><strong>Requests:</strong> <?=$school['shipping_requests']?></h3><? }?>
    <h2 class="details">Hachayol Shipments</h2>
    
    <?foreach($school_hachayols as $hachayol_shipment) {?>
    <div class='shipping_box'>
        <div class='shipping_inner_box'>
            <span class="checkbox-new"><i class="fa fa-square-o" aria-hidden="true"></i></span>
            <div class="shipping_details">
                <strong style="font-size: 1.4em;"><?=$hachayol_shipment['hachayol_qty'] + get_extra_hachayols($school_id, $hachayol_shipment['hachayol_qty'])?> <?=$hachayol_shipment['item']?></strong><br/>
                <i class="fa fa-square-o" aria-hidden="true"></i> <?=$hachayol_shipment['student_qty']?> For Students<br/>
                <i class="fa fa-square-o" aria-hidden="true"></i> <?=$hachayol_shipment['teacher_qty']?> For Teachers<br/>
                <? if (get_extra_hachayols($school_id) > 0) { ?>
                <i class="fa fa-square-o" aria-hidden="true"></i> <?=get_extra_hachayols($school_id, $hachayol_shipment['hachayol_qty'])?> Extra<br/>
                <? } ?>
            </div>
        </div>
    </div>
    <?} // end foreach student 
    /***************** SHOW THE SCHOOL TEACHERS **********************/ ?>
    <div class="page-break"></div>
<?} // end foreach school?>