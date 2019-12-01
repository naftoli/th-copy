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
    'Menorah'  =>  [
        'id'    =>  2,
        'fee'   =>  1.7, 
        'shipping'  => [
            50  =>  0.44
        ]
    ],
    'Brochure' =>  [
        'id'    =>  3,
        'fee'   =>  0.2, 
        'shipping'  =>  [
            50  =>  0.34, 
            100 =>  0.29,
            200 =>  0.25,
            300 =>  0.24,
            400 =>  0.23
        ]
    ]
];

$msg = '';
if ( isset( $_POST['submit'] ) ) {
    $school = $_POST['school'];
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

// $schoolList = implode(',', array_keys( $schools ));
// $items = [];
// foreach ( $fee_info as $item => $info ) {
//     $items[] = $info['id'];
// }
// $itemList = implode(',', array_keys( $items ));

// $stmt = $MASHPIA_DB->query("
//     SELECT 
//         school_id, allow_purchases, shipping_charge 
//     FROM
//         mivtzoim_purchases.school_settings
//     WHERE
//         school_id IN ($schoolList) AND item_id in ($itemList) 
// ");
// $rows = $stmt->fetchAll();
// foreach ( $rows as $row ) {
//     $info[$row['school_id']] = $row;
// }
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
            Use the following form to determine what the shipping charges will be for your students.<br /><br />
            If you choose to PICK UP, your children will not be charged anything.<br /><br />
            <?php
            foreach ( $fee_info as $item => $info ) {
                echo "<strong>Shipping Charge for " . $item . "s</strong><br />";
                $num = count( $info['shipping'] ); // use to find out when we are at the last amount
                $i = 1;
                foreach ( $info['shipping'] as $minimum => $charge ) {
                    echo $minimum;
                    if ( $i++ == $num ) {
                        echo "+";
                    }
                    echo " children guaranteed will have a shipping charge of $" . $charge . " per " . $item . "<br />";
                }
                echo "<br />";
            }
            ?>
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
                <input type="radio" name="shipping-fee" class='shipping-fee' value="-1" /> I would like to have my school removed from list of schools offering Menorahs and Brochures.<br />
                <input type="radio" name="shipping-fee" class='shipping-fee' value="0" /> We will PICKUP FROM HEADQUARTERS = NO CHARGE<br /><br />
                <?php
                foreach ( $fee_info as $item => $info ) {
                    $item_id = $info['id'];
                    $item_cost = $info['fee'];
                    echo "Number of children guaranteed for " . $item . "<br />";

                    $num = count( $info['shipping'] ); // use to find out when we are at the last amount
                    $i = 1;
                    foreach ( $info['shipping'] as $minimum => $charge ) {
                        echo "<input type='radio' name='" . strtolower( $item ) . "' value='" . $charge . "' class='" . strtolower( $item ) . "' />";
                        echo $minimum;
                        if ( $i++ == $num ) {
                            echo "+";
                        }
                        echo " children guaranteed = " . $charge . " per " . $item . "<br />";
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