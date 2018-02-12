<?
// function to render a single row in the table. Change this code to modify all the rows in the table....
function render_row($shipment, $staff=false, $user=false){
    global $admin_user; // use the global $admin_user object while rendering
    
    $row = "<tr>";
    if ($staff){ // staff info is kept in the shipment....
        $row .= "<td>".$shipment['name']."</td>";
        $row .= "<td>".$shipment['position']."</td>";
    } else if($user) {
        $row .= "<td>".$user['last'].", ".$user['first']."</td>";
        $row .= "<td>".$user['class_grade'].($user['class_sub'] ? " - ".$user['class_sub'] : "")."</td>";
    } else {
        $row = "<td></td><td></td>"; // render two blank rows
    }
    
    $row .= '<td class="item"> ' . $shipment['item'] . '</td>';
    $row .= '<td class="status"> ' . ($shipment['shipped'] ? "Shipped" : "Not Shipped") . '</td>';
    $row .= '<td class="missing"><a style="display: ' .($shipment['shipped'] ? "inline-block" : "none").'" class="button" data-ajax="'.$shipment['ajax'].'">Missing</a></td>';
    // if it is a superuser render the row with the shipping checkboxes and shipment dropdowns
    if ($admin_user['auth'] == 'super') {
        $row .= '<td style="text-align: center;"><label class="fancy-check-container">';
            $row .=  '<input class="shipped_toggle" type="checkbox" '.($shipment['shipped'] ? "checked" : "").' data-ajax="'.$shipment['ajax'].'"/>';
            $row .=  '<span class="fancy-check"></span>';
        $row .=  '</label></td>';
        $row .=  '<td class="shipment_dropdown" data-shipment_id="'.$shipment['shipment_id'].'"></td>';
    } else { // regular users just get to see the shipment that it is part of....
        $row .= '<td class="shipment_text">'.$shipment['shipment'].'</td>';
        //$row .= "<td></td>";
    }
    
    $row .= "<tr>";
    
    echo $row; // render the row on to the page
}

// handles the complex rendering for each students shipment list....
function render_shipments($user, $shipments, $staff=false) {
    global $totals; // update the totals....
    
    // do not show the user if there are not shipments
    if (count($shipments) == 0) return false;
    
    // render the first shipment with the user info
    $shipment = array_shift($shipments); // pull the first shipment from the array
    isset($totals[$shipment['item']]) ? $totals[$shipment['item']] += 1 : $totals[$shipment['item']] = 1; // update the totals...
    render_row($shipment, $staff, $user); // render the first shipment with the user info
    // render the rest of the shipments....
    foreach($shipments as $shipment){
        isset($totals[$shipment['item']]) ? $totals[$shipment['item']] += 1 : $totals[$shipment['item']] = 1; // update the totals...
        render_row($shipment);
    }
    
    return true; // we rendered the users shipments
}


/*  render_by_status($users_shipments_array, $order[, $staff=false])
 *  
 *  params:
 *      $users_shipments_array:
 *          array of users and shipments with the following structure: (user may be replaced with $staff)
 *              [[$user, $shipments], [$user, $shipments]]
 *      $order:
 *          "DESC" => not-shipped first...
 *          anything else => shipped_first....
 */
function render_by_status($users_shipments_array, $order, $staff = false) {
    // go through all the users once
    foreach($users_shipments_array as $user_shipments){// for each user_shipment array....
        $shipments = $user_shipments[1];    $user = $user_shipments[0]; // get the user and the shipment
        $shipments = filter_shipping_status($shipments, ($order == "DESC" ? "not-shipped" : "shipped" )); // filter the shipments...
        render_shipments($user, $shipments, $staff); // and render them
    } // end round one
    // go throught the users again
    foreach($users_shipments_array as $user_shipments){ // for each user_shipment array....
        $shipments = $user_shipments[1];    $user = $user_shipments[0]; // get the user and the shipments from the array....
        $shipments = filter_shipping_status($shipments, ($order == "DESC" ? "shipped" : "not-shipped" )); // filter to get the other status...
        render_shipments($user, $shipments, $staff); // and render them
    } // end round two
} // end render_by_status function....
