<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$year = GlobalSettings::getRegistrationYear();

$msg = '';
if ( isset( $_POST['submit'] ) ) {
    $school = $_POST['school'];
    $fee = $_POST['shipping-fee'];
    if ( $school > 0 ) {
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO lulav_settings 
            SET 
                year = :year, 
                school_id = :id,
                allow_lulav = :allow, 
                lulav_shipping = :fee
            ON DUPLICATE KEY UPDATE
                allow_lulav = :allow,
                lulav_shipping = :fee
        ");
        $res = $stmt->execute([
            ':year' =>  $year,
            ':allow'=>  $fee >= 0 ? 1 : 0,
            ':fee'  =>  $fee >= 0 ? $fee : 0,
            ':id'   =>  $school
        ]);
        if ( $res ) {
            $msg = "School setting updated.";
        } else {
            echo $stmt->debugDumpParams();
            $msg = "Error updating setting.";
        }
    } else {
        $msg = "You must choose a school";
    }
}

$schoolList = implode(',', array_keys( $schools ));
$stmt = $MASHPIA_DB->query("
    SELECT 
        s.school_name, ls.*
    FROM
        schools s
    LEFT JOIN 
        lulav_settings ls using (school_id)
    WHERE
        school_id IN ($schoolList)
");
$rows = $stmt->fetchAll();
foreach ( $rows as $row ) {
    $info[$row['school_id']] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mivtzoim Settings</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mivtza Lulav Settings</h1>
        <div class='infobox'>
            Use the following form to determine what the shipping charges will be for your students.<br /><br />
            YOU MUST CHOOSE ONE OF THE OPTIONS IF YOU WANT YOUR CHAYOLIM TO BE ABLE TO PURCHASE A SET!<br /><br />
            If you choose to PICK UP, your children will not be charged anything.<br /><br />
            Otherwise, you will need to choose from the following options:<br /><br />
            1 - 4 children guaranteed = $15 shipping charge per child<br />
            5 - 9 children guaranteed = $10 shipping charge per child<br />
            10 - 14 children guaranteed = $6 shipping charge per child<br />
            15 - 29 children guaranteed = $5 shipping charge per child<br />
            30 - 99 children guaranteed = $4 shipping charge per child<br />
            100+ children guaranteed = $3 shipping charge per child<br /><br />
            You can also totally OPT OUT from allowing your children to purchase lulav sets.
        </div>
        <br />
        <?php 
        if ( !empty( $msg ) ) {
            echo "<div style='color: #ff0000'>" . $msg . "</div><br />";
        }
        ?>
        <div>
            <form method="post" action="lulav_settings.php">
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
                <input type="radio" name="shipping-fee" value="0" /> We will PICKUP FROM HEADQUARTERS = NO charge<br />
                <input type="radio" name="shipping-fee" value="15" /> 1 - 4 children guarenteed = $15<br />
                <input type="radio" name="shipping-fee" value="10" /> 5 - 9 children guaranteed = $10<br />
                <input type="radio" name="shipping-fee" value="6" /> 10 - 14 children guaranteed = $6<br />
                <input type="radio" name="shipping-fee" value="5" /> 15 - 29 children guaranteed = $5<br />
                <input type="radio" name="shipping-fee" value="4" /> 30 - 99 children guaranteed = $4<br />
                <input type="radio" name="shipping-fee" value="3" /> 100+ children guaranteed = $3<br /><br />
                <input type="radio" name="shipping-fee" value="-1" /> I would like to have my school removed from list of schools offering Lulav sets.<br /><br />
                <input type="submit" value="submit" name="submit" />
            </form>
        </div>
    </body>
</html>