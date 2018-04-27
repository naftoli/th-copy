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
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css">
        <style>
            .options { text-align: center; margin-bottom: 5px;}
            a.button { display: inline-block; }
            table { width: 100%; }
            td, th { padding: 4px 8px; font-size: 14px; border: 1px solid #aaa; word-wrap: break-word; max-width: 170px; }
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Charidy Callers</h1>
        <p>
            To print the callers papers: Please press the "Print Caller Papers" button below.
        </p>
        <p>
            <strong>To assign callers: </strong><br/>
            1. Please select the donors you wish to assign by checking the checkbox next to their name.<br/>
            2. Then select the caller you wish to assign to them via the dropdown.<br/>
            3. Press the "Assign Caller" button and wait for the confirmation prompt.<br/>
        </p>

        <div class="options">
            <a class="button" href="caller_letters.php" target="_blank">
                Print Caller Papers
            </a>
        </div>
        <hr style="display: block">
        <div class="options">
            <select id="caller_id">
            <?php
                foreach( $callers as $caller ) { ?>
                    <option value="<?= $caller->charidy_caller_id ?>">
                        <?= $caller->fullName(); ?>
                    </option>
                <? } ?>
            </select>
            <a class="button" id="assign">
                Assign Caller
            </a>
        </div>

        <div class="assign_callers">
            <table>
                <thead>
                    <tr>
                        <th></th><th>Name</th><th>Address</th><th>Zip</th><th>Country</th>
                        <th>Phone</th><th>E-mail</th><th>Donations</th><th>Caller</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach( $donors as $donor ) { 
                    $donor->getDonated(); ?>
                    <tr>
                        <td>
                            <input class="donor-select" type="checkbox" data-donor_id="<?= $donor->donor_id ?>"/>
                        </td>
                        <td><?= $donor->fullName(); ?></td>
                        <td><?= $donor->address; ?></td>
                        <td><?= $donor->zip; ?></td>
                        <td><?= $donor->country; ?></td>
                        <td><?= $donor->phoneNumber(); ?></td>
                        <td><?= $donor->email; ?></td>
                        <td><?= implode( ", ", array_keys( $donor->donations ) ); ?></td>
                        <td class="caller"><?= $donor->getCaller( $year ) ? $donor->caller->fullName() : "N/A"; ?></td>
                    </tr>
                <? } ?>
                </tbody>
            </table>
        </div>
        <script>
            $("#assign").click( function() {

                var caller_id = $("#caller_id").val();
                var caller_name = $("option[value='"+ caller_id + "']").text().trim();

                var donor_checkboxes = $("input.donor-select:checked");
                var donors = [];

                $.each( donor_checkboxes, function( index, input ) {
                    donors.push( input.dataset.donor_id );
                });

                var postData = { 
                    caller_id: caller_id,
                    donor_ids: donors
                }
                
                $.post( "ajax/assignCaller.php", postData, function( response ){
                    $.each( donor_checkboxes, function( index, input ) {
                        input.checked = false;
                        $( input ).parent().parent().find(".caller").text( caller_name );
                    });
                });

            });
        </script>
    </body>
</html>