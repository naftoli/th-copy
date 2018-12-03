<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once( __DIR__ .'/../../header.php');

if ($admin_user['auth'] != 'super') {
   header("Location: /admin.php");
}

require_once( __DIR__ .'/../../api/header/db.php' );
require_once( __DIR__ .'/../../class.globalSettings.php' );
$year = GlobalSettings::getRegistrationYear();
// get all chayolei schools
$schools = \School::find_all_by_chayolei_and_test_school(
    1, 0, ['order' => 'school_name']
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>School Registration Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
    <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
        div#wrapper { width: 1201px; }
        #content .col_content { padding: 20px 10px; }
        #content .slider, #content { width: 950px; }
        table { width: 100%; }
        th, td { padding: 6px 8px; font-size: 14px; max-width: 130px; border-bottom: 1px solid #3e3e3e;}
        td.saved { padding-right: 0px; }
        td:last-child { padding: 4px 0px; }
        input[type="date"] { width: 135px; background: none; border: none; border-bottom: 1px solid; }
        input[type="date"]:disabled { color: #888; }
        input[type="number"] { width: 100%; border: none; border-bottom: 1px solid; background: none; }
        .info { padding: 10px; background: #ccc; margin-bottom: 15px; }
        .info ol, .info ul { list-style: decimal; margin-left: 15px; padding: 5px; }
        .info ul { list-style: disc }
        button { transition: .25s; }
        button:focus, button:hover, select:focus { transform: scale( 1.1 ) }
    </style>
</head>
<body>
    <?php // load the admin UI and JQuery 1.4
        include( __DIR__ .'/../../admin_header.php');
    ?>
    <h1>School Registration Settings</h1>

    <div class="info">
        <h3>School Registration Settings</h3>
        <br/>
        <h4>Settings Explained:</h4>
        <br/>
        <p>
            <strong>Year:</strong> Current Chayolei Registration year for this base. (Austrailian/South African schools may be different)
        </p>

        <strong>Registration Type:</strong>
        <ol>
            <li><strong>In Tuition:</strong> The base charges parents for registration in Tuition.</li>
            <li><strong>Guaranteed:</strong> 
                Parents are given an additional $<?=GlobalSettings::getGuaranteedDiscount()?> discount as base guarantees all parents will register. 
                If some parents do not register the base is charged for the discount given to all soldiers who registered.</li>
            <li><strong>By Parent:</strong> Parents pay for registration as they wish.</li>
        </ol>
        <br/>

        <p><strong>Chayolei Fee:</strong> The fee the base will pay to register.</p>

        <p><strong>Balance:</strong> The balance the base owes to Tzivos Hashem.</p>

        <strong>Soldier Chayolei Fee:</strong> The registration fee for soldiers in this base.
        <ul>
            <li>Please note that the early bird discount ($<?=GlobalSettings::getEarlyBird()?>) is applied to this amount.</li>
            <li>For example, $55 soldier fee - $5 early bird is $50 for registration.</li>
            <li><em>Set to / leave as <strong>Blank</strong> for default rates.</em></li>
        </ul>
        <br/>

        <p><strong>Early Bird:</strong> The date on which the early bird ends for the base.<br/>
            For "guaranteed" bases this is also the deadline to have all children register.
        </p>
        <br/>

        <strong>Status:</strong> The current status of the base
        <ol>
            <li><strong>Deactivate (button):</strong> Revoke base commanders access to their base untill they pay for registration.</li>
            <li><strong>Inactive:</strong> Base commanders are locked out of this base untill registration is paid.</li>
            <li><strong>Paid:</strong> the base has gone through registration and paid.</li>
        </ol>
    </div>
    <div id="report">
        <table>
            <thead>
                <th>Year</th>
                <th colspan='2'>Base</th>
                <th>Registration Type</th>
                <th>Chayolei Fee</th>
                <th>Balance</th>
                <th>Soldier Chayolei Fee</th>
                <th>Early Bird</th>
                <th>Status</th>
                <th>Save</th>
            <thead>
            <tbody>
            <?php foreach( $schools as $base ) { 
                $year = GlobalSettings::getRegistrationYear( $base->school_id ); ?>
                <tr class='base' data-school_id='<?= $base->school_id; ?>' data-year='<?= $year ?>'>
                    <td><?= $year ?></td>
                    <td><?= $base->school_number ?></td>
                    <td><?= $base->name ?></td>
                    <td>
                        <select name="reg_type">
                            <option value="0" <?= !$base->reg_type ? 'selected' : ''; ?> disabled>N/A</option>
                            <option value="1" <?= $base->reg_type == '1' ? 'selected' : ''; ?>>In Tuiton</option>
                            <option value="2" <?= $base->reg_type == '2' ? 'selected' : ''; ?>>Guaranteed</option>
                            <option value="3" <?= $base->reg_type == '3' ? 'selected' : ''; ?>>By Parent</option>
                        </select>
                    </td>
                    <td>
                        <input type='number' name='chayolei_fee'
                            value='<?= $base->chayolei_fee ?>' />
                    </td>
                    <td>
                        <input type='number' name='balance'
                            value='<?= $base->balance ?>' />
                    </td>
                    <td>
                        <input type='number' name='child_fee'
                            value='<?= $base->child_fee ?>' />
                    </td>
                    <td>
                        <input type='date' name='early_bird'
                            value='<?= $base->earlyBird()->format('Y-m-d') ?>' />
                    </td>
                    <td>
                    <?php
                        if ( $base->registration( $year ) ) {
                            echo 'Paid';
                        } else if ( $base->school_era ) {
                            echo 'Inactive';
                        } else { ?>
                            <button class='deactivate'>Deactivate</button>
                    <?php } ?>
                    </td>
                    <td>
                        <button class='save' disabled>Save Changes</button>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
    <script src="js/registration_info.js"></script>
</body>
</html>