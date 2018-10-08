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

// get all chidon schools
require_once($_SERVER['DOCUMENT_ROOT'].'/class.adminSchools.php');       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();
//echo implode(',', array_keys($schools)); exit;

$classesInfo = [];
foreach ($schools as $id => $school) {
    // get grades 
    $sql = "select class_id, class_grade, class_sub 
            from classes 
            where class_era = 0 
            and class_grade in ('4','5','6','7','8') 
            and school_id = " . $id;
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $classesInfo[$row['class_id']] = array(
                'school'=>  $school, 
                'grade' =>  $row['class_grade'], 
                'sub'   =>  $row['class_sub']
        );
    }
}

$info = [];
foreach ( $classesInfo as $class_id => $other ) {
    // for boys
    
    $sql = "select count(tc.user_id) as chidon_reg 
            from th_chidon tc 
            join users u using (user_id)
            join classes c on u.class_id = c.class_id
            join admin_auths aa on aa.id = u.user_id 
            join admins a using (admin_id) 
            join schools s on s.school_id = u.school_id 
            where tc.year = " . $year . "  
            and aa.auth = 'user' 
            and u.class_id = " . $class_id . " 
            and u.school_type_id in ('2','12')";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info['Boys'][$class_id] = $row['chidon_reg'];
    }

    // for girls
    $sql = "select count(tc.user_id) as chidon_reg 
            from th_chidon tc 
            join users u using (user_id)
            join classes c on u.class_id = c.class_id
            join admin_auths aa on aa.id = u.user_id 
            join admins a using (admin_id) 
            join schools s on s.school_id = u.school_id 
            where tc.year = " . $year . "  
            and aa.auth = 'user' 
            and u.class_id = " . $class_id . " 
            and u.school_type_id in ('3','13')";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info['Girls'][$class_id] = $row['chidon_reg'];
    }
}
$total = 0;
foreach ( $info as $gender => $other ) {
    foreach ( $other as $reg ) {
        $total += $reg;
    }
}
echo $total; exit;
//echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
//echo $sql;
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
                        $info = $classesInfo[$class_id];
                        $school = $info['school'];
                        $grade = $info['grade'] . ($info['sub'] ? '-' . $info['sub'] : '');
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