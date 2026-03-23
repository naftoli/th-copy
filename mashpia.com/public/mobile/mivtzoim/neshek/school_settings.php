<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimSetting.php';

$year = GlobalSettings::getCurrentYear();
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

// fee info
$fee_info = [
    'Neshek'  =>  [
        'id'    =>  132,
        'fee'   =>  2.0,
        'shipping'  => [
            50  =>  0.50,
            100 =>  0.40,
            200 =>  0.35,
            300 =>  0.30,
            400 =>  0.25
        ]
    ]
];

$msg = '';
if ( isset( $_POST['submit'] ) ) {
    if ( isset( $_POST['school'] ) ) $school = $_POST['school'];
    else if ( count( $schools ) == 1 ) $school = key( $schools );
    else $school = 0;
    if ( $school > 0 ) {
        // figure out what the shipping fee is 
        if ( isset( $_POST['neshek'] ) ) {
            $fees = [
                132   =>  $_POST['neshek']
            ];
        } else if ( isset( $_POST['shipping-fee'] ) ) {
            $fees = [
                132   =>  $_POST['shipping-fee']
            ];
        } 
        if ( isset( $fees ) ) {
            foreach ( $fees as $item => $fee ) {
                $m = new MivtzoimSetting( $year, $school, $item );
                if ( $fee == -1 ) $success = $m->disablePurchases();
                else $success = $m->enablePurchases( $fee );
                if ( !$success ) {
                    $msg = "Error saving info.";
                    break;
                }
            }
        } else {
            $msg = "You must choose a value for Neshek candlesticks.";
        }
    } else {
        $msg = "You must choose a school.";
    }
    if ( empty( $msg ) ) $msg = "Successfully Saved.";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Neshek Settings</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Neshek Settings</h1>
        <div class='infobox'>
        <strong>Neshek (Candlesticks) price settings.</strong>
<br /><br /> 
Based on your selection will depend on how much your children will pay for their Neshek Mivtzoim Ammunition.
<br /><br />
Each Candlestick set costs $2.00
<br /><br />
If you chose PICK UP, your children will pay this price and will not be charged anything extra for shipping.
<br /><br />
<strong>Shipping charges for Neshek.</strong><br /><br />
The price of shipping for candlesticks depends on how many you guarantee will be ordered by the chayolim in your base 
<br /><br />
50 - 99 sets cost $0.50 per set to ship. (Total $2.50 per set).
<br /><br />
100 - 199 sets cost $0.40 per set to ship (Total $2.40 per set).
<br /><br />
200 - 299 sets cost $0.35 per set to ship (Total $2.35 per set).
<br /><br />
300 - 399 sets cost $0.30 per set to ship (Total $2.30 per set).
<br /><br />
400 + sets cost $0.25 per set to ship (Total $2.25 per set).
<br /><br />
<strong>Important notice:</strong><br /><br />
There is a minimum order of 50 candlestick sets that a shipping school must take. 
<br /><br />          
        </div>
        <br />
        <?php 
        if ( !empty( $msg ) ) {
            echo "<div style='color: red'>" . $msg . "</div><br />";
        }
        ?>
        <div>
            <form method="post" action="school_settings.php">
                <?php
                if ( count( $schools ) > 1 ) {
                    echo "<select name='school'><option value='0'>Choose School</option>";
                    foreach ( $schools as $id => $name ) {
                        echo "<option value='" . $id . "'>" . $name . "</option>";
                    }
                    echo "</select><br /><br />";
                } else {
                    echo "<input type='hidden' name='school' value='" . key( $schools ) . "' />";
                }
                ?>
                <input type="radio" name="shipping-fee" class='shipping-fee' value="-1" /> We would like our base to be removed from the list of schools offering Neshek candlesticks on the parents accounts.<br />
                <br />
                <input type="radio" name="shipping-fee" class='shipping-fee' value="0" /> We will pick up our Neshek Mivtzoim Ammunition.<br /><br />
                <?php
                foreach ( $fee_info as $item => $info ) {
                    $item_id = $info['id'];
                    $item_cost = $info['fee'];
                    echo "The chayolim in our base will be ordering at least:<br />";

                    foreach ( $info['shipping'] as $minimum => $charge ) {
                        echo "<input type='radio' name='" . strtolower( $item ) . "' value='" . $charge . "' class='" . strtolower( $item ) . "' /> ";
                        echo $minimum . " " . $item . " sets.<br />";
                    }
                    echo "<br />";
                }
                ?>
                <input type="submit" value="submit" name="submit" id="submit" />
            </form>
        </div>
    </body>
    <script>
        $(function() {
            $(".shipping-fee").click( function() {
                $("input").not(".shipping-fee").attr('disabled', true);
                $("input#submit").attr('disabled', false);
            });
        });
    </script>
</html>