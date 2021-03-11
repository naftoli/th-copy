<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require __DIR__ . '/../../header.php';
require __DIR__ . '/../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

//***************** LOAD CURRENT YEAR **********************/
require_once __DIR__ . '/../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once __DIR__ . '/../../classes/authorize/AuthorizeAPIRequest.php';
require_once __DIR__ . '/../../classes/authorize/CustomerProfile.php';
require_once __DIR__ . '/../../classes/authorize/PaymentProfile.php';

use classes\authorize\CustomerProfile;

$info = [];
$sql = "select a.admin_id, a.first, a.last, a.admin_address1, a.admin_city, a.admin_state, a.admin_postal, a.admin_country, a.admin_email, 
        a.authorize_customer_profile_id, tcpp.authorize_trans_type, tcpp.amount, tcpp.purchase_date  
        from admins a 
        join th_chidon_parent_purchases tcpp using (admin_id) 
        where (tcpp.authorize_id = 1 or tcpp.authorize_id = '') 
        and a.admin_id not in (3, 1264) 
        group by a.admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

foreach ($info as $row) {
    $customer_id = $row['authorize_customer_profile_id'];
    $method = $row['authorize_trans_type'];
    $amount = $row['amount'];
    $desc = "Chidon Registration " . $year . " for family (admin_id): " . $row['admin_id'];
    $cp = new CustomerProfile($customer_id);
    if ($method == 'charge') $response = $cp->chargeCard($amount, null, null, null, $desc);
    else if ($method == 'hold') $response = $cp->chargeCard($amount, null, null, null, $desc, 'authOnlyTransaction');
    echo "<pre>"; print_r($response); echo "</pre>";
    if (is_arrray($response)) {
        if ($response->getMessages()->getResultCode() == "Ok") {
            // update th_chidon_parent_purchases
            $tresponse = $response->getTransactionResponse();
            $trans_id = $tresponse->getTransId();
            $trans_info = $trans_id . ":" . $tresponse->getResponseCode() . ":" . $tresponse->getMessages()[0]->getCode() . ":". $tresponse->getAuthCode() . ":" . $tresponse->getMessages()[0]->getDescription();
            $sql = "update th_chidon_parent_purchases 
                    set authorize_id = " . $trans_id . ", 
                    authorize_description = '" . $trans_info . "' 
                    where admin_id = " . $row['admin_id'] . ", 
                    and year = " . $year . " 
                    and purchase_date = '" . $row['purchase_date'] . "' 
                    and authorize_id = 1";
            if (!mysql_query($sql)) echo "error updating db.<br />" . $sql;
        }
    }
    echo "<br /><br />";
}