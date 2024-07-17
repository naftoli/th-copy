<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimSetting.php';

$year = GlobalSettings::getRegistrationYear();
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

// fee info
$fee_info = [
    'Menorah'  =>  [
        'id'    =>  2,
        'fee'   =>  2.5,
        'shipping'  => [
            50  =>  0.50
        ]
    ],
    'Brochure' =>  [
        'id'    =>  3,
        'fee'   =>  0.25,
        'shipping'  =>  [
            50  =>  0.16, 
            100 =>  0.11,
            200 =>  0.07,
            300 =>  0.06,
            400 =>  0.05
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
        if ( isset( $_POST['menorah'] ) && isset( $_POST['brochure'] ) ) {
            $fees = [
                2   =>  $_POST['menorah'], 
                3   =>  $_POST['brochure']
            ];
        } else if ( isset( $_POST['shipping-fee'] ) ) {
            $fees = [
                2   =>  $_POST['shipping-fee'], 
                3   =>  $_POST['shipping-fee']
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
            $msg = "You must choose a value for both Menorahs and Brochures.";
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
        <title>Mivtza Chanuka Settings</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mivtza Chanuka Settings</h1>
        <div class='infobox'>
        <strong>Menorah Box and Chanukah brochures price settings.</strong>
<br /><br /> 
Based on your selection will depend on how much your children will pay for their Chanukah Mivtzoim Ammunition.
<br /><br />
Each Menorah box costs $2.50
<br /><br />
Each Brochure costs $0.25
<br /><br />
If you chose PICK UP, your children will pay this price and will not be charged anything extra for shipping.
<br /><br />
<strong>Shipping charges for Menorah Boxes.</strong><br /><br />
Each Menorah Box costs $0.50 to ship. (Total $3.00).
<br /><br />
<strong>Shipping charges for Chanukah brochures.</strong><br /><br />
The price of shipping for  brochures depends on how many you guarentee will be ordered by the  chayolim in your base 
<br /><br />
50 - 99 brochures cost $0.16 per brochure to ship. (Total $0.36 per brochure).
<br /><br />
100 - 199 brochures cost $0.11 per brochure to ship  (Total $0.31 per brochure).
<br /><br />
200 - 299 brochures cost $0.07 per brochure to ship  (Total $0.27 per brochure).
<br /><br />
300 - 399 brochures cost $0.06 per brochure to ship  (Total $0.26 per brochure).
<br /><br />
400 + brochures cost $0.05 per brochure to ship  (Total $0.25 per brochure).
<br /><br />
<strong>Important notice:</strong><br /><br />
There is a minimum order of 50 brochures and or menorahs that a shipping schools must take. 
<br /><br />          
        </div>
        <br />
        <?php 
        if ( !empty( $msg ) ) {
            echo "<div style='color: red'>" . $msg . "</div><br />";
        }
        ?>
        <div>
            <form method="post" action="chanuka_settings.php">
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
                <input type="radio" name="shipping-fee" class='shipping-fee' value="-1" /> We would like our base to  be removed from the list of schools offering Menorah Boxes and brochures on the parents accounts.<br />
                <br />
                <input type="radio" name="shipping-fee" class='shipping-fee' value="0" /> We will pick up our Chanukah Mivtzoim Ammunition.<br /><br />
                <?php
                foreach ( $fee_info as $item => $info ) {
                    $item_id = $info['id'];
                    $item_cost = $info['fee'];
                    echo "The chayolim in our base will be ordering at least:<br />";

                    $num = count( $info['shipping'] ); // use to find out when we are at the last amount
                    $i = 1;
                    foreach ( $info['shipping'] as $minimum => $charge ) {
                        echo "<input type='radio' name='" . strtolower( $item ) . "' value='" . $charge . "' class='" . strtolower( $item ) . "' /> ";
                        echo $minimum . " " . $item . "s.<br />";
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