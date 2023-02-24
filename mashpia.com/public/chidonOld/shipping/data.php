<?php
// create array of fields for the admin to choose from
// key refers to input name and value refers to what the user will see
// key contains the table/field that we need to fetch
$fields = [
    's.school_name'         => 'School',
    'c.class_grade'         => 'Class Grade',
    'c.class_sub'           => 'Class Sub',
    'c.class_teacher'       => 'Teacher',
    'u.user_serial'         => 'Serial Number',
    'u.first'               => 'First Name',
    'u.last'                => 'Last Name',
    's.shipping_first'      => 'Shipping First Name',
    's.shipping_last'       => 'Shipping Last Name',
    's.shipping_phone'      => 'Shipping Contact Number',
    's.shipping_address1'   => 'Shipping Address 1',
    's.shipping_address2'   => 'Shipping Address 2',
    's.shipping_city'       => 'Shipping City',
    's.shipping_state'      => 'Shipping State',
    's.shipping_postal'     => 'Shipping Zip',
    's.shipping_country'    => 'Shipping Country',
    's.shipping_requests'   => 'Shipping Requests'
];

$item_details = [
    'id'        => 'Item ID',
    'qty'       => 'Quantity',
    'size'      => 'Size',
    'color'     => 'Color/Gender',
    'name'      => 'Personalization Name',
    'cat'       => 'Category'
];

function build_fields() {
    global $fields;
    $i = 1;
    $html = "<input type='checkbox' name='all_fields' id='all_fields' /> ALL FIELDS<br />";
    foreach ($fields as $field => $desc) {
        $html .= "<input type='checkbox' name='fields[" . $field . "]' class='field'";
        if ($i++ <= 7) $html .= " checked";
        $html .= " /> " . $desc . "<br />";
    }
    return $html;
}

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
        foreach ($items[$cat] as $item) {
            $name = strtolower($item);
            $html .= "<input type='checkbox' name='items[" . $cat . "][" . $name . "]' class='item' /> " . ucwords($item) . "<br />";
        }
    }
    return $html;
}

function createHtmlForItem($school, $row, $output = true) {
    global $info, $fields_chosen, $item_details_chosen, $items_chosen, $super, $limit_to_status;

    foreach ($items_chosen as $cat => $more) {
        if (isset($info[$cat]) && isset($info[$cat][$row['user_id']])) {
            $items = $info[$cat][$row['user_id']];
            foreach ($items as $item) {
                // get status and whether to show this item
                $status = isset($info['status'][$row['user_id']][$item['id']]) ? $info['status'][$row['user_id']][$item['id']] : [];
                $statusDesc = [
                    1   => 'shipped', 
                    2   => 'missing', 
                    3   => 'damaged'
                ];
                if ($limit_to_status > 0) {
                    if (!isset($status[$statusDesc[$limit_to_status]]) || intval($status[$statusDesc[$limit_to_status]]) == 0) continue;
                }
                if ($output) {
                    // create new row
                    echo "<tr>";
                    foreach ($fields_chosen as $field) {
                        if (strpos($field, 'shipping') === false) {
                            $desc = substr($field, strpos($field, '.') + 1);
                            echo "<td>" . $row[$desc] . "</td>";
                        }
                    }
                    echo "<td>" . $item['item'];
                    if ($item_details_chosen && count($item_details_chosen)) {
                        foreach ($item_details_chosen as $field) {
                            echo "</td><td>";
                            if ($field == 'cat') echo $cat;
                            else if ($field == 'qty') echo isset($item[$field]) ? $item[$field] : 1;
                            else if (isset($item[$field])) echo $item[$field];
                        }
                    }
                    echo "</td>";
                    // add column for shipping info
                    echo "<td class='no-print'>";
                    echo "<select id='" . $item['id'] . ':' . $row['user_id'] . "' class='shipping'";
                    // figure out if it should be disabled or not
                    if (!$super && (empty($status) || intval($status['shipped']) == 0)) echo " disabled";
                    echo ">";
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
                    echo "</select></td></tr>";
                }

                // update summary
                addToSummary($item, $school);
            }
        }
    }
}

