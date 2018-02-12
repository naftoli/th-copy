<?php
//// show any errors that occur
error_reporting(E_ALL);
ini_set('display_errors', '1');
// load dependencies
require_once("../../../db.php");
require_once("../PaymentProfile.php");
require_once("../CustomerProfile.php");
use \classes\authorize\PaymentProfile;
use \classes\authorize\CustomerProfile;
// if it is a one off
if (isset($_POST["schoolId"])) {
    echo "<pre>";
    // get all the information we need from the school and it's admin (his email address) if the school is a chayolei and has CC information
    $sql = 'SELECT schools.school_id, schools.school_name as name, 
schools.cc_number, schools.cc_exp, schools.cc_cvv,
schools.school_address1 as address, schools.school_city as city, schools.school_state as state, schools.school_postal as zip,
COALESCE(NULLIF(admins.admin_email, ""), schools.principal_email) as email
FROM schools
inner join admin_auths on schools.school_id = admin_auths.id
inner join admins on admins.admin_id = admin_auths.admin_id
where chayolei != 0 and
schools.cc_number != "" and schools.cc_number is not null and
CHAR_LENGTH(schools.cc_number) > 14 and
schools.school_id = ' . ms($_POST["schoolId"]) .' group by schools.school_id order by schools.school_id';
//execute the query
    $query = mq($sql);
    // get the data
    $result = mysql_fetch_assoc($query);
    // print it to the screen
    
    // get the billing array
    $billto = ["address" => $result['address'], "city" => $result['city'], "state" => $result['state'], "zip" => $result['zip']];
    // make sure it is good
    if(count(array_unique($billto)) == 1 && end($billto) === ''){ // set the billing to null if all are blank
        $billto = null;
    }
    // create the payment profile
    $payment_profile = PaymentProfile::createBasicArray($result['cc_number'],$result['cc_exp'],$result['cc_cvv'], $billto, true);
    // set the fields for the customer profile
    $id = $result['school_id'];
    $email = $result['name'];
    $name = $result['email'];
    // and then create each one
    $customer_profile = CustomerProfile::create("CTH_$id", $email, $name, $payment_profile);
    if(!($customer_profile instanceof CustomerProfile)){
        echo "\nERROR: \n";
        print_r($result);
        print_r($customer_profile);
    } else {
        echo "\n $name ($id) : Success \n";
    }
    // and save it
    mq("UPDATE schools SET authorize_customer_profile_id = ". $customer_profile->customerProfileId . ", authorize_payment_profile_id = " . $customer_profile->paymentProfiles[0]["customerPaymentProfileId"] . " WHERE school_id = $id");
    
    echo "</pre>";
} elseif(isset($_POST["runMigration"])) {
    // get the info from the school if they have all the info and have not been migrated yet.
    $sql = 'SELECT schools.school_id, schools.school_name as name, 
schools.cc_number, schools.cc_exp, schools.cc_cvv,
schools.school_address1 as address, schools.school_city as city, schools.school_state as state, schools.school_postal as zip,
COALESCE(NULLIF(admins.admin_email, ""), schools.principal_email) as email
FROM schools
inner join admin_auths on schools.school_id = admin_auths.id
inner join admins on admins.admin_id = admin_auths.admin_id
where chayolei != 0 and
schools.cc_number != "" and schools.cc_number is not null and
CHAR_LENGTH(schools.cc_number) > 14 and
schools.authorize_customer_profile_id is null
group by schools.school_id
order by schools.school_id';

    $query = mq($sql);
    echo "<h1>Full Migration Results: </h1>";
    echo "<pre>";
    while ($result = mysql_fetch_assoc($query)){
        // get the billing address
        $billto = ["address" => $result['address'], "city" => $result['city'], "state" => $result['state'], "zip" => $result['zip']];
        // make sure it is good
        if(count(array_unique($billto)) == 1 && end($billto) === ''){ // set the billing to null if all are blank
            $billto = null;
        }
        // create the payment profile
        $payment_profile = PaymentProfile::createBasicArray($result['cc_number'],$result['cc_exp'],$result['cc_cvv'], $billto, true);
        // set the fields for the customer profile
        $id = $result['school_id'];
        $email = $result['name'];
        $name = $result['email'];
        // and then create each one
        $customer_profile = CustomerProfile::create("CTH_$id", $email, $name, $payment_profile);
        if(!($customer_profile instanceof CustomerProfile)){ // if we get an array back (bad customer profile)
            echo "ERROR: \n";
            print_r($result);
            print_r($customer_profile);
            echo "\n";
        } else {
            echo "$name ($id) : Success \n";
            // and save the profile info to the database
            mq("UPDATE schools SET authorize_customer_profile_id = ". $customer_profile->customerProfileId .
               ", authorize_payment_profile_id = " . $customer_profile->paymentProfiles[0]["customerPaymentProfileId"] .
               " WHERE school_id = $id");
        }
    }
    echo "</pre>";
}

?>
<html>
<head>
    <title>Tzivos Hashem | Authorize.net Profile Demo</title>
<!--    Bootstrap 4 css-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <h1>Compleate and removed</h1>

        <form action="" method="post">
            <div class="form-group">
                <label>School ID</label>
                <input class="form-control" type="text" name="schoolId" value=""/>
            </div>
            
            <input class="btn btn-primary" type="submit"/>
        </form>
        
        <h2>Run Full Migration</h2>
        <form action="" method="post">
            <label>Run Migration?</label>
            <input type="checkbox" name="runMigration"/>
            <br/>
            <input class="btn btn-primary" type="submit"/>
        </form>
    </div>
</body>

