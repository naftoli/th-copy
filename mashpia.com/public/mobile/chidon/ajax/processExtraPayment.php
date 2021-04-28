<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../api/header/db.php';

//***************** LOAD CURRENT YEAR **********************/
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once __DIR__ . '/../../../classes/authorize/AuthorizeAPIRequest.php';
require_once __DIR__ . '/../../../classes/authorize/CustomerProfile.php';

use classes\authorize\CustomerProfile;

require __DIR__ . '/encrypt.php';
$admin = $_COOKIE['admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);

$amount = $_POST['amount'];
$cc = $_POST['cc'];

$customer_id = 0;
if (intval($cc['on_file']) == 1) {
    $customer_id = getCustomerID();
    if (empty($customer_id)) {
        echo json_encode([
            'success' => false,
            'msg' => 'You do not have a credit card on file, please enter credit card info and try again.'
        ]);
        exit;
    }
} else {
    $ccResult = processCC($customer_id);
    if ($ccResult['error']) {
        echo json_encode([
            'success' => false,
            'msg' => $ccResult['error']
        ]);
        exit;
    } else {
        // update tables
        $sql = "update th_chidon_zelda set balance = 0 where admin_id = " . $admin_id;
        mysql_query($sql);
        $sql = "update th_chidon_zelda_extra set extra = 0 where admin_id = " . $admin_id;
        mysql_query($sql);
        echo json_encode([
            'success'   => true,
            'msg'   => $ccResult['success']
        ]);
        exit;
    }
}

function getCustomerID() {
    global $admin_id;

    $sql = "select authorize_customer_profile_id from admins where admin_id = " . $admin_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['authorize_customer_profile_id'];
}

function processCC( $customer_id )
{
    global $cc, $admin_id, $year, $amount;

    $response = '';
    $desc = "Chidon Registration Final Payment " . $year . " for family (admin_id): " . $admin_id;
    if ( $customer_id ) {
        $cp = new CustomerProfile( $customer_id );
        $response = $cp->chargeCard( $amount, null, null, null, $desc );
    } else {
        $cc_info = [];
        $cc_info['number'] = $cc['num'];
        $cc_info['exp'] = $cc['mm'] . '' . $cc['yy'];
        $cc_info['cvc'] = $cc['cvv'];
        $cc_info['desc'] = $desc;
        $cc_info['last'] = $cc['name'];
        $cc_info['first'] = '';
        $cc_info['address'] = $cc['address'];
        $cc_info['city'] = $cc['city'];
        $cc_info['state'] = $cc['state'];
        $cc_info['zip'] = $cc['zip'];
        $cc_info['country'] = $cc['country'];

        require __DIR__ . '/../../../chidonOld/chidon_drive/ajax/authorize.php';
        $response = chargeCreditCard($amount, $cc_info);
    }

    return parseResponse($response);
}