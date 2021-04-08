<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

if (!isset($_POST['th_chidon_parent_purchase_id'], $_POST['person_relation_key'])) {
    exit("missing required params");
}
$th_chidon_parent_purchase_id = $_POST['th_chidon_parent_purchase_id'];
$person_relation_key = $_POST['person_relation_key'];
$sql = "UPDATE th_chidon_parent_purchases
    set $person_relation_key = null,
        {$person_relation_key}_ship = 0,
        {$person_relation_key}_ship_addr = null
    where th_chidon_parent_purchase_id = $th_chidon_parent_purchase_id";
$query = mysql_query($sql);
echo $query ? "1" : "0";