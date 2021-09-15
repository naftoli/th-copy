<?php $debug = false;
/***************** DEBUGGING **********************/
// enable debuging
if (isset( $_POST['debug'] ) && $_POST['debug']) {
    //;
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
            where c.class_grade in ('4','5','6','7','8') ";
    if ($school_id > 0) {
        $sql .= "and u.school_id = " . $school_id . " 
                and u.user_id not in (
                    select user_id from th_chidon 
                    where year = " . $year . " 
                    and school_id = " . $school_id . " 
                )";
    } else if ($school_id == -1) {
        $sql .= "and u.user_id not in (
                    select user_id from th_chidon 
                    where year = " . $year . " 
                )";
    }
    
    if ($options[0] && !$options[1]) { // show children enrolled into cth but not in chidon
        $sql .= "and u.user_registered > 0";
    } else if ($options[1] && !$options[0]) { // show children not enrolled into cth or chidon
        $sql .= "and (u.user_registered = 0 or u.user_registered is null)"; 
    }

    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $row['chidonReg'] = 0;
        $users[$row['class_grade']][$row['class_sub']][$row['last_name']][$row['first_name']] = $row;
    }
}

/***************** LOAD DATA **********************/
require_once $_SERVER['DOCUMENT_ROOT'] . "/chidonOld/reports/class.reports.php";
foreach( $years as $y ) {
    if ($school_id > 0) $r = new Reports( $y, $school_id );
    else $r = new Reports( $y );
    $qry = $r->createSql( $fields );
//    echo $qry; exit;
    $users_query = mysql_query( $qry ) or die( mysql_error() );

    while( $row = mysql_fetch_assoc($users_query) ) {
        $row['chidonReg'] = 1; // flag to know that this child is registered in chidon for that yr
        $users[$row['class_grade']][$row['class_sub']][$row['last_name']][$row['first_name']] = $row;
    }
}

// sort array
foreach ( $users as $grade => $other ) {
    foreach ( $other as $sub => $info ) {
        foreach ( $info as $last => $other ) {
            foreach ( $other as $first => $info ) {
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
                $temp[] = $info;
            }
        }
    }
}
$printed = [];
foreach ( $users as $grade => $other ) {
    foreach ( $other as $sub => $info ) {
        foreach ( $info as $last => $other ) {
            foreach ( $other as $first => $info ) {
                $printed[$grade][$sub][] = $info;
            }
        }
    }
}
$users = $temp;
//echo "<pre>"; print_r( $users ); echo "</pre>";

// find out what the fields returned are
$columns = array_values($fields);
$niceFields = $_POST['niceFields'];

/***************** RENDER REPORT **********************/
if($debug) echo "</pre>";

