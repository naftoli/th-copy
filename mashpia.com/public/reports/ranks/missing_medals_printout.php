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

$missing_medals = [];

foreach($_POST['subjects'] as $index => $subject) {
    $missing_medals[] = ["subject_id" => $subject, "medal_ord" => $_POST['medals'][$index]];
}

// get this from the post data...


$missing_medals_info = [];
$missing_medals_sql = [];
// add all the missing medals to the array///
foreach($missing_medals as $medal) {
    $missing_medals_sql[] = "(mm.subject_id = '".$medal['subject_id']."' AND mm.medal_ord = '".$medal['medal_ord']."')";
    
    $subject_name_query = mysql_query("SELECT subject_name FROM subjects WHERE subject_id = '". $medal['subject_id'] ."'");
    $medal_name_query   = mysql_query("SELECT medal_name   FROM medals   WHERE medal_ord  = '". $medal['medal_ord']  ."'");
    
    $missing_medals_info[] = ["subject" => mysql_result($subject_name_query, 0), "medal" => mysql_result($medal_name_query, 0)];
}

require_once($_SERVER["DOCUMENT_ROOT"]."/class.report.php");
$report = new Report(); // set the previous dates to true if the toggle is pointing to "previous"
$greg_start = $report->getReportDates()['start'];
$greg_end = $report->getReportDates()['end'];

$user_query = mysql_query(
    " SELECT u.first, u.last, s.school_name, c.class_grade, c.class_sub "
    ." FROM medal_marks mm "
    ." JOIN users u USING (user_id) "
    ." JOIN schools s USING (school_id) "
    ." JOIN classes c USING (class_id) "
    ." WHERE (".implode(" OR ", $missing_medals_sql). ") "
    ." AND date_awarded >= $greg_start AND date_awarded <= $greg_end "
    ." GROUP BY user_id "
    ." ORDER BY s.school_name, c.class_grade, c.class_sub, u.first, u.last "
);
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Missing Medal Letters</title>
        <style>
            .page-break {page-break-after: always;}
            p.large{font-size: 1.3em;line-height: 1.4;}
            p.school-name{margin-bottom: 50px; text-align: center; font-size: .9em;display: none;}
            @media print {
                p.school-name{display: block;}
            }
            div.letter{font-size: 1.5em;}
        </style>
    </head>
    <body>
        <h1>Tzivos Hashem Missing Medals Report</h1>
        <p class="large">
            <strong>Report Dates: </strong>
            From <?=$report->getHeReportDates()['start_he']?> To <?=$report->getHeReportDates()['end_he']?>
        </p>
        <p class="large">
            <strong>Medals Missing: </strong><br/>
            <? foreach ($missing_medals_info as $index => $info) { ?>
                <?=($index + 1).". ".$info['medal']." ".$info['subject']?> Medal</br>
            <? } ?>
        </p>
        <div class="page-break"></div>
        
        <? while($user = mysql_fetch_assoc($user_query)) { ?>
            <p class="school-name">
                <?=$user['school_name']?> - <?=$user['class_grade'] . " " .$user['class_sub']?>
            </p>
            
            <div class="letter">
                <p>Dear <?=$user['first']?> <?=$user['last']?>,</p>
                
                <p>
                    This year, you have earned more medals than ever before--a total of 51,913.
                    So many medals have been earned that here at Tzivos Hashem Headquarters we can't keep up--we've actually run out of medals.
                    New ones are being created right now at our factories in China, and then they'll travel to Headquarters on a huge freight ship.
                    Once they arrive, they'll be mailed to your school right away.
                    Thanks for your patience, and keep on bringing Moshiach with all of your missions.
                </p>
                
                <p>- Rabbi Shimmy Weinbaum</p>
            </div>
            
            <div class="page-break"></div>
        <? } ?>
    </body>
</html>