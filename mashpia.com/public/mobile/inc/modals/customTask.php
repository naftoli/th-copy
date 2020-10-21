<?/*
   * Expects the following variables from getGoalsPage.php
   *    $lang => 1 for english and 2 for yiddish
   *    $first => the name of the child
   *    $campaigns => array of campaign options as id => campaign
   *
   * Also expects an active mysql database connection....
*/

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/**************** GET THE ADMIN ********************/
require_once(dirname(__FILE__)."/../../reg/ajax/encrypt.php");
$admin_id = encrypt_decrypt('decrypt', $_COOKIE['admin']);
include($_SERVER['DOCUMENT_ROOT']."/classes/admin.php");
$admin_row = mysql_fetch_assoc(mysql_query("SELECT * FROM admins WHERE admin_id=".$admin_id));
$admin = new \classes\admin($admin_row);
$admin->get_markable_children(); // get the children he can mark...

//echo "<pre>";
//print_r($admin);
//echo "</pre>";

require_once $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

//find end date of this year's parshos
$end = mysql_fetch_assoc(mysql_query("SELECT end FROM parshos WHERE year = " . $year . " ORDER BY end DESC LIMIT 1"))['end'];
// get all the parshas from today untill the end of the year
$parsha_query = mysql_query("SELECT * FROM parshos WHERE start >= ".(unixtojd() - 7)." and end <= " . $end);
$parshos = [];
while ($parsha = mysql_fetch_assoc($parsha_query)) {
    $parshos[$parsha['name']] = [
        'id'    => $parsha['id'],
        'start' => $parsha['start'], 
        'end'   => $parsha['end']
    ];
}
// get the labels that the parents can use
$labels_query = mysql_query("select label_id, label_name from labels where label_id >= 30 order by label_name");
$labels = [];
while ($label = mysql_fetch_assoc($labels_query)) {
    $labels[$label['label_id']] = $label['label_name'];
}

