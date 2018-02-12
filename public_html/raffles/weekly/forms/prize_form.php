<?php
/***************** DEBUGGING **********************/
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}
// import the required files
require_once(dirname(__FILE__).'/../../shared/classes/Raffle.php');
require_once(dirname(__FILE__).'/../../shared/classes/Prize.php');

use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\weekly\Prize as Prize; // use the prize from its namespace

require_once(dirname(__FILE__).'/../../shared/functions.php');

/*********** HANDLE ACTION **********************/
if (!isset($_GET['action']) && !isset($_POST['action'])){ // if there is no action
    $action = "list"; // default action
} else { // action was provided
    $action = ($_POST['action'] ? $_POST['action'] : $_GET['action']); // prefer the post action
}
/*********** DEBUGGING **********************/
if($debug) echo "<pre>"; // if this is in debug mode, preformmat this whole section
if($debug) print_r($_POST);

// some variables
$valid = true; // will be changed if anything is invalid
$error = "";
$prize_props = [];
/*********** Validation **********************/
if($action == "create" || $action == "update"){ // for both create and update, run the validations
    // validate that requried elements where set
    if($_POST['name'] != ""){
        $name = $_POST['name'];
    } else {
        $error .= "'Name' cannot be blank";
        $valid = false;
    }

    $type_of_prize = "weekly";
    
    // validate the image is good
    switch($_FILES['picture']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $error .= T_('File is too large.');
            $valid = false;
        break;
        case UPLOAD_ERR_PARTIAL:
            $error .= T_('File was only partially uploaded.');
            $valid = false;
        break;
    }
}
/*********** CREATION **********************/
if ($action == "create") {
    // make sure that the file was uploaded
    if ($_FILES['picture'] == UPLOAD_ERR_NO_FILE) {
        $error .= T_("You must upload a picture");
        $valid = false;
    }
    
    if($valid && Prize::create($name, $type_of_prize, $_FILES['picture'])){
        if($debug) $action = "list";
        if(!$debug) header("Location: ".htmlspecialchars($_SERVER["PHP_SELF"])); // redirect to prevent page refreshes from re-submitting the page
    } else {
        $error .= "Could not create Prize.";
        $action = 'add'; // render the add page if it is not a valid request
    }
}
/*********** UPDATING **********************/
if ($action == "update"){
    if ($valid){
        $prize = Prize::load($_POST['prize_id']);
        $prize->name = $name;
        
        if ($_FILES['picture']['error'] == UPLOAD_ERR_OK) { // if the user uploaded a file
            if(!$prize->add_image($_FILES['picture'])){
                $valid = false;
                $error .= "Could not update Picture. Please check the image and try again";
                $action = "edit"; // if it does not update go back to the edit page
            };
        }
                    
        if($valid && !$prize->update()){// make sure that the image was updated if passed in and that it was updated
            $error .= "Could not update Prize.";
            $action = "edit"; // if it does not update go back to the edit page
        } else {
           $action = "list";
        }
    }
}
/*********** DELETION **********************/
if ($action == "destroy"){
    $prize = Prize::load($_GET['prize_id']); // load prize
    if($debug) print_r($prize);
    if($debug) echo "\n";
    if(!$prize || !$prize->destroy()){ // if we do not have the prize or some error happens when trying to destroy
        $error = "Could not delete Prize"; // set the error message
    }
    $action = 'list'; // show all the prizes
}
/*********** EDITING **********************/
if ($action == "edit"){
    if(!$prize) $prize = Prize::load($_GET['prize_id']); // if the prize has not loaded
    if(!$prize) $action='add'; // redirect to add if the prize does not exist
}
/*********** DEBUGGING **********************/
if($debug) print_r($action); // show the final raffle
if($debug) print_r($prize); // show the final raffle
    
