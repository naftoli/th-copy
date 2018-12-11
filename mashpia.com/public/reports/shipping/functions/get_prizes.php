<?php

/******************** GET_PRIZES() FUNCTION ********************/
/*
 * This function wraps the get_winners_dates function and normalizes the data for the unified shipping report
 * Normalizaition pattern
 *   shipped => boolean
 *   item => prize
 *   ajax => prize:user_id:raffle_id:prize_id
 *   shipment => the name of the shipment that it is a part of
 *   shipment_id => the id of the shipment (for the dropdown)
 * Notes:
 *  nested by school id and user id for quick access
 */

require_once($_SERVER["DOCUMENT_ROOT"].'/raffles/shared/shipping/functions/getWinners.php');
require_once(dirname(__FILE__)."/../classes/Shipment.php");

function get_prizes($start_date, $end_date, $shipping_status, $school_id, $type){
    $winners = getWinners::get_winners_dates($start_date, $end_date, false, $shipping_status, $school_id, false, $type);
    $result = [];
    foreach($winners[0] as $school_id => $winner_list){
        $school_raffle_shipments = shipping\Shipment::getPrizeShipmentDetails($school_id);
        foreach($winner_list as $winner){
            $ajax = "prize:".$winner['user_id'].":".$winner['raffle_id'].":".$winner['prize_id'];
            // if the winner shipped....
            if($winner['shipped'] &&
               isset($school_raffle_shipments[$winner['user_id']]) &&
               isset($school_raffle_shipments[$winner['user_id']][$winner['raffle_id']]))
            {
                $shipment = $school_raffle_shipments[$winner['user_id']][$winner['raffle_id']]['name'];
                $shipment_id = $school_raffle_shipments[$winner['user_id']][$winner['raffle_id']]['shipment_id'];
            } else {
                $shipment = "N/A"; $shipment_id = false;
            }
            // santize the results
            $result[$winner['school_id']][$winner['user_id']][] = [
                'shipped' => $winner['shipped'],
                'item' => $winner['prize']." (".$winner['raffle'].")",
                'ajax' => $ajax,
                'shipment' => $shipment,
                'shipment_id' => $shipment_id
            ];
        }
    }
    return $result;
}