<?php
$debug = false;
/***************** DEBUGGING **********************/
// enable debuging
if ($_POST['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

$info = [];
$classesInfo = [];
// for boys 
$sql = "SELECT u.class_id, s.school_name, c.class_grade, c.class_sub, count(*) as total 
        from th_chidon tc 
        join users u using (user_id) 
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        where tc.year = " . $year . "  
        and u.school_type_id in ('2','12') 
        group by u.class_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info['Boys'][$row['class_id']] = $row['total'];
    $classesInfo['Boys'][$row['class_id']] = [
        'school'    =>  $row['school_name'], 
        'grade'     =>  $row['school_grade'], 
        'sub'       =>  $row['school_sub']
    ];
}
// for girls
$sql = "SELECT u.class_id, s.school_name, c.class_grade, c.class_sub, count(*) as total 
        from th_chidon tc 
        join users u using (user_id) 
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        where tc.year = " . $year . "  
        and u.school_type_id in ('3','13') 
        group by u.class_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info['Girls'][$row['class_id']] = $row['total'];
    $classesInfo['Girls'][$row['class_id']] = [
        'school'    =>  $row['school_name'], 
        'grade'     =>  $row['class_grade'], 
        'sub'       =>  $row['school_sub']
    ];
}
// $total = 0;
// foreach ( $info as $gender => $other ) {
//     foreach ( $other as $reg ) {
//         $total += $reg;
//     }
// }
// echo $total; 
// echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
// echo $sql;
// get total number of children registered in CTH but not signed up to chidon per school
$notSignedUp = [];
foreach ($classesInfo as $class_id => $more) {
    // for boys

    // registered to cth
    $sql = "SELECT count(*) as total FROM users u 
            join classes c on c.class_id = u.class_id 
            where u.class_id = " . $class_id . " 
            and u.school_type_id in ('2','12') 
            and u.user_id not in (
                select tc.user_id from th_chidon tc 
                join users u using (user_id) 
                where tc.year = $year
                and u.class_id = $class_id
            ) 
            and u.user_registered > 0";
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $notSignedUp['Boys'][$class_id]['reg'] = $row['total'];
                
    // not yet registered
    $sql = "SELECT count(*) as total FROM users u 
            join classes c on c.class_id = u.class_id 
            where u.class_id = " . $class_id . " 
            and u.school_type_id in ('2','12') 
            and u.user_id not in (
                select tc.user_id from th_chidon tc 
                join users u using (user_id) 
                where tc.year = $year
                and u.class_id = $class_id
            ) 
            and (u.user_registered is null or u.user_registered = 0)";
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $notSignedUp['Boys'][$class_id]['notReg'] = $row['total'];

    // for girls

    // registered to cth
    $sql = "SELECT count(*) as total FROM users u 
            join classes c on c.class_id = u.class_id 
            where u.class_id = " . $class_id . " 
            and u.school_type_id in ('3','13') 
            and u.user_id not in (
                select tc.user_id from th_chidon tc 
                join users u using (user_id) 
                where tc.year = $year
                and u.class_id = $class_id
            ) 
            and u.user_registered > 0";
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $notSignedUp['Girls'][$class_id]['reg'] = $row['total'];

    // not yet registered
    $sql = "SELECT count(*) as total FROM users u 
            join classes c on c.class_id = u.class_id 
            where u.class_id = " . $class_id . " 
            and u.school_type_id in ('3','13') 
            and u.user_id not in (
                select tc.user_id from th_chidon tc 
                join users u using (user_id) 
                where tc.year = $year
                and u.class_id = $class_id
            ) 
            and (u.user_registered is null or u.user_registered = 0)";
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $notSignedUp['Girls'][$class_id]['notReg'] = $row['total'];
}

$percentages = [];
foreach ($info as $gender => $other) {
    foreach ($other as $class_id => $registered) {
        $percent = round( ($registered / ($registered + $notSignedUp[$gender][$class_id]['reg'] + $notSignedUp[$gender][$class_id]['notReg']) * 100), 2 );
        $percentages[$gender][$class_id] = $percent;
    }
}
arsort( $percentages['Boys'] );
arsort( $percentages['Girls'] );
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
        <?php foreach ( $percentages as $gender => $other ) : ?>
            <h2><?=$gender?></h2>
            <table>
                <thead>
                    <tr>
                        <th></th><th>School</th><th>Grade</th><th>Chayolim in Base</th><th>Chayolim Registered in Chidon</th><th>Percentage Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    $prev = 0;
                    foreach ( $other as $class_id => $percent ) {  
                        // add one to i if prev percent does not equal current percent
                        if ( $prev != $percent ) {
                            $i++;
                            $prev = $percent;
                        }  
                        $class = $classesInfo[$gender][$class_id];
                        $school = $class['school'];
                        $grade = ($class['grade'] . $class['sub'] ? '-' . $class['sub'] : '');
                        echo "<tr><td>#" . $i . "</td><td>" . $school . "</td><td>" . $grade . "</td><td>";
                        echo ($info[$gender][$class_id] + $notSignedUp[$gender][$class_id]['reg'] + $notSignedUp[$gender][$class_id]['notReg']) . "</td><td>";
                        echo $info[$gender][$class_id] . "</td><td>" . $percent . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </body>
</html>