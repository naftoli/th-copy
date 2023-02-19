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
    'qty'       => 'Quantity',
    'size'      => 'Size',
    'color'     => 'Color',
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