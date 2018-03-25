<?php
require_once(dirname(__FILE__)."/../classes/Shipment.php"); // load up the shipments class....
require_once ($_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php'); // require the global settings class
require_once(dirname(__FILE__)."/get_parshos.php"); // import the get_parshos function...

/******************** get_hachayols() FUNCTION ********************/
/*
 * This function wraps the get_winners_dates function and normalizes the data for the unified shipping report
 * Normalizaition pattern
 *   shipped => is in get_shipped_marks?
 *   shipment => Hachayol
 *   name => the name of the teacher
 *   ajax => hachayol:<type>:<id>
 * Notes:
 *  nested by school id and user id for quick access
 */

//require_once($_SERVER["DOCUMENT_ROOT"].'/class.hachayol.php');

function get_hachayols($school_id, $start_date, $end_date){
    //$shipments = shipping\Shipment::getHachayolShipmentDetails($school_id);
    
    // get the current year...
    $year = GlobalSettings::getCurrentYear();
    $year_start = '2017-07-01';/*date("Y-m-d" , jdtounix(GlobalSettings::getCurYearDates()['start']));*/
    // get the parshos of hachayol selected...
    $parshos = get_parshos($year, $start_date, $end_date);
    
    // the array containing all the results from the report generation...
    $result = [];
    
    foreach($parshos as $parsha){ // for each parsha that has an hachayol
        $parsha_end_date_greg = date('Y-m-d 23:59:59', jdtounix($parsha['end']));
        
        $hachayol_count_sql = "SELECT school_id, SUM(total) as total, SUM(teacher_total) as teachers FROM ( "
            ."SELECT school_id, COUNT(*) as total, 0 as teacher_total FROM users WHERE user_registered > '$year_start' AND user_registered < '$parsha_end_date_greg' ";
        if($school_id) $hachayol_count_sql .= "AND school_id = $school_id ";
        $hachayol_count_sql .= "GROUP BY school_id UNION "
            ."SELECT school_id, COUNT(*) as total, COUNT(*) as teacher_total FROM classes WHERE class_era = 0 ";
            if($school_id) $hachayol_count_sql .= "AND school_id = $school_id ";
        $hachayol_count_sql .= "GROUP BY school_id, class_teacher "
            .") AS SQ GROUP BY school_id";
        
        $hachayol_count_query = mysql_query($hachayol_count_sql); // excecute the query we generated....
        
        while($hachayol_count_row = mysql_fetch_assoc($hachayol_count_query)){
            $ajax = "hachayol:".$hachayol_count_row['school_id'].":".$parsha['id'];
            $shipping_info = get_hachayol_shipping($hachayol_count_row['school_id'], $parsha['id'])[0];
            $result[$hachayol_count_row['school_id']][] = [
                'ajax'          => $ajax,
                'item'          => 'Hachayol For ' . $parsha['name'],
                // TODO, handle the shipping part
                'shipped'       => $shipping_info['qty'], // TODO
                'shipment'      => $shipping_info['name'],
                'shipment_id'   => $shipping_info['shipment_id'],
                // totals for rendering
                'hachayol_qty'  => $hachayol_count_row['total'],
                'student_qty'   => $hachayol_count_row['total'] - $hachayol_count_row['teachers'],
                'teacher_qty'   => $hachayol_count_row['teachers']
            ];
        }
    }
    return $result;
}

function get_hachayol_shipping($school_id, $parsha_id = false){
    $sql = "SELECT * FROM hachayol_shipping hs LEFT JOIN shipments s USING (shipment_id) WHERE hs.school_id = $school_id ";
    if($parsha_id) $sql .= "AND hs.parsha_id = $parsha_id";
    
    $query = mysql_query($sql);
    $hachayol_shippings = [];
    if(mysql_num_rows($query) > 0) {
        while($row = mysql_fetch_assoc($query)){
            $hachayol_shippings[] = $row;
        }
    } else {
        $hachayol_shippings[] = ['qty' => 0, 'shipment_id' => false, 'name' => false];
    }
    return $hachayol_shippings;
}

function get_extra_hachayols($school_id, $current_amount=false){
    $extras = [
        # Beis Rivkah Crown Heights => dynamically compute totals to enforce desired total of 580
        54  => reduce_to_total($current_amount, 580),  // wants 580 (550 before 1/3/2018) in each shipment. no matter what
        3   => reduce_to_total($current_amount, 110), // 
        265 => reduce_to_total($current_amount, 65), // Lubavitch Girls London requested only 65 via email to shipping@tzivoshashem.org
        58  => 45,  # YTTL-Montreal
        84  => 75,  # Torah Day School of Houston
        9   => 20   # Lubavitcher Yeshiva, Crown Heights => requested by Ester Zachar via Email to bugs@tzivoshashem.org on 2/27/2018
    ];
    return isset($extras[$school_id]) ? $extras[$school_id] : 0; // return the extras or 0
}

/*
 * function reduce_to_total
 *
 * calculates number needed to transform one number to another using addtion only.
 *
 * $amount => the amount that we wish to reduce
 * $total => the amount we want it to become
 *
 * returns => number required to add to $amount to make it $total
 *
 */
function reduce_to_total($amount, $total) {
    // return the total if the amount is falsy
    if(!$amount) return $total;
    // if the amount is greater then the total return the - number needed to reduce it to the total
    if ( $amount > $total )
        return -($amount - $total);
    // return the difference between the amount and the total
    return abs( $amount - $total );
}

function mark_hachayol($qty, $school_id, $parsha_id){
    
    // prevent SQL injection
    $qty = mysql_real_escape_string($qty);
    $school_id = mysql_real_escape_string($school_id);
    $parsha_id = mysql_real_escape_string($parsha_id);
    // generate the SQL
    if ($qty > 0) {
        $check_query = mysql_query("SELECT * FROM hachayol_shipping WHERE school_id = '$school_id' AND parsha_id = '$parsha_id'");
        if ( mysql_num_rows($check_query) > 0 ){
            $sql = "UPDATE hachayol_shipping SET qty='$qty' WHERE school_id = '$school_id' AND parsha_id = '$parsha_id'";
        } else {
            $sql = "INSERT INTO hachayol_shipping (qty, school_id, parsha_id) VALUES ('$qty', '$school_id', '$parsha_id')";
        }
        return !!mysql_query($sql); // return the result
    } else {
        $sql = "DELETE FROM hachayol_shipping WHERE school_id='$school_id' AND parsha_id=$parsha_id";
        return !!mysql_query($sql); // return the result
    }
}