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
    global $info, $item_details_chosen, $items_chosen, $limit_to_status, $superAdmin;

    $school = $admin_id; // school is the admin_id in this case bc we are using the code from class.chidon_shipping.php
    foreach ($items_chosen as $cat => $more) {
        if (isset($info[$cat]) && isset($info[$cat][$school])) {
            $items = $info[$cat][$school];
            foreach ($items as $item) {
                // get status and whether to show this item
                $show_item = false;
                $status = isset($info['status'][$admin_id][$item['id']]) ? $info['status'][$admin_id][$item['id']] : [];
                if (empty($limit_to_status)) $show_item = true;
                else {
                    foreach ($limit_to_status as $idx) {
                        if ($idx == 0 && (empty($status) || $status['status'] == 0)) {
                            $show_item = true;
                            break;
                        }
                        else if (!empty($status) && $status['status'] == $idx) {
                            $show_item = true;
                            break;
                        }
                    }
                }
                if ($show_item) {
                    if ($output) {
                        // create new row
                        echo "<tr><td>" . ($item['type'] ? ($item['type'] . ' ') : '') . $item['item'] . "</td>";
                        if ($item_details_chosen && count($item_details_chosen)) {
                            foreach ($item_details_chosen as $field) {
                                if ($field == 'item') continue;
                                if ($field == 'qty') echo "<td class='qty'>" . (isset($item[$field]) ? $item[$field] : 1) . "</td>";
                                else echo "<td>";
                                if (isset($item[$field])) echo $item[$field];
                                echo "</td>";
                            }
                        }
                        echo "<td>" . $item['type'] . "</td>";
                        echo "<td>" . $item['size'] . "</td>";
                        // add column for shipping info
                        echo "<td class='no-print'>";
                        $originalValue = empty($status) ? 0 : $status['status'];
                        echo "<select id='" . $item['id'] . ':' . $admin_id . "' class='shipping' data-original-value='$originalValue'>";
                        if (!$superAdmin && (empty($status) || $status['status'] == 0)) $options = ['Not Yet Shipped'];
                        else $options = ['Not Yet Shipped', 'Shipped', 'Missing', 'Damaged', 'Replaced'];
                        foreach ($options as $i => $val) {
                            echo "<option value='$i'";
                            /*
                             * 0 = not yet shipped
                             * 1 = shipped
                             * 2 = missing
                             * 3 = damaged
                             * 4 = replaced
                             */
                            if ($i == 0 && (empty($status) || $status['status'] == 0)) echo " selected";
                            else if (!empty($status) && $i == $status['status']) echo " selected";
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
                        echo "</select></td><td><textarea class='description' rows='3' cols='15'>" . $status['description'] . "</textarea></td></tr>";
                    } else {
                        // update summary
                        addToSummary($item, $status);
                    }
                }
            }
        }
    }
}

function addToSummary($item, $status) {
    global $summary, $summary_items;

    $id = $item['id'];
    $qty = isset($item['qty']) ? intval($item['qty']) : 1;
    if (! empty($status['description'])) $id .= '*';

    if (! in_array($id, array_keys($summary_items))) $summary_items[$id] = $item;

    if (isset($summary[$id])) $summary[$id] += $qty;
    else $summary[$id] = $qty;
}

function makeTextForExcel($text) {
    if (empty($text)) return '';
    // first strip out all non-numeric characters
    $text = preg_replace('/[^0-9]/', '', $text);
    // add a - after the first 3 digits
    $text = substr($text, 0, 3) . '-' . substr($text, 3);
    // find out how many digits we have
    $digits = strlen($text);
    if ($digits == 10) {
        // add a - after the first 7 digits
        $text = substr($text, 0, 7) . '-' . substr($text, 7);
    }
    return $text;
}