/*********** LISTING **********************/
if($action == "list"){ // default action
    $prizes = Prize::loadAll();
}
/*********** DEBUGGING **********************/
if($debug) echo "</pre>"; // end debugging preformatting

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Raffle Prize Form</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/form_style.css?v=1.1" rel="stylesheet" type="text/css">
    </HEAD>
    <BODY>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1><? // determine the text at the top of the page
            if($action == "add"){ // if the action is add
                echo "Create New Prize"; // show Create New
            } else { // it is not add
                echo $action == "edit" ? "View/Edit Prize" : "View Prizes List"; // otherwise show View/Edit or View
            }?>
        </h1>
        <? if ($action == "add" || $action == "edit") {
            if($error) echo "<div class='error'>$error</div>"; // show error messages
        }?>
        <? if ($action == "add"){ // *************************** ADD ACTION PAGE **********************// ?>
            <form method="POST" enctype="multipart/form-data" action="<? echo htmlspecialchars($_SERVER["PHP_SELF"]); echo $debug ? "?debug=true" : "";?>">
                <input name="action" value="create" type="hidden"/>
                <div class="input_group input_half">
                     <label>Name: <input type="text" name="name" required/></label>
                </div>
                <div class="input_group input_half">
                    <label> Picture: <input type="file" name='picture' required /> </label>
                </div>
                <div class="action-links">
                    <input class="button" type="submit" value="Create"/>
                    <a href="<? echo htmlspecialchars($_SERVER["PHP_SELF"]); echo $debug ? "?debug=true" : "";?>" class="button">Cancel</a>
                </div>
            </form>
        <?} else if ($action == "edit"){ // *************************** EDIT ACTION PAGE **********************//?>
            <h2>Edit Prize</h2>
            <form method="POST" enctype="multipart/form-data" action="<? echo htmlspecialchars($_SERVER["PHP_SELF"]); echo $debug ? "?debug=true" : "";?>">
                <input name="action" value="update" type="hidden"/>
                <input name="prize_id" value="<?=$prize->prize_id?>" type="hidden"/>
                <div class='input_box'>
                    <div class="input_group input_full">
                        <label>Name*: <input type="text" name="name" value="<?=$prize->name?>" required/></label>
                    </div>
                    <div class="input_group input_full">
                        Picture: <input type="file" name='picture'/>
                    </div>
                </div>
                <div class="picture_box">
                    <h2>Exisiting picture</h2>
                    <img src="<?=$prize->picture?>" alt="picture of prize" height="125px"/>
                </div>
                <div class="action-links">
                    <input class="button" type="submit" value="Save"/>
                    <a href="<? echo htmlspecialchars($_SERVER["PHP_SELF"]); echo $debug ? "?debug=true" : "";?>" class="button">Cancel</a>
                </div>
            </form>
            <? $prize->get_raffles(); // load the prizes
            /*********** DEBUGGING **********************/
            //if($debug){echo "<pre>";print_r($prize->raffles);echo "</pre>";} // in debug mode show the prize objects
            /*********** GET ALL THE RAFFLES **********************/
            $raffles = Raffle::loadAll("where type='".$prize->type_of_prize."'"); // only load prizes of this type ?>
            
            <h2>Raffles</h2>
            <p>
                <strong>Please check off the raffles you would like this prize to be included in.</strong><br/>
                (Note that a specific raffle might have already hit it's 100 prize limit with another prize)
            </p>
            
            <table id="raffles">
                <thead>
                    <tr><th>Raffle</th><th>Runs On</th><th>Included</th><th>Quantity</th></tr>
                </thead>
                
                <tbody>
                <?foreach($raffles as $raffle){ // render each prize option
                    $raffle = $prize->raffles[$raffle->raffle_id] ? $prize->raffles[$raffle->raffle_id] : $raffle;?>
                    <tr>
                        <td><?=$raffle->name;?></td>
                        <td><?=$raffle->run_date->format("m/d/Y");?></td>
                        <td>
                            <input type="checkbox" id="raffle_<?=$raffle->raffle_id?>" <?= $prize->raffles[$raffle->raffle_id] ? "checked ": ""; ?>/>
                        </td> 
                        <td>
                            <input type="number" disabled
                                id="qty-raffle_<?=$raffle->raffle_id?>" value="<?=$prize->raffles[$raffle->raffle_id]->qty ? $prize->raffles[$raffle->raffle_id]->qty : 0;?>"
                            />
                        </td>
                    </tr>
                <?}?>
                </tbody>
            </table>
            <script src="/raffles/shared/js/raffle_prize_form.js"></script>
        <?} else { // show the list ?>
        <?if($error) echo "<div class='error'>$error</div>"; // show error messages?>
        <div class="action-links">
            <a href="../../shared/forms/<?=$debug ? "?debug=true" : ""?>" class="button">
                <img src="/images/icon_back.png" height="12" alt="tickets"/>
                <span class="link-text">Go Back</span>
            </a>
            <a href="prize_form.php?action=add<?=$debug ? "&debug=true" : ""; // include debug?>" class="button">
                <img src="/images/icon_add.png" height="12" alt="tickets"/>
                <span class="link-text">Create New Prize</span>
            </a>
        </div>
        <h2>Prizes</h2>
        <div id="prizes"></div>
        <script>
            var debug_mode = <?=$debug ? "true" : "false"?>;
            $(document).ready(function(){
                $.post("/raffles/shared/ajax/table_prize.php" + (debug_mode ? "?debug=true" : ""), {type: "weekly"}, function(data){
                    $("#prizes").html(data);
                });
            });
        </script>
        <?} // end show list?>
    </BODY>
</HTML>