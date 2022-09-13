<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school'); 
require('header.php');

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
                border-bottom: 1px solid grey;
            }
            caption {
                border-bottom: dashed 1px black;
            }
            .col_content {
                display: none;
            }
            .hidden {
                display: none;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Chidon Registered Report</h1>
        <?php if ($admin_user['auth'] != 'super' || ($admin_user['auth'] == 'super' && isset($_POST['submit']))) : ?>

        <?php
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
        $schools = $as->getSchools();
        //echo implode( ',', array_keys( $schools ) ); exit;
//        echo "<pre>"; print_r( $schools ); echo "</pre>"; exit;
        //if ($admin_user['auth'] == 'super') $schools[82] = "Avrohom Academy";
        
        $ids = [];
        $users = array();
        foreach ($schools as $id => $school) {
            $sql = "select tc.*, u.first, u.last, u.first_he, u.last_he, u.gender, c.*, a.admin_email, a.admin_phone_mobile, a.admin_phone_mobile2, a.admin_phone_home
                    from th_chidon tc 
                    join users u using (user_id)
                    left join classes c on u.class_id = c.class_id
                    join admin_auths aa on aa.id = u.user_id 
                    join admins a using (admin_id) 
                    where tc.year = ". $year . " 
                    and aa.auth = 'user' 
                    and u.school_id = " . $id;
            if ($admin_user['auth'] != 'super') $sql .= " and deleted = 0";
            //if ($admin_user['auth'] != 'super' && $shutdown && !in_array($id, $exceptions)) $sql .= " and tc.shabbaton = 1";
            //$sql .= " and tc.shabbaton = 1";
            else {
                if (isset($_POST['showBlank'])) {
                    $sql .= " and test1a = 0";
                }
                else if (isset($_POST['blank'])) {
                    $sql .= " and test1a > 0";
                }
                if (isset($_POST['deleted'])) {
                    $sql .= " and deleted = 0";
                }
            }
            $sql .= " order by class_grade, class_sub, u.last, u.first";
            $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
            while ($row = mysql_fetch_assoc($result)) {
                $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                $name = $row['first'] . ' ' . $row['last'];
                $heName = $row['first_he'] . ' ' . $row['last_he'];
                $date = $row['reg_date'];
                $email = $row['admin_email'];
                $phone1 = $row['admin_phone_mobile'];
                $phone2 = $row['admin_phone_mobile2'];
                $phone3 = $row['admin_phone_home'];
                $chidonType = '';
                if ($row['school_rep']) $chidonType = 'Representative';
                else if ($row['contestant']) $chidonType = 'Contestant';
                /*
                $avg = ($row['test1a'] + $row['test2a']) / 2;
                if ($admin_user['auth'] != 'super') {
                    if ($avg < 55) continue;
                }
                */
                $users[$id][$grade][][$name][$email][$phone1][$phone2][$phone3][$row['th_chidon_id']][$heName][$date][$chidonType] =
                    array('paid' => $row['paid'], 'deleted' => $row['deleted'], 'info' => $row);
                if ( !array_search( $id, $ids ) ) $ids[] = $id;
            }
        }
//        echo "<pre>"; print_r( $users ); echo "</pre>"; exit;
        //echo implode(',', $ids); exit;
        echo "<input type='hidden' name='numSchools' value='" . count($ids) . "'>";
        $total = 0;
        $totals = [];
        $totalsByGrade = [];
        foreach ($users as $school_id => $info) {
            ?>
            <table>
                <caption><?=$schools[$school_id]?></caption>
                <tr>
                    <th>Chidon ID</th>
                    <th>Registered for Chidon</th>
                    <th>Home Phone</th>
                    <th>Father Cell</th>
                    <th>Mother Cell</th>
                    <th width="60px">Parent Email</th>
                    <th width="50px">Grade</th>
                    <th width="180px">Student</th>
                    <th width="160px">Hebrew Name</th>
                    <!--
                    <th width="50px">Chidon Type</th>
                    <th width="50px">Paid</th>
                    <th>Host Number</th>
                    <th>Accomodation Info</th>
                    -->
                    <th>Sweater Size</th>
                    <!--
                    <th>Shoe Size</th>
                    <th>Sandwich Preference</th>
                    <th>Walk Alone</th>
                    -->
                    <?php if ($admin_user['auth'] == 'super') : ?>
                    <th width="50px">Deleted by BC</th>
                    <?php endif; ?>
                    <th></th>
                </tr>
                <?php
                foreach ($info as $grade => $other) {
                    foreach ($other as $more) {
                        foreach ($more as $name => $other) {
                            foreach ($other as $email => $more) {
                                foreach ($more as $phone1 => $other) {
                                    foreach ($other as $phone2 => $more) {
                                        foreach ($more as $phone3 => $other) {
                                            foreach ($other as $id => $more) {
                                                foreach ($more as $he => $other) {
                                                    foreach ($other as $date => $more) {
                                                        foreach ($more as $chidonType => $other) {
                                                            echo "<tr><td>" . $id . "</td><td>" . $date . "</td><td>" .
                                                                $phone3 . "</td><td>" . $phone1 . "</td><td>" .
                                                                $phone2 . "</td><td>" . $email . "</td><td>" . $grade . "</td><td>" .
                                                                $name . "</td><td>" . $he . "</td><td>";
                                                            /*
                                                            echo $chidonType . "</td><td>" .
                                                                (intval($other['paid']) ? 'yes' : 'no') . "</td><td>";
                                                            echo $other['info']['host_number'] . "</td><td>";
                                                            $accInfo = $other['info']['host'] . "<br />" .
                                                                $other['info']['host_address1'] . ' ' . $other['info']['host_address2'];
                                                            echo $accInfo . "</td><td>";
                                                            */
                                                            echo $other['info']['size'] . "</td><td>";
                                                            /*
                                                            echo $other['info']['shoe_size'] . "</td><td>";
                                                            $sand = $other['info']['sandwich'];
                                                            if ($sand == 'cc') $sand = "cream cheese";
                                                            echo $sand . "</td><td>";
                                                            if ($other['info']['walk_night']) echo "yes";
                                                            else if ($other['info']['walk_day']) echo "only by day";
                                                            else echo "no";
                                                            echo "</td><td>";
                                                            */
                                                            if ($admin_user['auth'] == 'super') {
                                                                if (intval($other['deleted'])) {
                                                                    echo 'yes<br /><a class="addBack" href="#">add back</a>';
                                                                } else {
                                                                    echo 'no';
                                                                }
                                                                echo "</td><td>";
                                                            }
                                                            echo "<a class='remove' href='#'>delete</a></td><td></td></tr>";
                                                            $total++;

                                                            $gender = '';
                                                            if ($other['info']['gender'] == 'M') $gender = 'Boys';
                                                            else if ($other['info']['gender'] == 'F') $gender = 'Girls';

                                                            if (isset($totals[$gender][$school_id][$grade])) $totals[$gender][$school_id][$grade]++;
                                                            else $totals[$gender][$school_id][$grade] = 1;

                                                            // for totals by grade we need grade only without the sub
                                                            if ( $pos = strpos( $grade, '-' ) !== false ) {
                                                                $grade = substr( $grade, 0, $pos );
                                                            }
                                                            $grade = trim( $grade );
                                                            if ( isset( $totalsByGrade[$grade][$gender] ) ) $totalsByGrade[$grade][$gender]++;
                                                            else $totalsByGrade[$grade][$gender] = 1;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                ?>
            </table>
            <br />
            <?php
        }
        //echo $total;
        ?>
        <h2>Grand Totals</h2>
        <?php
        foreach ($totals as $gender => $more) {
            $grandTotal = 0;
            echo "<h2>Total " . $gender . "</h2>";
            echo "<table><tr><th>School</th><th>Grade</th><th>Total</th></tr>";
            foreach ($more as $school => $other) {
                $total = 0;
                foreach ($other as $grade => $num) {
                    echo "<tr><td>" . $schools[$school] . "</td><td>" . $grade . "</td><td>" . $num . "</td></tr>";
                    $total += $num;
                }
                echo "<tr><td colspan='3' align='right'>" . $total . "</td></tr>";
                $grandTotal += $total;
            }
            echo "<tr><td colspan='3' align='right'><b>Grand Total: " . $grandTotal . "</b></td></tr>";
            echo "</table>";
        }

        // only show for th hq
        if ($admin_user['auth'] == 'super') {
            ksort( $totalsByGrade );
//            var_dump( $totalsByGrade );
            // get total number of children registered in CTH but not signed up to chidon per grade
            $notSignedUp = [];
            $grades = ['3','4','5','6','7','8'];
            foreach ( $grades as $grade ) {
                // for boys

                // registered to cth
                $sql = "SELECT count(*) as total FROM users u 
                        join classes c on c.class_id = u.class_id 
                        where c.class_grade = '" . $grade . "'  
                        and u.gender = 'M' 
                        and u.user_id not in (
                        select user_id from th_chidon where year = $year) 
                        and u.user_registered > 0";
                $result = mysql_query( $sql );
                $row = mysql_fetch_assoc( $result );
                $notSignedUp[$grade]['Boys']['reg'] = $row['total'];

                // not yet registered
                $sql = "SELECT count(*) as total FROM users u 
                        join classes c on c.class_id = u.class_id 
                        where c.class_grade = '" . $grade . "'  
                        and u.gender = 'M' 
                        and u.user_id not in (
                        select user_id from th_chidon where year = $year) 
                        and (u.user_registered is null or u.user_registered = 0)";
                $result = mysql_query( $sql );
                $row = mysql_fetch_assoc( $result );
                $notSignedUp[$grade]['Boys']['notReg'] = $row['total'];

                // for girls

                // registered to cth
                $sql = "SELECT count(*) as total FROM users u 
                        join classes c on c.class_id = u.class_id 
                        where c.class_grade = '" . $grade . "'  
                        and u.gender = 'F' 
                        and u.user_id not in (
                        select user_id from th_chidon where year = $year) 
                        and u.user_registered > 0";
                $result = mysql_query( $sql );
                $row = mysql_fetch_assoc( $result );
                $notSignedUp[$grade]['Girls']['reg'] = $row['total'];

                // not yet registered
                $sql = "SELECT count(*) as total FROM users u 
                        join classes c on c.class_id = u.class_id 
                        where c.class_grade = '" . $grade . "'  
                        and u.gender = 'F'  
                        and u.user_id not in (
                        select user_id from th_chidon where year = $year) 
                        and (u.user_registered is null or u.user_registered = 0)";
                $result = mysql_query( $sql );
                $row = mysql_fetch_assoc( $result );
                $notSignedUp[$grade]['Girls']['notReg'] = $row['total'];
            }

            $totals['Boys']['signedUp'] = 0;
            $totals['Girls']['signedUp'] = 0;
            $totals['Boys']['notSignedUp'] = 0;
            $totals['Girls']['notSignedUp'] = 0;
            $totals['Boys']['notReg'] = 0;
            $totals['Girls']['notReg'] = 0;

            echo "<h2>Totals By Grade</h2>";
            echo "<table><tr><th>Grade</th><th colspan='3'>Boys</th><th colspan='3'>Girls</th></tr>";
            echo "<tr><th></th><th>Chidon Registered</th><th>Chidon Not Registered but CTH Registered</th><th>Not Registered for BOTH (Chidon / CTH)</th>";
            echo "<th>Chidon Registered</th><th>Chidon Not Registered but CTH Registered</th><th>Not Registered for BOTH (Chidon / CTH)</th></tr>";
            foreach ( $totalsByGrade as $grade => $amount ) {
                if ( is_numeric( $grade ) ) {
                    echo "<tr><td>" . $grade . "</td><td>" . (isset($amount['Boys']) ? $amount['Boys'] : 0) .
                        "</td><td>" . (isset($notSignedUp[$grade]['Boys']['reg']) ? $notSignedUp[$grade]['Boys']['reg'] : 0) .
                        "</td><td>" . (isset($notSignedUp[$grade]['Boys']['notReg']) ? $notSignedUp[$grade]['Boys']['notReg'] : 0);
                    echo "</td><td>";
                    if (isset($amount['Girls'])) echo $amount['Girls'];
                    echo "</td><td>" . (isset($notSignedUp[$grade]['Girls']['reg']) ? $notSignedUp[$grade]['Girls']['reg'] : 0)  .
                        "</td><td>" . (isset($notSignedUp[$grade]['Girls']['notReg']) ? $notSignedUp[$grade]['Girls']['notReg'] : 0) . "</td></tr>";

                    if (isset($notSignedUp[$grade]['Boys']['reg'])) $totals['Boys']['notSignedUp'] += $notSignedUp[$grade]['Boys']['reg'];
                    if (isset($notSignedUp[$grade]['Girls']['reg'])) $totals['Girls']['notSignedUp'] += $notSignedUp[$grade]['Girls']['reg'];
                    if (isset($notSignedUp[$grade]['Boys']['notReg'])) $totals['Boys']['notReg'] += $notSignedUp[$grade]['Boys']['notReg'];
                    if (isset($notSignedUp[$grade]['Girls']['notReg'])) $totals['Girls']['notReg'] += $notSignedUp[$grade]['Girls']['notReg'];
                } else {
                    echo "<tr><td>" . $grade . "</td><td>" . (isset($amount['Boys']) ? $amount['Boys'] : 0) . "</td><td></td><td></td><td>" .
                        (isset($amount['Girls']) ? $amount['Girls'] : 0) . "</td><td></td><td></td></tr>";
                }
                if (isset($amount['Boys'])) $totals['Boys']['signedUp'] += $amount['Boys'];
                if (isset($amount['Girls'])) $totals['Girls']['signedUp'] += $amount['Girls'];
            }
            echo "<tr><th>Totals:</th><th>" . $totals['Boys']['signedUp'] . "</th><th>" . $totals['Boys']['notSignedUp'] . "</th><th>" . $totals['Boys']['notReg'] . "</th><th>";
            echo $totals['Girls']['signedUp'] . "</th><th>" . $totals['Girls']['notSignedUp'] . "</th><th>" . $totals['Girls']['notReg'] . "</th></tr></table>";
            echo "<br /><br />Grand Total Signed Up: " . ($totals['Boys']['signedUp'] + $totals['Girls']['signedUp']);
        }
        ?>
        
        <?php else: ?>
        
        <form action="chidon_report.php" method="post">
        	<p>Please choose from the following options:</p>
        	<ul>
        		<li><input type="checkbox" name="showBlank" /> ONLY show children children that have no mark or who's mark is 0</li>
        		<li>&nbsp;</li>
        		<li><input type="checkbox" name="blank" /> Do NOT show children that have no mark or who's mark is 0</li>
        		<li><input type="checkbox" name="deleted" /> Do NOT show children that have been deleted by Base Commander</li>
        	</ul>
        	<br />
        	<input type="submit" name="submit" value="submit" />
        </form>
        
        <?php endif; ?>
    </body>
    <script>
        $(function() {
            var school = <?=$admin_user['auths']['school'][0]?>;
            var schools = [176,54,30,106,2];
            var passwords = {
                176 : 'laky',
                54 : 'cth792ep',
                30 : 'Chaimke10',
                106 : 'Toronto',
                2 : '8650'
            }
            var show = true;
            <?php if ($admin_user['auth'] != 'super') : ?>
            for (var s in schools) {
                if (schools[s] == school) {
                    var password = prompt("Please enter the password to access this page.");
                    if (passwords[school] != password) {
                        show = false;
                        alert('You have no permission to access this page.');
                        location.href = 'admin.php';
                    } else {
                        $(".col_content").show();
                    }
                }
            }
            <?php endif; ?>
            if (show) $(".col_content").show();
            
            $(".remove").click( function() {
                if (confirm("Are you sure you want to delete this boy/girl from the list. In order to add him back you will have to re-register him.")) {
                    var id = parseInt($(this).parent().parent().find('td').eq(0).text());
                    var elem = this;
                    var superadmin = false;
                    <? if ($admin_user['auth'] == 'super') : ?>
                        superadmin = true;
                    <? endif; ?>
                    var url = 'ajax/disableFromChidon.php';
                    if (superadmin) {
                        url = 'ajax/removeFromChidon.php';
                    }
                    $.post(url, { id : id }, function( success ) {
                        if (parseInt(success)) {
                            alert(success);
                        } else {
                            $(elem).parent().parent().remove();
                        }
                    });
                }
            });
            
            $(".addBack").click( function() {
                var id = parseInt($(this).parent().parent().find('td').eq(0).text());
                $.post('ajax/addBackToChidon.php', { id : id }, function( error ) {
                    if (parseInt(error)) {
                        alert(error);
                    } else {
                        alert('Added back.');
                        location.reload(true);
                    }
                });
            });
        });
    </script>
</html>