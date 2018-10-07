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

// if we have current year to check then make sure to add user_registered field
if ( in_array( $year, $years ) ) {
    if (!in_array('user_registered', $fields)) {
        $fields[] = 'user_registered';
    }
}

// array to hold results
$users = [];

// the chidon reporting engine only shows kids enrolled into chidon so if we want to also show kids not enrolled into chidon, we need to add them manually
$options = $_POST['options'];

// make sure that current year was selected if choosing to show option
if (in_array( $year, $years ) && ($options[0] == 'true' || $options[1] == 'true')) { // ajax call send true / false as string
    $sql = "select c.class_grade, c.class_sub, c.class_teacher, u.last as last_name, u.first as first_name, u.user_registered  
            from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            where c.class_grade in ('4','5','6','7','8') 
            and u.school_id = " . $school_id . " 
            and u.user_id not in (
                select user_id from th_chidon 
                where year = " . $year . " 
                and school_id = " . $school_id . " 
            ) ";
    
    if ($options[0] && !$options[1]) { // show children enrolled into cth but not in chidon
        $sql .= "and u.user_registered > 0";
    } else if ($options[1] && !$options[0]) { // show children not enrolled into cth or chidon
        $sql .= "and (u.user_registered = 0 or u.user_registered is null)"; 
    }

    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $row['year'] = $year;
        $row['chidonReg'] = 0;
        $users[$row['class_grade']][$row['class_sub']][$row['last_name']][$row['first_name']][$row['year']] = $row;
    }
}

/***************** LOAD DATA **********************/
require_once $_SERVER['DOCUMENT_ROOT'] . "/chidon/reports/class.reports.php";
foreach( $years as $y ) {
    $r = new Reports( $y, $school_id );
    $qry = $r->createSql( $fields );
    //echo $qry;
    $users_query = mysql_query( $qry ) or die( mysql_error() );

    while( $row = mysql_fetch_assoc($users_query) ) {
        $row['chidonReg'] = 1; // flag to know that this child is registered in chidon for that yr
        $users[$row['class_grade']][$row['class_sub']][$row['last_name']][$row['first_name']][$row['year']] = $row;
    }
}

// sort array
foreach ( $users as $grade => $other ) {
    foreach ( $other as $sub => $info ) {
        foreach ( $info as $last => $other ) {
            foreach ( $other as $first => $info ) {
                foreach ( $info as $y => $other ) {
                    ksort( $users[$grade][$sub][$last][$first][$y] );
                }
                ksort( $users[$grade][$sub][$last][$first] );
            }
            ksort( $users[$grade][$sub][$last] );
        }
        ksort( $users[$grade][$sub] );
    }
    ksort( $users[$grade] );
}

// flatten array for report rendering
// create two arrays one for regular report and other for printed report
$temp = [];
foreach ( $users as $grade => $other ) {
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
$printed = [];
foreach ( $users as $grade => $other ) {
    foreach ( $other as $sub => $info ) {
        foreach ( $info as $last => $other ) {
            foreach ( $other as $first => $info ) {
                foreach ( $info as $y => $other ) {
                    $printed[$grade][$sub][] = $other;
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

if (count($users) > 0) { 
    if (isset($_POST['printed']) && $_POST['printed']) {
        echo "<button onclick='window.print()' class='noPrint'>Print</button>";
        foreach ($printed as $grade => $more) {
            foreach ($more as $sub => $users) {
                echo "<h4>Grade: " . $grade . ($sub ? '-' . $sub : '') . "<br />";
                echo "Teacher: " . $users[0]['class_teacher'] . "</h4>";
                if ( $users[0]['year'] == $year ) $showExtra = true;
                else $showExtra = false;
                $totalChidonReg = 0;
                ?>
                <table>
                    <thead>
                        <tr>
                            <?php if ( $showExtra ) : ?>
                                <th><?php if ($options[0] == 'true') echo "Enrolled into Chidon"; ?></th>
                                <th><?php if ($options[1] == 'true') echo "Enrolled into CTH"; ?></th>
                            <? endif; ?>
                            <?php foreach ( $columns as $column ) {
                                if (!in_array( $column, ['chidonReg', 'user_registered', 'class_grade', 'class_sub', 'class_teacher'] )) echo "<th>" . $column . "</th>"; 
                            } 
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                    <? foreach($users as $user) {?>
                        <?php
                        if ($user['chidonReg']) $totalChidonReg++;
                        ?>
                        <tr class="users">
                            <?php if ( $showExtra ) : ?>
                                <td><?php if ($options[0] == 'true') echo $user['chidonReg'] ? 'yes' : 'no'; ?></td>
                                <td><?php if ($options[1] == 'true') echo (isset($user['user_registered']) && $user['user_registered'] > 0) ? 'yes' : 'no'; ?></td>
                            <?php endif; ?>
                            <?php 
                                foreach ( $columns as $column ) {
                                    if (!in_array( $column, ['chidonReg', 'user_registered', 'class_grade', 'class_sub', 'class_teacher'] )) echo "<td>" . (isset( $user[$column] ) ? $user[$column] : '') . "</td>"; 
                                }
                            ?>
                        </tr>
                        <?}?>
                    </tbody>
                </table>
                <h4>Total children Registered for Chidon this year: <?=$totalChidonReg?>
                <?php
                // if we are showing also registered to CTH but unregistered to Chidon show percentage
                if ( $options[1] == 'true' ) {
                    $total = count($users);
                    echo "<br />Percentage of children registered for Chidon this year: " . round( ($totalChidonReg / $total * 100), 2 ) . "%";
                }
                ?>
                </h4>
                <div style="page-break-after: always"></div>
                <?
            }
        }
    } else {
        if ( $users[0]['year'] == $year ) $showExtra = true;
        else $showExtra = false;
        ?>
        <table>
            <thead>
                <tr>
                    <?php if ( $showExtra ) : ?>
                        <th><?php if ($options[0] == 'true') echo "Enrolled into Chidon"; ?></th>
                        <th><?php if ($options[1] == 'true') echo "Enrolled into CTH"; ?></th>
                    <? endif; ?>
                    <?php foreach ( $columns as $column ) {
                        if (!in_array( $column, ['chidonReg', 'user_registered', 'class_teacher'] )) echo "<th>" . $column . "</th>"; 
                    }    
                    ?>
                </tr>
            </thead>
            <tbody>
                <? foreach($users as $user) {?>
                <tr class="users">
                    <?php if ( $showExtra ) : ?>
                        <td><?php if ($options[0] == 'true') echo $user['chidonReg'] ? 'yes' : 'no'; ?></td>
                        <td><?php if ($options[1] == 'true') echo (isset($user['user_registered']) && $user['user_registered'] > 0) ? 'yes' : 'no'; ?></td>
                    <?php endif; ?>
                    <?php 
                        foreach ( $columns as $column ) {
                            if (!in_array( $column, ['chidonReg', 'user_registered', 'class_teacher'] )) echo "<td>" . (isset( $user[$column] ) ? $user[$column] : '') . "</td>"; 
                        }
                    ?>
                </tr>
                <?}?>
            </tbody>
        </table>
        <? 
        } 
    } else { // if there are no students found... ?>
    <div class="no-report">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <h2>No Eligible Students Found</h2>
    </div> 
<? } ?>