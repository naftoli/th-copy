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
require_once(dirname(__FILE__).'/../../class.globalSettings.php');
$year = GlobalSettings::getCurrentYear(); 
// load the classes
require_once( dirname(__FILE__) . "/classes/Donor.php" );
require_once( dirname(__FILE__) . "/classes/Caller.php" );

$donors = [];
$donors_query = mysql_query(
    " SELECT charidy_donors.* FROM charidy_donors WHERE needs_call = 1 OR parent_admin_id IN ( "
        ." SELECT admin_id FROM th_chidon JOIN users USING (user_id) JOIN admin_auths ON auth='user' "
        ." AND id = user_id WHERE date_paid IS NOT NULL "
    .") ORDER BY first_name, last_name;"
);
while ( $row = mysql_fetch_assoc( $donors_query ) ){
    $donors[] = Donor::LoadFromRow( $row );
}

$callers = Caller::LoadAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Raffles Home Page</title>
        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/datatables.min.css"/>
        <style>
            body {
                padding: 50px;
            }
            h1, .options, p {
                text-align: center;
            }
            select#caller_id { max-width: 500px; display: inline-block; text-transform: capitalize;}
            .options { margin-bottom: 15px; }
            tbody tr:hover {
                cursor: pointer;
            }
            /* fancy checkboxes */
            label.fancy-check-container {display: inline-block;height: 1em;font-size: 25px;}
            label.fancy-check-container input {display: none;}
            label.fancy-check-container span.fancy-check { 
                display: inline-block; font: normal normal normal 14px/1 Font Awesome\ 5 Free; font-size: inherit;
                text-rendering: auto; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
            }
            /* plain box */
            label.fancy-check-container span.fancy-check:before { content: "\f0c8"; }
            /* box with check */
            label.fancy-check-container input:checked + span.fancy-check:before { content: "\f14a"; }
        </style>
    </head>
    <body>
        <h1>Charidy Caller Papers Form</h1>
        <p>
            <strong>To print the callers papers:</strong><br/>
            Please press the "Print Caller Papers" button below. Please note that they will be grouped by caller with a title page for each one.
        </p>
        <p>
            <strong>To assign callers: </strong><br/>
            1. Please select the donors you wish to assign by checking the checkbox next to their name.<br/>
            2. Then select the caller you wish to assign to them via the dropdown.<br/>
            3. Press the "Assign Caller" button and wait for the caller names to update.<br/>
        </p>

        <div class="options">
            <a class="btn btn-info" href="caller_letters.php" target="_blank">
                Print Caller Papers
            </a>
        </div>
        <hr style="display: block">
        <div class="options">
            <select id="caller_id" class="form-control">
            <?php
                foreach( $callers as $caller ) { ?>
                    <option value="<?= $caller->charidy_caller_id ?>">
                        <?= $caller->fullName(); ?>
                    </option>
                <? } ?>
            </select>
            <a class="btn btn-success" id="assign" href="#">
                Assign Caller
            </a>
        </div>

        <div class="assign_callers">
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th></th><th>Name</th><th>Address</th><th>Zip</th><th>Country</th>
                        <th>Phone</th><th>E-mail</th><th>Donations</th><th>Shabbaton</th><th>Caller</th>
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
                        <td><?= $donor->email; ?></td>
                        <td>
                        <?php
                            foreach( $donor->donations as $donation_year => $donation ){
                                echo $donation_year . " ($" . $donation['amount'] . ")<br/>";
                            }
                        ?>
                        </td>
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

        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.16/datatables.min.js"></script>
        <script type="text/javascript" src="index.js"></script>
    </body>
</html>