?>
<div class="modal fade" role="dialog" id="customTaskModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="img_new/x-color-white-svg.svg"/></button>
                <h4 class="modal-title">Create Custom Task For <?=$year?></h4>
            </div>
            <form id="custom-task-form">
                <div class="modal-body">
                    <div style="text-align: center; font-weight: 700; font-size: 1.5em;margin-bottom: 15px;">Please note that all fields are required.</div>
                    <div class="form-group">
                        <label for="lang" class="control-label">Task Language:</label>
                        <label class="checkbox-label">
                            <input type="radio" name="lang" class="lang" value="1" id="lang_radio_1" <?=$lang == 1 ? 'checked="checked"' : ""?>/>
                            <span class='checkbox-display'></span> English 
                        </label>
                        <label class="checkbox-label">
                            <input type="radio" name="lang" class="lang" value="2" id="lang_radio_2" <?=$lang == 1 ? "" : 'checked="checked"'?>/>
                            <span class='checkbox-display'></span> Yiddish
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="campaign" class="control-label">Task Campaign:</label>
                        <select name='campaign' id='campaign' class="form-control" required>
                            <option disabled value="" selected hidden>Choose Campaign</option>
                            <?foreach ($campaigns as $id => $campaign) {
                                if ( !in_array( $id, [ 1, '1', 40, '40', 94, '94' ] ) )
                                    echo "<option value='$id'>" . $campaign . "</option>";
                            } // end foreach campaign?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shortName" class="control-label">Task Title: (Example: 'Modeh Ani'):</label>
                        <input name="shortName" size="30" id="shortName" type="text" class="form-control" placeholder="Modeh Ani" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Task Details: (Example: 'I did my quota of volunteer hours').</label>
                        <input type='text' name='name' size='80' id='name' class="form-control" placeholder="I did my quota of volunteer hours" required/>
                    </div>
                    
                    <div class="form-group">
                        <label for="label" class="control-label">Task Category:</label>
                        <select name='label' id='label' class="form-control" required>
                            <option  disabled value="" selected hidden>Choose Label</option>
                            <?foreach ($labels as $id => $label) {
                                echo "<option value='$id' class='label_lang_".($id >= 50 ? 2 : 1)."'>" . $label . (in_array($id, [30,31,32,38,50,51,52,58]) ? " (Daily)" : "") . "</option>";
                            } // end foreach campaign?>
                        </select>
                    </div>
                    
                    <fieldset style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                        <legend style="width: auto;border-bottom: 0px;padding: 0px 10px;">Children</legend>
                        <?
                        foreach ($admin->children as $child) {
                            if($child->allow_parent_tasks) { ?>
                                <span class="child lang_<?=$child->lang_id?>">
                                    <label class="checkbox-label">
                                        <input type='checkbox' class='mission' name='children[]' value='<?=$child->user_id?>' />
                                        <span class='checkbox-display'></span>
                                        <strong><?=$child->first?></strong>
                                    </label>
                                </span>
                            <? } // make sure that the child is allowed to create custom tasks....
		                } // end foreach child?>
                    </fieldset>
                    
                    <fieldset style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                        <legend style="width: auto;border-bottom: 0px;padding: 0px 10px;">Parshos (Fri before to Thurs after)</legend>
                        <div align='center'>
                            <input type='button' id='toggleParshos' class="btn btn-danger" style="background-color: #5e1c77;border-color:#834999;" value='Toggle Parshos' />
                            <br/>
                        </div>
                        <?
                        foreach ($parshos as $name => $details) {?>
                            <span class="parsha">
                                <label class="checkbox-label">
                                    <strong><?=$name?></strong>
                                    <input type='checkbox' class='mission' name='parshos[]' value='<?=$details['id']?>' />
                                    <span class='checkbox-display'></span>
                                </label>
                            </span>
                        <?}?>
                        <div style="clear: both;"></div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" id="create-task-bottom" style="background-color: #5e1c77;border-color:#834999;">Create</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" style="background-color: #5e1c77;border-color:#834999;float: left;">Cancel</button>
                </div>
             </form> <!-- Shamai asked for the button to go on the bottom of the page so the form has to be like this :-( its bad I know but I could not find a way to run the HTML validations otherwise... -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
//    Hide the english or yiddish labels based on the language...
    $(document).ready(function(){
        $("button#create-task-bottom").click(function(){
            //debugger;
            //$("#custom-task-form").trigger( "submit" ); // submit the form...
        });
        
        function toggle_yiddish() {
            $.each($('.label_lang_1, .child.lang_1'), function(index, item){$(item).css({"display": "none"});});
            $.each($('.label_lang_2, .child.lang_2'), function(index, item){$(item).css({"display": "inline-block"});});
        }
        function toggle_english() {
            $.each($('.label_lang_2, .child.lang_2'), function(index, item){$(item).css({"display": "none"});});
            $.each($('.label_lang_1, .child.lang_1'), function(index, item){$(item).css({"display": "inline-block"});});
        }
        
        $("#lang_radio_1").click(toggle_english);
        $("#lang_radio_2").click(toggle_yiddish);
        toggle_<?=$lang == 1 ? "english" : 'yiddish'?>();
        
        $(".child .checkbox-display").dblclick(function(event){
            $(event.target).siblings()[0].checked = true; // make sure that this one is checked
            // go to all the other kids...
            $.each($(event.target).parent().parent().siblings().find(".mission"), function(index, item){
                item.checked = false; // and uncheck them...
            });
        });
        
        var toggle = true;
        
        $("#toggleParshos").click(function(event){
            toggle = !toggle;
            $.each($(event.target).parent().parent().find(".parsha input"), function(index, parsha){
                parsha.checked = toggle;
            });
        });
        
        function checkboxReducer(array, checkbox) {
            if (checkbox.checked && $(checkbox).parent().parent().css("display") !== "none") {array.push(checkbox.value);} // make sure the checkbox is checked AND visible to the user...
            return array;
        }
        
        $("#custom-task-form").submit(function(event){
            event.preventDefault();
            var form  = $(this);
            
            var lang_id = form.find("input[name='lang']:checked").val(); // get the selected radio box...
            if (!lang_id){alert ("Please Select a Language"); return false;}
            
            var campaign_id = form.find("#campaign").val();
            if (!campaign_id){alert ("Please Select A Campaign"); return false;}
            
            var shortName = form.find("#shortName").val(); // get the shortname
            if (!shortName){alert ("Please Enter A Task Title"); return false;}
            if (shortName.length > 30) {alert ("Please Enter A Task Title That Is 30 Characters Or Less"); return false;}
            
            var name = form.find("#name").val();
            if (!name){alert ("Please Enter A Task Description"); return false;}
            
            var label_id = form.find("#label").val();
            if (!label_id){alert ("Please Select A Task Category"); return false;}
            if ((label_id >= 50 && lang_id == 1) || (label_id < 50 && lang_id == 2)){
                alert ("Please Select A Task Category In " + (lang_id == 1 ? "English" : "Yiddish")); return false;
            }
            
            var children = form.find(".child .mission").toArray().reduce(checkboxReducer, []);
            if (children.length === 0 ){alert ("Please Select At Least One Child"); return false;}
            
            var parshos = form.find(".parsha .mission").toArray().reduce(checkboxReducer, []);
            if (parshos.length === 0 ){alert ("Please Select At Least One Parsha"); return false;}
            
            var post_data = {lang_id: lang_id, campaign_id: campaign_id, shortName: shortName, name: name, label_id: label_id, children: children, parshos: parshos};
            
            // sho the rotating spinner to show that we are doing some work...
            var opts = {
                lines: 8,length: 26,width: 12,radius: 26,scale: 0.75,corners: 1,color: '#888',
                opacity: 0.25,rotate: 0,direction: 1,speed: 1.1,trail: 60,fps: 20,zIndex: 2e9,className: 'spinner',
                top: '50%',left: '50%',shadow: false,hwaccel: true,position: 'absolute'
            };
            new Spinner(opts).spin(document.getElementById('spinner')); // show the spinner on the page...
            
            $.post("ajax/createParentTask.php", post_data, function(data){
                try {
                    $("#spinner").html(""); // clear the spinner...
                    data = JSON.parse(data); // try to make sense of the gobbly gook from the server...
                    // if we failed then show them the reason and tell them to contact support
                    if (!data.success) {alert("Server Error: " + data.error_message + ". If you belive this is in error, please contact techinical support."); return false;} // show the error to the user...}
                    // we did ok... now lets make this act like an SPA...
                    // we will do this by closing the panel and resetting it's content so that it has to be reloaded when it is opened again (getting the new task, what you thought we would do something fancy and efficient? no time for that)
                    var campaign_panel = $("#"+post_data.campaign_id); // find the div that stores the tasks for this campaign.
                    campaign_panel.find(".tasks").html(""); // clear the tasks in the campaign
                    // close the dropdown and remove the "in" class...
                    campaign_panel.parent().css({"height": "0px"}).removeClass("in"); // set the height to 0px and remove the in class on the .collapse container...
                    campaign_panel.parent().parent().removeClass("open"); // remove the open class from the dropdown so that the arrow resets....
                    
                    $("#customTaskModal .close").click();
                    alert("Congratulations, your custom task has been successfully created!");
                } catch (error) { // ok, we cannot understand this. just tell them to send us an email. that should work.
                    alert("Server Error: Invalid Response. Please contanct support.");
                }
            });
        });
    });
</script>
