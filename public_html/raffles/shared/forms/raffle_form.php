<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/../classes/Prize.php');
require_once(dirname(__FILE__).'/../functions.php');

use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\weekly\Prize as Prize; // use the prizes for the prize form under the edit action

/*********** HANDLE ACTION **********************/
if (!isset($_GET['action']) && !isset($_POST['action'])){ // if there is no action
    $action = "list"; // default action
} else { // action was provided
    $action = ($_POST['action'] ? $_POST['action'] : $_GET['action']); // prefer the post action
}
/*********** DEBUGGING **********************/
if($debug) echo "<pre>"; // if this is in debug mode, preformmat this whole section
if($debug) print_r($_POST);

/*********** VARIABLES **********************/
// some variables
$valid = true; // will be changed if anything is invalid
$error = "";
$raffle_props = []; // properties to create the raffle with

/********************* PERFORM VALIDATIONS **********************/
if($action == "create" || $action == "update"){ // for both create and update, run the validations
    // make sure that the weeks where posted
    /********************* VALIDATE DATES **********************/
    if ($_POST['type'] == "weekly" ) { // weekly raffles
        $raffle_props['type'] = 'weekly'; // set the type
        $raffle_props['start_date'] = $_POST['week_start']; // get the start date
        $raffle_props['end_date'] = $_POST['week_start'] + 6; // and add 6 to get to the end of the week
        $raffle_props['run_date'] = new DateTime(formatJdToDate($raffle_props['end_date'] + 6) . " 11:59 PM"); // cast to datetime at 11:59 pm. Add 6 to
    } else if ($_POST['type'] == "monthly") { // monthly raffles
        $raffle_props['type'] = 'monthly'; // set the type
        $raffle_props['start_date'] = $_POST['start_date'];
        $raffle_props['end_date'] = $_POST['end_date'];
        $raffle_props['run_date'] = new DateTime(formatJdToDate($raffle_props['end_date'] + 6) . " 11:59 PM"); // cast to datetime at 11:59 pm
    } else {
        $error .= "Type is invalid<br/>";
        $valid = false;
    }
    /********************* VALIDATE RUN DATE IS ON WENDSDAY **********************/
    if($raffle_props['run_date']->format('N') != 3) { // if the run date is not wendsday
        $error .= "Run Date must be on a Wendsday<br/>";
        $valid = false;
    }
    /********************* VALIDATE RUN DATE IS ON WENDSDAY **********************/
    
    // preform validations on name and type
    if($_POST['name'] == ""){
        $error .= "Raffle Name cannot be blank<br/>";
        $valid = false;
    } else {
        $raffle_props['name'] = htmlspecialchars($_POST['name']); // escape html chars in name
    }
}

/********************* HANDLE CREATION **********************/
if ( $action == "create" && !$valid ){ // if it is set to create and is not valid
    $action = 'add'; // redirect to the add page
} else if ( $action == "create" ){
    if(Raffle::create($raffle_props)){ // create the raffle
        if($debug) $action = "list";
        if(!$debug) header("Location: ".htmlspecialchars($_SERVER["PHP_SELF"])); // redirect to prevent page refreshes from re-submitting the page
    } else {
        $error .= "Error Creating Raffle";
        $action = "add";
    };
}

/********************* HANDLE UPDATE **********************/
if ($action == "update" && !$valid){
    $action = 'edit'; // redirect to the add page
} else if ($action == "update"){
    $raffle = Raffle::load($_POST['raffle_id']); // load the raffle
    
    // update the propertys
    $raffle->name = $raffle_props['name'];
    $raffle->type = $_POST['type'];
    $raffle->run_date = $raffle_props['run_date'];
    $raffle->start_date = $raffle_props['start_date'];
    $raffle->end_date = $raffle_props['end_date'];
    //save it to the database
    if($raffle->update()){  
        $action = 'list'; // render the list page
    } else { // if it fails
        $error .= "Error Updating Raffle";
        $action = 'edit'; // re render the edit page
    };
}

if ($action == "destroy"){
    $raffle = Raffle::load($_POST['raffle_id']);
    $raffle->destroy(); // destroy the raffle
}

if ($action == "edit" && !$raffle){ // if there is no raffle and we are in edit mode
    $raffle_id = $_POST['raffle_id'] ? $_POST['raffle_id'] : $_GET['raffle_id']; // get the raffle_id from post if we can. if not from get
    $raffle = Raffle::load($raffle_id); // load the raffle
}

