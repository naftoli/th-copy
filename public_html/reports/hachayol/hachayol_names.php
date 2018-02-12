<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page. Non superusers get redirected to the page that they can use
if ($admin_user['auth'] != 'super') {
    header("Location: /reports/");
}

$school_query = mysql_query("SELECT school_id, school_name, hachayol_name FROM schools WHERE test_school=0 ORDER BY school_name");
$schools = [];
while($school = mysql_fetch_assoc($school_query)){
    $schools[] = $school;
};

if ($debug) echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Hachayol Reports | School Names</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
        <style>
            table{width: 100%;}
            tr {border-bottom: 1px solid #888;}
            td{padding: 4px; 8px;}
            input.hachayol_name{background: none; border: none; border-bottom: 1px solid;}
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>School - Hachayol Names</h1>
        <table>
            <thead>
                <th>School Name</th><th>Hachayol Name</th>
            </thead>
            <tbody>
                <? foreach($schools as $school) {?>
                <tr>
                    <td><?=$school['school_name']?></td>
                    <td>
                        <input type ="text" class="hachayol_name" data-school_id="<?=$school['school_id']?>" value="<?=$school['hachayol_name']?>"/>
                        <button class="hachayol_name_save button">Save</button>
                    </td>
                </tr>
                <?} // end foreach school ?>
            </tbody>
        </table>
        <script>
            $(document).ready(function(){
                $(".hachayol_name_save").click(function(event){
                    var hachayol_name = $(event.target).siblings(); // get the input next door
                    $.post("ajax/set_hachayol_name.php", { // save the info with an ajax request
                        school_id: hachayol_name[0].dataset.school_id,
                        hachayol_name: hachayol_name.val()
                    }, function(response){
                        response = JSON.parse(response);
                        if (!response.success) {
                            alert(response.error);
                        } else {
                            $(event.target).text("Saved!");
                            setTimeout(function(){
                                $(event.target).text("Save");
                            }, 1000); // set the text back after one second.
                        }; // end if response was sucessfull or not
                    }); // end POST request
                }); // end hachayol_name_save event listener
            }); // end on page loaded function.
        </script>
    </body>
</html>