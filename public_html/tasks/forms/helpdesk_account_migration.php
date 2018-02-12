<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

$admin_query = mysql_query("SELECT admin_id, first, last, username, password, admin_email FROM admins " // get the id, username, password, and email address...
    ."JOIN admin_auths aa USING (admin_id) WHERE admin_email != '' AND first != '' " // get superusers and BC's With email addresses
    ."GROUP BY admin_email ORDER BY first, last"); // make sure the emails are unique

$admins = [];
while($admin_row = mysql_fetch_assoc($admin_query)){
    if(mswIsValidEmail($admin_row['admin_email'])){ // make sure the emails are valid
        $admins[$admin_row['admin_email']] = $admin_row;
    }
}

$portal_users = [];
$protal_users_query = mysql_query("SELECT * FROM tickets.msp_portal");
while($portal_user_row = mysql_fetch_assoc($protal_users_query)){
    $portal_users[$portal_user_row['email']] = $portal_user_row;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Helpdesk Account Migration</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <style>
            table {width: 100%;}
            td {padding: 4px 8px;max-width: 15%;word-break: break-word;}
            div#content {width: 1080px;}
            div#wrapper {width: 1340px;}
            #content .slider {width: 100%;}
            .action a{display: inline-block;}
            .action {max-width: 50%;text-align: center;display: inline-block;min-width: 29%;}
        </style>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Helpdesk Account Migration</h1>
        
        <h2>Migration Actions</h2>
        
        <div class="action">
            <label>Admin ID:
                <input type="text" id="admin_id" />
            </label>
            <a class="button" id="refresh_admin">Create/Refresh Admin</a>
        </div>
        
        <div class="action">
            <a class="button" id="refresh_all_admins">Refresh All Admin Passwords</a>
        </div>
        
        <div class="action">
            <a class="button" id="create_admins">Create All Non-Present Admins</a>
        </div>
        
        <h2>Migration Status</h2>
        <table>
            <thead>
                <tr>
                    <th>Admin ID</th><th>Name</th><th>Email</th><th>Mashpia Username</th><th>Password</th><th>Helpdesk Account</th>
                </tr>
            </thead>
            <tbody>
                <?foreach ($admins as $email => $admin) {?>
                <tr>
                    <td style="min-width: 60px;"><?=$admin['admin_id']?></td>
                    <td><?=$admin['first'] . " " . $admin['last']?></td>
                    <td><?=$admin['admin_email']?></td>
                    <td><?=$admin['username']?></td>
                    <td><?=$admin['password']?></td>
                    <td><?=isset($portal_users[$email]) ? "Yes" : "No"?></td>
                </tr>
                <?} // end foreach admin ?>
            </tbody>
        </table>
        <script>
            $(document).ready(function(){
                // reusable function for submitting the actions...
                function submit_action(data, reload){
                    $.post("ajax/helpdesk_account_migration.php", data ,function(raw_response){
                        response = $.parseJSON(raw_response); // parse the response...
                        if (!response) {
                            alert(raw_response);
                        } else if (!response.success) { // if it was not good
                            alert(response.error); // alert the error
                        } else if(reload) { // refresh the page
                            window.location.reload(); // refresh the page
                        } // end if invalid request
                    }); // and ajax response
                } // end submit_action
                
                // setup event handlers
                $("#refresh_admin").click(function(){
                    submit_action({
                        action: "refresh_admin",
                        admin_id: $("#admin_id").val()
                    }, true); // submit the request to the server to refresh one admin
                }); // end event handler for refresh_admin
                
                $("#refresh_all_admins").click(function(){
                    submit_action({action: "refresh_all_admins"});
                }, false);
                
                $("#create_admins").click(function(){
                    submit_action({action: "create_admins"});
                }, true);
            });
        </script>
    </body>
</html>