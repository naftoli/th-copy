<?php $debug = false;
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

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

/***************** POST PARAMS **********************/
$school_id = mysql_real_escape_string( $_POST['school_id'] );
foreach ( $_POST['fields'] as $v ) {
    $fields[] = mysql_real_escape_string( $v );
}
// add year field
$fields[] = 'year';

foreach ( $_POST['years'] as $y ) {
    $years[] = mysql_real_escape_string( $y );
}

// array to hold results
$users = [];

// the chidon reporting engine only shows kids enrolled into chidon so if we want to also show kids not enrolled into chidon, we need to add them manually
$options = $_POST['options'];
if ($options[0] || $options[1]) {
    $sql = "select s.school_name as school, c.class_grade, c.class_sub, u.last as last_name, u.first as first_name, u.user_registered  
            from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            where c.class_grade in ('4','5','6','7','8') 
            and u.user_id not in (
                select user_id from th_chidon 
                where year = " . $year . " 
                and school_id = " . $school_id . " 
            )";
    
    if ($options[0] && !$options[1]) { // show children enrolled into cth but not in chidon
        $sql .= "and u.user_registered > 0";
    } else if ($options[1] && !$options[0]) { // show children not enrolled into cth or chidon
        $sql .= "and (u.user_registered = 0 or u.user_registered is null)"; 
    }
    
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $row['year'] = $year;
        $row['chidonReg'] = 0;
        $users[$row['school']][$row['class_grade']][$row['class_sub']][$row['last_name']][$row['first_name']][$row['year']] = $row;
    }
}

/***************** LOAD DATA **********************/
require_once $_SERVER['DOCUMENT_ROOT'] . "/chidon/reports/class.reports.php";
foreach( $years as $y ) {
    $r = new Reports( $y );
    $qry = $r->createSql( $fields );
    $users_query = mysql_query( $qry ) or die( mysql_error() );

    while( $row = mysql_fetch_assoc($users_query) ) {
        $row['chidonReg'] = 1; // flag to know that this child is registered in chidon for that yr
        $users[$row['school']][$row['class_grade']][$row['class_sub']][$row['last_name']][$row['first_name']][$row['year']] = $row;
    }
}

// sort array
foreach ( $users as $school => $info ) {
    foreach ( $info as $grade => $other ) {
        foreach ( $other as $sub => $info ) {
            foreach ( $info as $last => $other ) {
                foreach ( $other as $first => $info ) {
                    foreach ( $info as $y => $other ) {
                        ksort( $users[$school][$grade][$sub][$last][$first][$y] );
                    }
                    ksort( $users[$school][$grade][$sub][$last][$first] );
                }
                ksort( $users[$school][$grade][$sub][$last] );
            }
            ksort( $users[$school][$grade][$sub] );
        }
        ksort( $users[$school][$grade] );
    }
    ksort( $users[$school] );
}

$temp = [];
foreach ( $users as $school => $info ) {
    foreach ( $info as $grade => $other ) {
        foreach ( $other as $sub => $info ) {
            foreach ( $info as $last => $other ) {
                foreach ( $other as $first => $info ) {
                    foreach ( $info as $y => $other ) {
                        $temp[] = $other;
                    }
                }
            }
        }
    }
}
$users = $temp;
//print_r( $users );

// find out what the fields returned are
$columns = array_keys( $users[0] );

/***************** RENDER REPORT **********************/
if($debug) echo "</pre>";

if (count($users) > 0) { ?>
    <table>
        <thead>
            <tr>
                <th>Enrolled into Chidon</th>
                <th>Enrolled into CTH</th>
                <?php foreach ( $columns as $column ) {
                    if (!in_array( $column, ['chidonReg', 'user_registered'] )) echo "<th>" . $column . "</th>"; 
                }    
                ?>
            </tr>
        </thead>
        <tbody>
            <? foreach($users as $user) {?>
            <tr class="users">
                <?php if ( $user['year'] == $year ) : ?>
                    <td><?php echo $user['chidonReg'] ? 'yes' : 'no'; ?></td>
                    <td><?php echo (isset($user['user_registered']) && $user['user_registered'] > 0) ? 'yes' : 'no'; ?></td>
                <?php else : ?>
                    <td></td>
                    <td></td>
                <?php endif; ?>
                <?php 
                    foreach ( $columns as $column ) {
                        if (!in_array( $column, ['chidonReg', 'user_registered'] )) echo "<td>" . (isset( $user[$column] ) ? $user[$column] : '') . "</td>"; 
                    }
                ?>
            </tr>
            <?}?>
        </tbody>
    </table>
<? } else { // if there are no students found... ?>
    <div class="no-report">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <h2>No Eligible Students Found</h2>
    </div> 
<? } ?>