if (count($users) > 0) { 
    if (isset($_POST['printed']) && $_POST['printed']) {
        echo "<button onclick='window.print()' class='noPrint'>Print</button>";
        foreach ($printed as $grade => $more) {
            foreach ($more as $sub => $users) {
                echo "<h4>Grade: " . $grade . ($sub ? '-' . $sub : '') . "<br />";
                echo "Teacher: " . $users[0]['class_teacher'] . "</h4>";
                if ($options[0] == 'true' || $options[1] == 'true') $showExtra = true;
                else $showExtra = false;
                $totalChidonReg = 0;
                ?>
                <table>
                    <thead>
                        <tr>
                            <?php if ( $showExtra ) : ?>
                                <th><?php if ($options[0] == 'true' || $options[1] == 'true') echo "Enrolled into Chidon"; ?></th>
                                <th><?php if ($options[1] == 'true') echo "Enrolled into CTH"; ?></th>
                            <? endif; ?>
                            <?php foreach ( $columns as $column ) {
                                if (!in_array( $column, ['chidonReg', 'user_registered', 'class_grade', 'class_sub', 'class_teacher'] )) {
                                    if (isset($niceFields[$column])) echo "<th>" . $niceFields[$column] . "</th>";
                                    else {
                                        if (in_array($column, ['first','last','admin_email','admin_phone_mobile','admin_phone_mobile2'])) {
                                            // deal with parent info
                                            if ($column == 'first') {
                                                // show parent first and last name
                                                echo "<th>Parent Name</th>";
                                            } else if ($column == 'admin_phone_mobile') {
                                                echo "<th>Parent Cell Number(s)</th>";
                                            } else if ($column == 'admin_email') {
                                                echo "<th>Parent Email</th>";
                                            }
                                        }
                                    }
                                }
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
                                <td><?php if ($options[0] == 'true' || $options[1] == 'true') echo $user['chidonReg'] ? 'yes' : 'no'; ?></td>
                                <td><?php if ($options[1] == 'true') echo (isset($user['user_registered']) && $user['user_registered'] > 0) ? 'yes' : 'no'; ?></td>
                            <?php endif; ?>
                            <?php 
                                foreach ( $columns as $column ) {
                                    if (!in_array( $column, ['chidonReg', 'user_registered', 'class_sub', 'class_teacher'] )) {
                                        if (in_array($column, ['class','first','last','admin_email','admin_phone_mobile','admin_phone_mobile2'])) {
                                            if ($column == 'class') {
                                                echo "<td>" . $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']) . "</td>";
                                            }
                                            // deal with parent info
                                            if ($column == 'first') {
                                                // show parent first and last name
                                                echo "<td>" . $user['first'] . ' ' . $user['last'] . "</td>";
                                            } else if ($column == 'admin_phone_mobile') {
                                                echo "<td>" . $user[$column] . "<br />" . $user['admin_phone_mobile2'] . "</td>";
                                            } else if ($column == 'admin_email') {
                                                echo "<td>" . $user[$column] . "</td>";
                                            }
                                        } else {
                                            if (isset($user[$column])) echo "<td>" . $user[$column] . "</td>";
                                        }
                                    }
                                }
                            ?>
                        </tr>
                        <?}?>
                    </tbody>
                </table>
                <h4>Total children Registered for Chidon: <?=$totalChidonReg?>
                <?php
                // if we are showing also registered to CTH but unregistered to Chidon show percentage
                if ( $options[1] == 'true' ) {
                    $total = count($users);
                    echo "<br />Total children Registered for CTH: " . $total;
                    echo "<br />Percentage of children Registered for Chidon this year: " . round( ($totalChidonReg / $total * 100), 2 ) . "%";
                }
                ?> 
                </h4>
                <div style="page-break-after: always"></div>
                <?
            }
        }
    } else {
        if ($options[0] == 'true' || $options[1] == 'true') $showExtra = true;
        else $showExtra = false;
        ?>
        <table>
            <thead>
                <tr>
                    <?php if ( $showExtra ) : ?>
                        <th><?php if ($options[0] == 'true' || $options[1] == 'true') echo "Enrolled into Chidon"; ?></th>
                        <th><?php if ($options[1] == 'true') echo "Enrolled into CTH"; ?></th>
                    <? endif; ?>
                    <?php foreach ( $columns as $column ) {
                        if (!in_array( $column, ['chidonReg', 'user_registered'] )) {
                            if (isset($niceFields[$column])) echo "<th>" . $niceFields[$column] . "</th>";
                            else {
                                if (in_array($column, ['first','last','admin_email','admin_phone_mobile','admin_phone_mobile2'])) {
                                    // deal with parent info
                                    if ($column == 'first') {
                                        // show parent first and last name
                                        echo "<th>Parent Name</th>";
                                    } else if ($column == 'admin_phone_mobile') {
                                        echo "<th>Parent Cell Number(s)</th>";
                                    } else if ($column == 'admin_email') {
                                        echo "<th>Parent Email</th>";
                                    }
                                }
                            }
                        }
                    }    
                    ?>
                </tr>
            </thead>
            <tbody>
                <? foreach($users as $user) {?>
                <tr class="users">
                    <?php if ( $showExtra ) : ?>
                        <td><?php if ($options[0] == 'true' || $options[1] == 'true') echo $user['chidonReg'] ? 'yes' : 'no'; ?></td>
                        <td><?php if ($options[1] == 'true') echo (isset($user['user_registered']) && $user['user_registered'] > 0) ? 'yes' : 'no'; ?></td>
                    <?php endif; ?>
                    <?php
                    foreach ( $columns as $column ) {
                        if (!in_array( $column, ['chidonReg', 'user_registered'] )) {
                            if (in_array($column, ['class','first','last','admin_email','admin_phone_mobile','admin_phone_mobile2'])) {
                                if ($column == 'class') {
                                    echo "<td>" . $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']) . "</td>";
                                }
                                // deal with parent info
                                else if ($column == 'first') {
                                    // show parent first and last name
                                    echo "<td>" . $user['first'] . ' ' . $user['last'] . "</td>";
                                } else if ($column == 'admin_phone_mobile') {
                                    echo "<td>" . $user[$column] . "<br />" . $user['admin_phone_mobile2'] . "</td>";
                                } else if ($column == 'admin_email') {
                                    echo "<td>" . $user[$column] . "</td>";
                                }
                            } else {
                                if (isset($user[$column])) echo "<td>" . $user[$column] . "</td>";
                            }
                        }
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