<?php
$debug = false;
/***************** DEBUGGING **********************/
$_POST['debug'] = true;
// enable debuging
if ($_POST['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

// get schools connected to account
require_once($_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php');

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "select s.school_name, c.class_grade, c.class_sub, c.class_teacher, u.user_id, tc.th_chidon_id 
        from users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        left join th_chidon tc on tc.user_id = u.user_id 
        where u.school_id > 0 
        and u.school_id != 82 
        and u.class_id > 0 
        and s.school_era is null 
        and s.chayolei = 1 
        and c.class_grade in ('4','5','6','7','8') 
        order by school_name, class_grade, class_sub, u.user_id";
//echo $sql;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    // initialize vars if not set
    if ( !isset( $info[$row['school_name']]['reg'] ) ) $info[$row['school_name']]['reg'] = 0;
    if ( !isset( $info[$row['school_name']]['notReg'] ) ) $info[$row['school_name']]['notReg'] = 0;
    
    if ($row['th_chidon_id']) $info[$row['school_name']]['reg']++;
    else $info[$row['school_name']]['notReg']++;
}

$percentages = [];
foreach ( $info as $school => $data ) {
    $percent = round( ($data['reg'] / ($data['notReg'] + $data['reg']) * 100), 2 );
    $percentages[$school] = $percent;
}
arsort( $percentages );
//echo "<pre>"; print_r( $percentages ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            @media print {
               .noPrint {
                   display: none;
                }
            }
            body {
                font-family: Verdana;
            }
            tr, th, td {
                font-size: 14px;
                padding-top: 10px;
                padding-bottom: 10px;
                font-family: Verdana;
            }
        </style>
    </head>
    <body>
        <button onclick='window.print()' class='noPrint'>Print</button>
        <table>
            <thead>
                <tr>
                    <th></th><th>School</th><th>Chayolim in Base</th><th>Chayolim Registered in Chidon</th><th>Percentage Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach ( $percentages as $school => $percent ) {            
                    echo "<tr><td>#" . $i++ . "</td><td>" . $school . "</td><td>" . ($info[$school]['notReg'] + $info[$school]['reg']) . "</td><td>" . $info[$school]['reg'] . "</td><td>" . $percent . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
</html>