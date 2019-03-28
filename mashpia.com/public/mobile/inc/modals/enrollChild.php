<?/*
   * Expects the following variables from getGoalsPage.php
   *    $lang => 1 for english and 2 for yiddish
   *    $first => the name of the child
   *    $user_id => The user id of the user on the page...
   *
   * Also expects an active mysql database connection....
*/

/**************** GET THE ADMIN ********************/
require_once(dirname(__FILE__)."/../../reg/ajax/encrypt.php");
$admin_id = encrypt_decrypt('decrypt', $_COOKIE['admin']);
//include($_SERVER['DOCUMENT_ROOT']."/classes/admin.php");
//$admin_row = mysql_fetch_assoc(mysql_query("SELECT * FROM admins WHERE admin_id=".$admin_id));
//$admin = new \classes\admin($admin_row);
//$admin->get_markable_children(); // get the children he can mark...

/**************** GET THE CURRENT YEAR ********************/
require_once $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

/**************** GET THE CAMPAIGNS THE USER IS ELIGIBLE FOR ********************/
require_once(dirname(__FILE__)."/../functions/getCampaigns.php");
$campaigns = getCampaigns($user_id, false);

?>
<style>
/*    No Campaign Available...*/
    #no-campaigns{text-align: center;}
    #no-campaigns img{max-width: 150px;}
/*    Campaign rows...*/
    .campaign{padding: 12px;clear: both;min-height: 75px;}
    .campaign h3{margin-top: 0px; font-size: 1.4em;font-weight: 600}
    .campaign div.description {margin-left: 18%;margin-right:15%;}
    .campaign img {max-width: 15%;min-width: 55px; float: left;margin-right: 3%;}
    .campaign button.enroll {float: right;margin-top: 10px; margin-right: 1%; padding: 8px 16px;}
    @media (max-width: 470px){.campaign div.description {margin-left: 70px;margin-right:0px;}}
</style>
<div class="modal fade" role="dialog" id="enrollCampaignModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="img_new/x-color-white-svg.svg"/></button>
                <h4 class="modal-title">Enroll <?=$first?> In <?=$year?> Campaigns</h4>
            </div>
            <div class="modal-body">
                <? if (count($campaigns) == 0) {?>
                <div id="no-campaigns">
                    <img src="//mashpia.com/mobile/img_new/<?//$gender == "M" ? "boy" : "girl"?>boy-color-purple-svg.svg" alt="Default Profile Picture"/>
                    <h1>Your Child Is Already Registered In All Available Campaigns!</h1>
                </div>
                <? } else {
                    foreach($campaigns as $campaign) {?>
                    <div class="campaign">
                        <img src="/file_view.php?id=<?=$campaign['subject_image_id']?>" alt="Medal Badge"/>
                        <button class="enroll" data-subject_id="<?=$campaign['subject_id']?>">Enroll</button>
                        <div class="description">
                            <h3><?=$campaign['subject_name']?></h3>
                            <p><?=$campaign['subject_description'] ? $campaign['subject_description'] : "No Description Available"?></p>
                        </div>
                    </div>
                <?  } // end foreach campaign...
                }// end if they have campaigns to still enroll in... ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" style="background-color: #5e1c77;border-color:#834999;">Cancel</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
//    Hide the english or yiddish labels based on the language...
    $(document).ready(function(){
        $("button.enroll").click(function(event){
            var subject_id = event.target.dataset.subject_id;
            $.post("ajax/enrollChild.php", {subject_id: subject_id, user_id: <?=$user_id?>}, function(data){
                data = JSON.parse(data);
                if (!data.success) {
                    alert("Sorry, it seems that we could not enroll <?= addslashes( $first )?> in this campaign. Please try again later.");
                } else {
                    var campaign = $(event.target).parent();
                    var campaign_name = campaign.find("h3").text();
                    campaign.html("<div class='alert alert-success'>"+
                                  "You have successfully enrolled <?= addslashes( $first ) ?> in " + campaign_name + "!<br/>" +
                                  "Please refresh the page to customize his tasks.</div>");
                    
                }
            });
        });
    });
</script>
