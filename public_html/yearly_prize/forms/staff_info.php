<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    echo "<h2>Debug log:</h2>";
}
if($debug) echo "<pre>";

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** GET SOME BASIC INFORMATION **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

if($debug) echo "</pre>";
// render the page
?><!DOCTYPE html">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Staff Information</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
        <link href="../css/grey_select.css?v=1.1" rel="stylesheet" type="text/css">
        <link href="../css/small_tables.css" rel="stylesheet" type="text/css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>h3 {margin: 2%;border-bottom: 1px solid #aaa;}h4 {font-size: .95em;padding: 1%;}
        a.button{display: inline-block}th{text-align: left}.modal-content h1{margin-bottom: 5px}
        #position_custom, #position_Teacher{visibility: collapse; width: 0px; margin: 0px;}.first_row_scale {transition: .25s;}</style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // load the basic UI ?>
        <h1>Staff Information</h1>
        
        <div id="dropdowns">
            <? if(count($schools) == 1) {?>
                <select id="school_id" class="hidden" name="school_id" disabled>
                    <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
                </select>
            <?} else {?>
                <select id="school_id" name="school_id">
                    <option value="">All Schools</option>
                    <? foreach($schools as $school_id => $school_name){?>
                        <option value="<?=$school_id?>"><?=$school_name?></option>
                    <?}?>
                </select>
            <?}?>
            <a class="button" id="add_staff">
                <i class="fa fa-plus" aria-hidden="true"></i> Add Staff Member
            </a>
            <a class="button" id="load_table">
                <i class="fa fa-refresh" aria-hidden="true"></i> Refresh
            </a>
        </div>
        
        <h2>Staff List</h2>
        
        <div id="staff_info"></div>
        
        <div class="modal" id="view_modal">
            <div class="modal-content">
                <h1>
                    Create/Edit Staff Member
                    <span class="close" id="cancel_modal_x">×</span>
                </h1>
                <h2></h2>
                <form id="modal-form" name="modal-form">
                    <input type="hidden" id="staff_id" />
                    <input type="hidden" id="school_id" />
                    <input type="hidden" id="staff_type" />
                    <div class="input_group input_half first_row_scale">
                        <label>Name<br/><input id="name" type="text" placeholder="First Last" required /></label>
                    </div>
                    <div class="input_group input_half first_row_scale">
                        <label>Position<br/>
                            <select id="type">
                                <option value="" disabled>Select an Option</option>
                                <option value="Dean">Dean</option>
                                <option value="Director">Director</option>
                                <option value="Principal">Principal</option>
                                <option value="Assistant Principal">Assistant Principal</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Base Commander">Base Commander</option>
                                <option value="Office Staff">Office Staff</option>
                                <option value="Secretary">Secretary</option>
                                <option value="Chidon">Chidon</option>
                                <option value="custom">Other</option>
<!--                                Hidden options -->
                                <option value="teacher" hidden disabled>Primary Teacher</option>
                            </select>
                        </label>
                    </div>
                    <div class="input_group input_third first_row_scale custom_input" id="position_custom">
                        <label><br/><input id="position" type="text" placeholder="Enter Position..."/></label>
                    </div>
                    <div class="input_group input_third first_row_scale custom_input" id="position_Teacher">
                        <label><br/><span id="class_select"></span></label>
                    </div>
                    <div class="input_group input_half">
                        <label>Email<br/><input id="email" type="email" placeholder="test@test.com" required/></label>
                    </div>
                    <div class="input_group input_quarter">
                        <label>Cell Number<br/><input id="cell_phone" type="text" placeholder="(555) 555-5555" required maxlength="15"/></label>
                    </div>
                    <div class="input_group input_quarter">
                        <label>Work Number<br/><input id="work_phone" type="text" placeholder="(555) 555-5555 x666" maxlength="45"/></label>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="button" id="save_modal" value="Save"/>
                        <input type="button" class="button" id="cancel_modal" value="Cancel"></input>
                    </div>
                </form>
            </div>
        </div>
        
        <script>var debug = <?=$debug ? "true" : "false"?></script>
        <script src="../js/staff_info.js?v=2.2"></script>
        
    </body>
</html>