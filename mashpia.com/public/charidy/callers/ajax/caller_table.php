<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
if ( $admin_user['auth'] !== "super" ){
    echo "Invalid Account Permissions. HQ account only"; die();
}
// load the current year
require_once(dirname(__FILE__).'/../../../class.globalSettings.php');
$year = GlobalSettings::getCurrentYear(); 
// load the classes
require_once( dirname(__FILE__) . "/../classes/Donor.php" );

$donors_sql = 
     " SELECT mashpia_charidy.donors.* FROM mashpia_charidy.donors "
    ." LEFT JOIN charidy_donors_callers cdc ON cdc.donor_id = mashpia_charidy.donors.donor_id AND year = $year "
    ." WHERE ( needs_call = 1 OR parent_admin_id IN ( "
    ." SELECT admin_id FROM th_chidon JOIN users USING (user_id) JOIN admin_auths ON auth='user' "
    ." AND id = user_id WHERE date_paid IS NOT NULL "
    ." )) ";
if ( isset($_POST['caller_id']) ){
    if ( $_POST['caller_id'] == "-1" )
        $donors_sql .= " AND charidy_caller_id IS NULL";
    else if ( intval( $_POST['caller_id'] ) > 0 )
        $donors_sql .= " AND charidy_caller_id = '" . mysql_real_escape_string( $_POST['caller_id'] ) . "' ";
}
$donors_sql .= " ORDER BY first_name, last_name;";

$donors = [];
$donors_query = mysql_query( $donors_sql );
while ( $row = mysql_fetch_assoc( $donors_query ) ){
    $donors[] = Donor::LoadFromRow( $row );
}
?>
<div class="assign_callers">
    <table class="table table-striped table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th></th><th>Name</th><th>Address</th><th>Zip</th><th>Country</th>
                <th>Phone</th><th>New Phone</th><th>E-mail</th><th>5776</th><th>5777</th><th>5778</th>
                <th>Shabbaton</th><th>Caller</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach( $donors as $donor ) { 
            $donor->getDonated(); ?>
            <tr>
                <td>
                    <label class="fancy-check-container">
                        <input class="donor-select" type="checkbox" data-donor_id="<?= $donor->donor_id ?>"/>
                        <span class="fancy-check"></span>
                    </label>
                </td>
                <td><?= $donor->fullName(); ?></td>
                <td><?= $donor->address; ?></td>
                <td><?= $donor->zip; ?></td>
                <td><?= $donor->country; ?></td>
                <td><?= $donor->phoneNumber(); ?></td>
                <td><?= $donor->mashpiaPhoneNumber(); ?></td>
                <td><?= $donor->email; ?></td>
                <td>$<?= isset( $donor->donations['5776'] ) ? $donor->donations['5776']['amount'] : 0 ?></td>
                <td>$<?= isset( $donor->donations['5777'] ) ? $donor->donations['5777']['amount'] : 0 ?></td>
                <td>$<?= isset( $donor->donations['5778'] ) ? $donor->donations['5778']['amount'] : 0 ?></td>
                <td>
                <?php
                    foreach( $donor->onShabbaton( $year ) as $child ){
                        echo $child['first'] . "<br/>";
                    }
                ?>
                </td>
                <td class="caller" id="donor-caller-<?= $donor->donor_id ?>">
                    <?= $donor->getCaller( $year ) ? $donor->caller->fullName() : "N/A"; ?>
                </td>
            </tr>
        <? } ?>
        </tbody>
    </table>
</div>