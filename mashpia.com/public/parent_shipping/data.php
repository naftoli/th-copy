<?php
// create array of fields for the admin to choose from
// key refers to input name and value refers to what the user will see
// key contains the table/field that we need to fetch
//$fields = [
//    's.school_name'         => 'School',
//    's.shipping_first'      => 'Shipping First Name',
//    's.shipping_last'       => 'Shipping Last Name',
//    's.shipping_phone'      => 'Shipping Contact Number',
//    's.shipping_address1'   => 'Shipping Address 1',
//    's.shipping_address2'   => 'Shipping Address 2',
//    's.shipping_city'       => 'Shipping City',
//    's.shipping_state'      => 'Shipping State',
//    's.shipping_postal'     => 'Shipping Zip',
//    's.shipping_country'    => 'Shipping Country',
//    's.shipping_requests'   => 'Shipping Requests'
//];

$item_details = [
    'id'        => 'Item ID',
    'qty'       => 'Quantity',
    'item'      => 'Prize Name',
    'cat'       => 'Category'
];

//function build_fields() {
//    global $fields;
//    $i = 1;
//    $html = "<input type='checkbox' name='all_fields' id='all_fields' /> ALL FIELDS<br />";
//    foreach ($fields as $field => $desc) {
//        $html .= "<input type='checkbox' name='fields[" . $field . "]' class='field' checked='checked' /> " . $desc . "<br />";
//    }
//    return $html;
//}

function build_details() {
    global $item_details;
    $html = "<input type='checkbox' name='all_details' id='all_details' /> ALL DETAILS<br />";
    foreach ($item_details as $desc => $detail) {
        $html .= "<input type='checkbox' name='details[" . $desc . "]' class='detail' /> " . ucwords($detail) . "<br />";
    }
    return $html;
}

function build_items() {
    global $categories, $items;
    $html = "<input type='checkbox' name='all_items' id='all_items' /> ALL ITEMS<br />";
    foreach ($categories as $cat) {
        $html .= "<h4>" . ucwords($cat) . "</h4>";
        $html .= "<div><input type='checkbox' name='items[" . $cat . "]' class='check_items' /> All " . ucwords($cat) . "<br />";
        foreach ($items[$cat] as $item) {
            $name = strtolower($item);
            $html .= "<input type='checkbox' name='items[" . $cat . "][" . htmlspecialchars($name, ENT_QUOTES) . "]' class='item' /> " . ucwords($item) . "<br />";
        }
        $html .= "</div>";
    }
    return $html;
}

function createHtmlForItem($admin_id, $output = true) {
    global $info, $item_details_chosen, $items_chosen, $limit_to_status;

    $school = $admin_id; // school is the admin_id in this case bc we are using the code from class.chidon_shipping.php
    foreach ($items_chosen as $cat => $more) {
        if (isset($info[$cat]) && isset($info[$cat][$school])) {
            $items = $info[$cat][$school];
            foreach ($items as $item) {
                // get status and whether to show this item
                $show_item = false;
                $status = isset($info['status'][$school][$item['id']]) ? $info['status'][$school][$item['id']] : [];
                $statusDesc = [
                    1   => 'shipped',
                    2   => 'missing',
                    3   => 'damaged'
                ];
                if (empty($limit_to_status)) $show_item = true;
                else {
                    foreach ($limit_to_status as $idx) {
                        if ($idx == 0) {
                            if (empty($status) || ($status['shipped'] == 0 && $status['missing'] == 0 && $status['damaged'] == 0)) $show_item = true;
                        }
                        else if ($idx && !empty($status) && intval($status[$statusDesc[$idx]]) == 1) $show_item = true;
                    }
                }
                if ($show_item) {
                    if ($output) {
                        // create new row
                        echo "<tr><td>" . $item['item'];
                        if ($item_details_chosen && count($item_details_chosen)) {
                            foreach ($item_details_chosen as $field) {
                                if ($field == 'item') continue;
                                echo "</td><td>";
                                if ($field == 'qty') echo isset($item[$field]) ? $item[$field] : 1;
                                else if (isset($item[$field])) echo $item[$field];
                            }
                        }
                        echo "</td>";
                        echo "<td>" . $item['type'] . "</td>";
                        echo "<td>" . $item['size'] . "</td>";
                        // add column for shipping info
                        echo "<td class='no-print'>";
                        echo "<select id='" . $item['id'] . ':' . $school . "' class='shipping'>";
                        $options = ['Not Yet Shipped', 'Shipped', 'Missing', 'Damaged'];
                        foreach ($options as $i => $val) {
                            echo "<option value='$i'";
                            /*
                             * 0 = not yet shipped
                             * 1 = shipped
                             * 2 = missing
                             * 3 = damaged
                             */
                            switch ($i) {
                                case 0:
                                    if (empty($status) || intval($status['shipped']) == 0) echo " selected ";
                                    break;
                                case 1:
                                    if (!empty($status) && intval($status['shipped']) == 1 && intval($status['missing']) == 0
                                        && intval($status['damaged']) == 0) echo " selected ";
                                    break;
                                case 2:
                                    if (!empty($status) && intval($status['missing']) == 1) echo " selected ";
                                    break;
                                case 3:
                                    if (!empty($status) && intval($status['damaged']) == 1) echo " selected ";
                                    break;
                            }
                            echo ">" . $val . "</option>";
                        }
                        echo "</select></td>";
                        echo "<td><select class='qty'>";
                        $total = 10;
                        if (isset($item['qty'])) $total = intval($item['qty']);
                        for ($i = 1; $i <= $total; $i++) {
                            echo "<option value='$i'";
                            if (
                                !empty($status) && $status['qty'] == $i
                                || empty($status) && $i == $total
                            ) {
                                echo " selected ";
                            }
                            echo ">$i</option>";
                        }
                        echo "</select></td><td><textarea class='description' rows='3' cols='15'>" . $status['desc_of_damage'] . "</textarea></td></tr>";
                    } else {
                        // update summary
                        addToSummary($item);
                    }
                }
            }
        }
    }
}

function addToSummary($item) {
    global $summary, $summary_items;

    $id = $item['id'];
    $qty = $item['qty'] ?? 1;

    // update summary
    if (isset($summary[$id])) $summary[$id] += $qty;
    else $summary[$id] = $qty;

    // if item doesn't exist in summary_items, add it
    if (! isset($summary_items[$id])) $summary_items[$id] = $item;
}