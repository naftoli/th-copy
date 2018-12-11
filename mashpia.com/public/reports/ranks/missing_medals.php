<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

// only superusers can use this page. Non superusers get redirected to the page that they can use
if ($admin_user['auth'] != 'super') {
    header("Location: /reports/users/");
}

$subjects_list = [];
$subjects_list_query = mysql_query(
     " SELECT subject_id, subject_name FROM subjects "
    ." WHERE subject_id IN (45, 27, 4, 90, 21, 16, 13, 40, 12, 41, 100, 1, 92, 93, 94)"
    ." ORDER BY subject_name"
);
while($subject = mysql_fetch_assoc($subjects_list_query)){
    $subjects_list[] = $subject;
}

$medals = [];
$medals_query = mysql_query("SELECT medal_ord, medal_name FROM medals ORDER BY medal_ord");
while($medal = mysql_fetch_assoc($medals_query)){
    $medals[] = $medal;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Rank Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <style>
            .half-width {width: 49.6%;display: inline-block;}
            .submit-button{text-align: center;    margin-top: 15px;}
            input[type="submit"]{padding: 6px 10px;}
            .missing_medal {margin: 10px;}
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Missing Medals Printout Report</h1>
        
        <p>Please enter the missing medals.</p>
        
        <form action="missing_medals_printout.php" method="POST" target="_blank">
            <div class="missing-medals">
                <div class="template missing_medal">
                    <div class="half-width">
                        <label>Subject:</label>
                        <select name="subjects[]">
                            <? foreach ($subjects_list as $subject) {?>
                                <option value="<?=$subject['subject_id']?>"><?=$subject['subject_name']?></option>
                            <? } ?>
                        </select>
                    </div>
                    <div class="half-width">
                        <label>Medal:</label>
                        <select name="medals[]">
                            <? foreach ($medals as $medal) {?>
                                <option value="<?=$medal['medal_ord']?>"><?=$medal['medal_name']?></option>
                            <? } ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="submit-button">
                <a class="button" id="add_medal">Add Missing Medal.</a>
                <input type="submit" value="Print Letters"/>
            </div>
        </form>
        <script>
            $("#add_medal").click(function(){
                $(".missing-medals").append("<div class='missing_medal'>" + $(".template").html() + "</div>");
            });
        </script>
    </body>
</html>