<?php // enable debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once( dirname(__FILE__).'/../../../header.php' );
// superusers only
if ( $admin_user['auth'] !== "super" ) {
    echo "Invalid Account Permissions"; die();
}

/***************** LOAD YEARLY RAFFLE CLASS **********************/
require_once( dirname(__FILE__).'/../classes/YearlyRaffle.php' );
use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
$yearly_raffle = new YearlyRaffle();

$realtime = isset($_POST['report_type']) ? $_POST['report_type'] === "realtime" : false;

$users = $yearly_raffle->get_eligible_users( $realtime );

?>
<h2><?= count($users) ?> Eligible Users</h2>
<table>
    <thead>
        <tr>
            <th>School</th><th>Serial #</th><th>Last</th><th>First</th><th>Days Compleated</th>
        </tr>
    </thead>
    <tbody>
        <?php // render a row for each user
        foreach( $users as $user ){ 
            ?>
            <tr>
                <td><?= $user['school_name'] ?></td>
                <td><?= $user['user_serial'] ?></td>
                <td><?= $user['last'] ?></td>
                <td><?= $user['first'] ?></td>
                <td><?= $user['days'] ?>/160 </td>
            </tr>
        <?php
        } 
        ?>
    </tbody>
</table>