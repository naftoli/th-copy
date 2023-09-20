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
    'cat'       => 'Category',
    'rank'      => 'Rank (for Gear Category)'
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
                $show_item = false;
                $status = isset($info['status'][$row['user_id']][$item['id']]) ? $info['status'][$row['user_id']][$item['id']] : [];
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
                        echo "</select></td><td><textarea class='description' rows='3' cols='15'>" . $status['description'] . "</textarea></td></tr>";
                    } else {
                        // update summary
                        addToSummary($item, $school);
                    }
                }
            }
        }
    }
}

function addToSummary($item, $school) {
    global $summary, $summary_items, $grand_summary;

    $key = $item['id'];
    $qty = isset($item['qty']) ? intval($item['qty']) : 1;
//    if (is_array($key)) print_r($key);

    if (! in_array($key, array_keys($summary_items))) $summary_items[$key] = $item;

    if (isset($summary[$school][$key])) $summary[$school][$key] += $qty;
    else $summary[$school][$key] = $qty;

    if (isset($grand_summary[$key][$school])) $grand_summary[$key][$school] += $qty;
    else $grand_summary[$key][$school] = $qty;
}

function checkShippingStatus($admin_id) {
    global $MASHPIA_DB, $year;

    $status = 'pickup';
    $sql = "select * from chidon_parent_shipping 
            where year = :year 
            and parent_id = :id";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        'year'  => $year,
        'id'    => $admin_id
    ]);
    $row = $stmt->fetch();
    if ($row && $row['amount_paid'] > 0) $status = 'shipping';
    return $status;
}

function createCSV($items, $school_id, $usOnly = false, $intlOnly = false) {
    global $items_chosen, $MASHPIA_DB;

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
                    AND u.school_id = :id";
    if ($usOnly) $sql .= " AND a.admin_country = 'USA'";
    if ($intlOnly) $sql .= " AND a.admin_country != 'USA'";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute(['id' => $school_id]);
    $rows = $stmt->fetchAll();

    $admins = [];
    $children = [];
    $users = [];
    $shipping_status = [];
    foreach ($rows as $row) {
        $admins[$row['admin_id']] = $row;
        $children[$row['user_id']] = $row['admin_id'];
        $users[$row['user_id']] = $row;
        $shipping_status[$row['user_id']] = checkShippingStatus($row['admin_id']);
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
        'Country Code', 'Item SKU', 'Item Name 1', 'Item Quantity', 'Item Options', 'Recipient Email'];
    $csv[$i++] = ['Family ID', 'Parent Full Name', 'Parent First Name', 'Parent Last Name', 'Recipient Phone', 'School - Shipping Type',
        'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code', 'Country Code', 'CHI Number',
        'Full Item Name', 'Quantity', 'Child Name - Serial #', 'Recipient Email'];
    foreach ($children as $user_id => $admin_id) {
        if (isset($info[$user_id])) {
            foreach ($info[$user_id] as $item) {
                $admin = $admins[$admin_id];
                $phone = $admin['admin_phone_mobile'] ?? $admin['admin_phone_work'] ?? $admin['admin_phone_home'] ?? '';
                $first = empty($admin['father']) ? $admin['first'] : ($admin['father'] . ' ' . $admin['mother']);
                $user = $users[$user_id];
                $school = $user['school_id'] == 61 ? 'MyShliach' : 'Anash Kinder';
                $shipping = $shipping_status[$user_id];
                $qty = $item['qty'] ?? 1;
                $itemDesc = '';
                if ($item['name']) $itemDesc .= "Personalized ";
                $itemDesc .= $item['item'];
                if ($item['color']) $itemDesc .= ", " . $item['color'];
                if ($item['size']) $itemDesc .= ", size: " . $item['size'];

                $csv[$i++] = [$admin_id, ($first . ' ' . $admin['last']), $admin['first'], $admin['last'],
                    $phone, ($school . '-' . ucwords($shipping)), $admin['admin_address1'], $admin['admin_address2'], '', $admin['admin_city'],
                    $admin['admin_state'], $admin['admin_postal'], $admin['admin_country'], $item['id'], $itemDesc,
                    $qty, ($user['u_first'] . ' ' . $user['u_last'] . ' - ' . $user['user_serial']), $admin['admin_email']];
            }
        }
    }
    return $csv;
}

function createCSVforGear($users, $items) {
    global $MASHPIA_DB;

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
                aa.auth = 'user' AND aa.id in (" . implode(',', $users) . ")";
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
        if (isset($items[$user_id])) {
            $details = $items[$user_id];
            foreach ($details as $item) {
                $info[$user_id][] = $item;
            }
        }
    }

    $i = 0;
    $csv[$i++] = ['Order Number', 'Recipient Full Name', 'Recipient First Name', 'Recipient Last Name', 'Recipient Phone',
        'Recipient Company', 'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code',
        'Country Code', 'Item SKU', 'Item Name 1', 'Item Quantity', 'Item Options', 'Recipient Email'];
    $csv[$i++] = ['Family ID', 'Parent Full Name', 'Parent First Name', 'Parent Last Name', 'Recipient Phone', 'School - Shipping Type',
        'Address Line 1', 'City', 'State', 'Postal Code', 'Country Code', 'CHI Number',
        'Full Item Name', 'Quantity', 'Child Name - Serial #', 'Recipient Email'];

    foreach ($info as $user_id => $more) {
        foreach ($more as $item) {
            $admin = $admins[$children[$user_id]];
            $phone = $admin['admin_phone_mobile'] ?? $admin['admin_phone_work'] ?? $admin['admin_phone_home'] ?? '';
            $first = empty($admin['father']) ? $admin['first'] : ($admin['father'] . ' ' . $admin['mother']);
            $user = $users[$user_id];
            $qty = $item['qty'] ?? 1;
            $itemDesc = '';
            $itemDesc .= $item['item'];
            if ($item['color']) $itemDesc .= ", " . $item['color'];
            if ($item['size']) $itemDesc .= ", size: " . $item['size'];
            if ($item['rank']) $itemDesc .= ", rank: " . $item['rank'];
            if ($item['address'] == 'pickup') {
                $shipping = 'pickup from JCM';
                $addressInfo[0] = '';
                $addressInfo[1] = '';
                $addressInfo[2] = '';
                $addressInfo[3] = '';
            } else {
                $shipping = 'ship';
                $addressInfo = explode(', ', $item['address']);
            }

            $csv[$i++] = [$admin_id, ($first . ' ' . $admin['last']), $admin['first'], $admin['last'],
                $phone, $shipping, $addressInfo[0], $addressInfo[1], $addressInfo[2], $addressInfo[3], 'USA', $item['id'], $itemDesc,
                $qty, ($user['u_first'] . ' ' . $user['u_last'] . ' - ' . $user['user_serial']), $admin['admin_email']];
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

function createZip($files, $filename) {
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file) {
        $zip->addFromString($file, file_get_contents($file));
        unlink($file);
    }
    $zip->close();
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

function getUpdatedSchools($schools) {
    global $MASHPIA_DB;
    $sql = "select * from schools where school_id in (" . implode(',', array_keys($schools)) . ")";
    $result = $MASHPIA_DB->query($sql);
    $rows = $result->fetchAll();
    $schools = [];
    foreach ($rows as $row) {
        $schools[$row['school_id']] = $row['chidon_5783_updated_shipping'];
    }
    return $schools;
}