function addToSummary($item, $school) {
    global $summary, $summary_items, $MASHPIA_DB;

    $key = $item['id'];
    $qty = isset($item['qty']) ? intval($item['qty']) : 1;
    if (is_array($key)) print_r($key);

    if (! in_array($key, array_keys($summary_items))) $summary_items[$key] = $item;

    if (isset($summary[$school][$key])) $summary[$school][$key] += $qty;
    else $summary[$school][$key] = $qty;
}

function createCSV($items) {
    global $items_chosen, $MASHPIA_DB, $schools;

    // create sql to get all needed fields
    $sql = "SELECT 
                a.*, u.user_id, u.first AS u_first, u.last AS u_last, u.user_serial, u.school_id 
            FROM
                admins a
                    JOIN
                admin_auths aa USING (admin_id)
                    JOIN
                users u ON u.user_id = aa.id
            WHERE
                aa.auth = 'user'
                    AND u.school_id IN (61 , 269)
                    AND u.user_registered > 0";
    $stmt = $MASHPIA_DB->query($sql);
    $rows = $stmt->fetchAll();

    $admins = [];
    $children = [];
    $users = [];
    foreach ($rows as $row) {
        $admins[$row['admin_id']] = $row;
        $children[$row['user_id']] = $row['admin_id'];
        $users[$row['user_id']] = $row;
    }

    $info = [];
    foreach ($children as $user_id => $admin_id) {
        foreach ($items_chosen as $cat => $other) {
            if (isset($items[$cat]) && isset($items[$cat][$user_id])) {
                $details = $items[$cat][$user_id];
                foreach ($details as $item) {
                    $info[$user_id][] = $item;
                }
            }
        }
    }

    $i = 0;
    $csv[$i++] = ['Order Number', 'Recipient Full Name', 'Recipient First Name', 'Recipient Last Name', 'Recipient Phone',
        'Recipient Company', 'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code',
        'Country Code', 'Item SKU', 'Item Name 1', 'Item Quantity', 'Item Options'];
    $csv[$i++] = ['Family ID', 'Parent Full Name', 'Parent First Name', 'Parent Last Name', 'Recipient Phone', 'School - Shipping Type',
        'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code', 'Country Code', 'CHI Number',
        'Full Item Name', 'Quantity', 'Child Name - Serial #'];
    foreach ($children as $user_id => $admin_id) {
        if (isset($info[$user_id])) {
            foreach ($info[$user_id] as $item) {
                $admin = $admins[$admin_id];
                $phone = $admin['admin_phone_mobile'] ?? $admin['admin_phone_work'] ?? $admin['admin_phone_home'] ?? '';
                $user = $users[$user_id];
                $school = $user['school_id'] == 61 ? 'MyShliach - Shipping' : 'Anash Kinder - Pickup';
                $qty = $item['qty'] ?? 1;

                $csv[$i++] = [$admin_id, ($admin['first'] . ' ' . $admin['last']), $admin['first'], $admin['last'],
                    $phone, $school, $admin['admin_address1'], $admin['admin_address2'], '', $admin['admin_city'],
                    $admin['admin_state'], $admin['admin_postal'], $admin['admin_country'], $item['id'], $item['item'],
                    $qty, ($user['first'] . ' ' . $user['last'] . ' - ' . $user['user_serial'])];
            }
        }
    }
    return $csv;
}

function createFile($name, $info) {
    $fp = fopen($name, "w");
    fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // utf8
    if (is_array($info)) {
        foreach ($info as $fields) {
            fputcsv($fp, $fields);
        }
    } else {
        fputs($fp, $info);
    }
    fclose($fp);
}

function downloadFile($filename) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    flush(); // Flush system output buffer
    readfile($filename);
    unlink($filename);
}
