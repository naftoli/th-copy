<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once  '../../vendor/autoload.php';
Shippo::setApiKey("shippo_test_4bec2f25543b8d1409a260e7084f1075bd3e0d3c");

/// Example from_address array
// The complete refence for the address object is available here: https://goshippo.com/docs/reference#addresses
$from_address = array(
    'name' => 'Mr Hippo',
    'company' => 'Shippo',
    'street1' => '215 Clayton St.',
    'city' => 'San Francisco',
    'state' => 'CA',
    'zip' => '94117',
    'country' => 'US',
    'phone' => '+1 555 341 9393',
    'email' => 'mr-hippo@goshipppo.com',
);

// Example to_address array
// The complete refence for the address object is available here: https://goshippo.com/docs/reference#addresses
$to_address = array(
    'name' => 'Ms Hippo',
    'company' => 'San Diego Zoo',
    'street1' => '2920 Zoo Drive',
    'city' => 'San Diego',
    'state' => 'CA',
    'zip' => '92101',
    'country' => 'US',
    'phone' => '+1 555 341 9393',
    'email' => 'ms-hippo@goshipppo.com',
);

// Parcel information array
// The complete reference for parcel object is here: https://goshippo.com/docs/reference#parcels
$parcel = array(
    'length'=> '5',
    'width'=> '5',
    'height'=> '5',
    'distance_unit'=> 'in',
    'weight'=> '2',
    'mass_unit'=> 'lb',
);

// Example shipment object
// For complete reference to the shipment object: https://goshippo.com/docs/reference#shipments
// This object has async=false, indicating that the function will wait until all rates are generated before it returns.
// By default, Shippo handles responses asynchronously. However this will be depreciated soon. Learn more: https://goshippo.com/docs/async
$shipment = Shippo_Shipment::create(
    array(
        'address_from'=> $from_address,
        'address_to'=> $to_address,
        'parcels'=> array($parcel),
        'async'=> false,
    ));

// Rates are stored in the `rates` array
// The details on the returned object are here: https://goshippo.com/docs/reference#rates
// Get the first rate in the rates results for demo purposes.
$rate = $shipment['rates'][0];

// Purchase the desired rate with a transaction request
// Set async=false, indicating that the function will wait until the carrier returns a shipping label before it returns
$transaction = Shippo_Transaction::create(array(
    'rate'=> $rate['object_id'],
    'async'=> false,
));
echo "<pre>"; print_r($transaction); echo "</pre>"; die();

// Print the shipping label from label_url
// Get the tracking number from tracking_number
if ($transaction['status'] == 'SUCCESS'){
    echo "--> " . "Shipping label url: " . $transaction['label_url'] . "\n";
    echo "--> " . "Shipping tracking number: " . $transaction['tracking_number'] . "\n";
} else {
    echo "Transaction failed with messages:" . "\n";
    foreach ($transaction['messages'] as $message) {
        echo "--> " . $message . "\n";
    }
}
// For more tutorals of address validation, tracking, returns, refunds, and other functionality, check out our
// complete documentation: https://goshippo.com/docs/
?>