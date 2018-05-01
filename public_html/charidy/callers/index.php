<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
if ( $admin_user['auth'] !== "super" ){
    echo "Invalid Account Permissions. HQ account only"; die();
}
// load the classes
require_once( dirname(__FILE__) . "/classes/Caller.php" );

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
        <link rel="stylesheet" type="text/css" href="/styles/admin/loader.css"/>
        <style>
            body {
                padding: 50px;
            }
            h1, .options, p {
                text-align: center;
            }
            .loader {
                border-top: 1.1em solid rgba(128, 128, 128, 0.2);
                border-bottom: 1.1em solid rgba(128, 128, 128, 0.2);
                border-right: 1.1em solid rgba(128, 128, 128, 0.2);
                border-left: 1.1em solid #000;
            }
            select#caller_id, select#print_caller_id { max-width: 500px; display: inline-block; text-transform: capitalize;}
            .options { margin-bottom: 15px; }
            tbody tr:hover {
                cursor: pointer;
            }
            td:nth-child(2), td:last-child {
                text-transform: capitalize;
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
            1. Select the Callers you wish to print (or All Callers)<br/>
            2. Press the "Print Caller Papers" button below.<br/><br/>
            Please note that they will be grouped by caller with a title page for each one.
        </p>
        <p>
            <strong>To assign callers: </strong><br/>
            1. Select the caller you wish to see from the bottom dropdown and press the "Load Caller Table" button below.<br/>
            2. Select the donors you wish to assign by checking the checkbox next to their name.<br/>
            3. Then select the caller you wish to assign to them via the dropdown you used in step 1 (bottom).<br/>
            4. Press the "Assign Caller" button and wait for the caller names to update.<br/>
        </p>

        <div class="options">
            <select id="print_caller_id" class="form-control">
                <option value="">All Callers</option>
            <?php
                foreach( $callers as $caller ) { ?>
                    <option value="<?= $caller->charidy_caller_id ?>">
                        <?= $caller->fullName(); ?>
                    </option>
            <?php } ?>
            </select>
            <a class="btn btn-info" href="caller_letters.php" id="print_caller_letters" target="_blank">
                Print Caller Papers
            </a>
        </div>
        <hr style="display: block">
        <div class="options">
            <select id="caller_id" class="form-control">
                <option value="">All Callers</option>
                <option value="-1">N/A</option>
            <?php
                foreach( $callers as $caller ) { ?>
                    <option value="<?= $caller->charidy_caller_id ?>"><?= $caller->fullName(); ?></option>
                <? } ?>
            </select>
            <a class="btn btn-primary" id="load-table" href="#">
                Load Caller Table
            </a>
            <a class="btn btn-success" id="assign" href="#">
                Assign Caller
            </a>
        </div>

        <div id="donor-table"></div>

        <div class="modal fade" id="invalid-caller" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Caller Error</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        You cannot assign someone to N/A or All Callers at the moment. We apologize for the inconvenice.
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.16/datatables.min.js"></script>
        <script type="text/javascript" src="index.js"></script>
    </body>
</html>
