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
print_r( $admin_user );
$as = new adminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

$totalReg = [];
$totalUnreg = [];
$schoolInfo = [];
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
        and s.school_id in (" . implode(',', array_keys($schools)) . ") 
        order by school_name, class_grade, class_sub, u.user_id";
//echo $sql;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    // initialize vars if not set
    if ( !isset( $schoolInfo[$row['school_name']][$row['class_grade']][$row['class_sub']] ) ) $schoolInfo[$row['school_name']][$row['class_grade']][$row['class_sub']] = 0;
    if ( !isset( $totalReg[$row['school_name']][$row['class_grade']][$row['class_sub']] ) ) $totalReg[$row['school_name']][$row['class_grade']][$row['class_sub']] = 0;
    if ( !isset( $totalUnreg[$row['school_name']][$row['class_grade']][$row['class_sub']] ) ) $totalUnreg[$row['school_name']][$row['class_grade']][$row['class_sub']] = 0;
    
    if ($row['th_chidon_id']) $totalReg[$row['school_name']][$row['class_grade']][$row['class_sub']]++;
    else $totalUnreg[$row['school_name']][$row['class_grade']][$row['class_sub']]++;
}
// echo "<pre>"; 
// print_r( $totalReg );
// print_r( $totalUnreg ); 
// echo "</pre>";
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
        <?php
        foreach ( $schoolInfo as $school => $info ) {
            echo "<h2>" . $school . "</h2>";
            echo "<table><tr><th>Grade</th><th>Sub</th><th>Number of Chayolim in Platoon</th><th>Number of Chayolim Registered in Chidon</th><th>Percentage of Chayolim Registered</th></tr>";
            foreach ( $info as $grade => $other ) {
                foreach ( $other as $sub => $notNeeded ) {
                    echo "<tr><td>" . $grade . "</td><td>" . $sub . "</td><td>" . $totalUnreg[$school][$grade][$sub] . "</td><td>" . $totalReg[$school][$grade][$sub] . "</td><td>";
                    $percent = round( ($totalReg[$school][$grade][$sub] / ($totalReg[$school][$grade][$sub] + $totalUnreg[$school][$grade][$sub]) * 100), 2 );
                    echo $percent . "</td></tr>";
                }
            }
        }
        ?>
    </body>
</html>