if($action == "list"){ // default action
    // $raffles = Raffle::loadAll();  // <- loaded via ajax now
}

if($debug) print_r($raffle); // in debug mode load the raffle

if($debug) echo "</pre>"; // end debugging preformatting

?>
<!DOCTYPE html">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Raffles</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/form_style.css?v=1.1" rel="stylesheet" type="text/css">
        <link href="/styles/admin/grey_select.css?v=1.1" rel="stylesheet" type="text/css">
        <style>.action-links{margin-bottom: 15px}tr td:first-child{white-space: normal}</style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // load the basic UI ?>
        <h1><?// for some reason doing two turnary operators does not work...
            if($action == "add"){echo "Create New Raffle"; } else { echo $action == "edit" ? "View/Edit Raffle" : "View Raffles"; }; // otherwise show Edit or View
        ?></h1>
        <?if($error) echo "<div class='error'>$error</div>"; // display errors ?>
        <?if ($action == "add"){ // ********************* ADDING FORM **********************?>
            <form method="POST" action="<? echo htmlspecialchars($_SERVER["PHP_SELF"]); echo $debug ? "?debug=true" : ""; // remove params from url and add debug to perssist debug mode?>">
                <input name="action" value="create" type="hidden"/>
                <div class="input_group input_half">
                    <label>Name*: <input type="text" name="name" value="<?=$raffle->name?>" required/></label>
                </div>
                <div class="input_group input_half">
                    Type*:
                    <select name="type">
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div id="detailed_inputs">
                    <script src="/raffles/shared/js/load_form.js"></script>
                </div>
            </form>
        <?} else if ($action == "edit") { //********************************* EDIT FORM ************************//?>
        <form method="POST" action="<? echo htmlspecialchars($_SERVER["PHP_SELF"]); echo $debug ? "?debug=true" : ""; // remove params from url and add debug to perssist debug mode?>">
            <h2>Edit Raffle</h2>
            <input name="action" value="update" type="hidden"/>
            <input name="raffle_id" value="<?=$raffle->raffle_id?>" type="hidden"/>
            <div class="input_group input_half">
                <label>Name*: <input type="text" name="name" value="<?=$raffle->name?>" required/></label>
            </div>
            <div class="input_group input_half">
                <h3>
                    Type: <?=ucfirst($raffle->type)?><br/>
                    Run On: <?=$raffle->run_date->format("m/d/Y")?>
                </h3>
            </div>
            <div id="detailed_inputs">
                <?include_once(dirname(__FILE__)."/../../$raffle->type/forms/raffle_form.php");?>
            </div>
        </form>
        
        <h2>Set Prizes</h2>
        
        <div id="prizes">
            <?if ($raffle->type == "monthly" && $raffle->raffle_id != 10) {
                include(dirname(__FILE__)."/../../monthly/forms/raffle_prizes_form.php");
            } else {
                include(dirname(__FILE__)."/../../weekly/forms/raffle_prizes_form.php");
            } ?>
        </div>
        
        <script src="/raffles/shared/js/raffle_prize_form.js"></script>
        
        <?} else {//********************************* VIEW LIST ************************//?>
            <div class="action-links">
                <a href="index.php<?=$debug ? "?debug=true" : ""?>" class="button">
                    <img src="/images/icon_back.png" height="12" alt="tickets"/>
                    <span class="link-text">Go Back</span>
                </a>
                <a href="raffle_form.php?action=add<?=$debug ? "&debug=true" : ""?>" class="button">
                    <img src="/images/icon_add.png" height="12" alt="tickets"/>
                    <span class="link-text">Create New Raffle</span>
                </a>
                <select class="grey" id="type" name="type">
                    <option value="" selected>All</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            
            <div id="raffles"></div>
            <script>
                var debug_mode = <?=$debug ? "true" : "false"?>;
                $("select#type").change(function(event){
                    var type = event.target.value;
                    $.post("/raffles/shared/ajax/table_raffle.php" + (debug_mode ? "?debug=true" : ""), {type: type}, function(data){
                        $("#raffles").html(data);
                    });
                });
                $(document).ready(function(){
                    $("select#type").change();
                });
            </script>
        <?}?>
    </body>
</html>


