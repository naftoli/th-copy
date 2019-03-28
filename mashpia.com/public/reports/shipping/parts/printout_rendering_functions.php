<?
/*  function printout_shipment_row($user)
 *
 *  Prints out the header on the top of the shipping printouts
 */

function printout_shipment_row($shipments, $user, $staff = false) {
    global $admin_user; // use the global $admin_user object while rendering
    
    $row  = "<div class='shipping_box'>";
    $row .=     "<div class='shipping_inner_box'>";
    $row .=         '<span class="checkbox-new"><i class="fa fa-square-o" aria-hidden="true"></i></span>'; // render the empty checkbox before each row....
    $row .=         '<div class="shipping_details">'; // render the details of the shipment...
    // render the differnet items for users or staff members
    if($user) {
        $row .=         '<strong>'.$user['last'].', '.$user['first'].'</strong><br/>'; // render the details of the shipment...
        $row .=         'Grade: <strong>'.$user['class_grade'].($user['class_sub'] ? " - ". $user['class_sub'] : "").'</strong><br/>'; // render the details of the shipment...
    } elseif($staff) {
        $row .=         '<strong>'.$staff['name'].'</strong><br/>'; // render the details of the shipment...
        $row .=         'Position: <strong>'.$staff['position'].'</strong><br/>'; // render the details of the shipment...
    }
    // render the shipment's header....
    $row .=             '<strong class="shipments_header">Shipments</strong><br/>';
    // render each shipment on it's own row for now
    foreach ($shipments as $shipment) {
        $row .=         '<i class="fa fa-square-o" aria-hidden="true"></i> ';
        $row .=         ' '.$shipment['item'].'<br/>';
    } // end foreach shipment
    // close the divs...
    $row .=         "</div>"; // close the shipping_details div
    $row .=     "</div>"; // close the shipping_inner_box div
    $row .= "</div>"; // close the shipping_box div
    
    echo $row; // render the single row on to the page....
}

function printout_totals($totals) {
    $html  = '<table style="min-width: '.(count($totals) > 1 ? "100" : "50").'%;">';
    $html .=    '<tbody>';
    $html .=       '<tr>';
    $index = 0; // index to keep track of where we are in the loop
    foreach($totals as $shipment_name => $shipment_total){ // for each shipment
        // $picture = is_numeric($prize_count['picture']) ? "/file_view.php?id=".$prize_count['picture'] : $prize_count['picture']; // get the correct url for the image
        if($index % 2 == 0) echo "<tr>"; // if we are on an even row, render a new row
        //$html .= '<td class="td-img"><img src="'.$picture.'"/></td>';
        $html .= '<td>'.$shipment_name.'</td>';
        $html .= '<td>'.$shipment_total.'</td>';
        $html .= '<td><span class="checkbox-new"><i class="fa fa-square-o" aria-hidden="true"></i></span></td>';
        if($index % 2 != 0) {
            $html .= "</tr>"; // if we are on an odd row, end the row,
        } else {
            $html .= "<td class='spacer'></td>"; // if we are on an even row, add a spacer for the next item...
        } $index++; // increment the index
    } // end for each prize total
    $html .=        '</tr>';
    // render the grand total....
    $html .=        '<tr>';
    $html .=            '<th class="grand_total">Grand Total:</th>';
    $html .=            '<th class="grand_total">'.array_sum($totals).'</th>';
    $html .=            '<td><span class="checkbox-new"><i class="fa fa-square-o" aria-hidden="true"></i></span></td>';
    $html .=        '</tr>';
    // close off the table...
    $html .=    '</tbody>';
    $html .= '</table>';
    
    echo $html;
}