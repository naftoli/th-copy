<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
//$year = 5778;

$userInfo = array();
foreach ($schools as $sid => $schoolName) {
    $sql = "select tc.*, u.first, u.last, u.gender, c.*, a.admin_email, a.admin_phone_mobile, a.admin_phone_mobile2, a.admin_phone_home
            from th_chidon tc 
            join users u using (user_id)
            join classes c on u.class_id = c.class_id
            join admin_auths aa on aa.id = u.user_id 
            join admins a using (admin_id) 
            where tc.year = " . $year . "
            and (tc.contestant = 1 or tc.school_rep = 1) 
            and tc.date_paid > 0 
            and aa.auth = 'user'
            and u.school_id = " . $sid;
    $sql .= " order by class_grade, class_sub, tc.school_rep desc, u.last, u.first";
    echo "<input type='hidden' name='sql' value=\"" . $sql . "\" />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $name = $row['first'] . ' ' . $row['last'];
        $userInfo[$sid][$grade][$row['th_chidon_id']][$name] = $row;
    }
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Review Enrollment</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 5px;
            }
            caption {
                border-bottom: solid 1px black;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            .tests input {
                width: 30px;
            }
            input[disabled] {
                color: #A9A9A9;
                padding: 2px;
                margin: 0 0 0 0;
                background-image: none;
            }
            a.button{display: inline-block;}
            a#next_page{float: right;}
            a#prev_page{float: left;}
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); // require a password to use this page... ?>
        <h1>Review Enrollment</h1>
 
        <?php foreach ($userInfo as $sid => $info) : ?>
            <table class="tests">
                <caption><?=$schools[$sid]?></caption>
                <tr>
                    <th>Grade</th>
                    <th>Student</th>
                    <th>Eligibility Status</th>
                    <th>Parent Email / Phone</th>
                    <th>Enrolled</th>
                    <th>Print Enrollment Info</th>
                    <th>Confirm Enrollment Info</th>
                </tr>
                <?php
                $curGrade = 0;
                foreach ($info as $grade => $other) {
                    foreach ($other as $chidon_id => $more) {
                        foreach ($more as $name => $row) {
                            
                            if ($curGrade != $grade) {
                                echo "<tr><td colspan='8'><h2></h2></td></tr>";
                                $curGrade = $grade;
                            }
                            
                            echo "<tr id=" . $chidon_id . "><td>" . $grade . "</td><td>" . $name . "</td><td>";
                            if ($row['school_rep']) echo "Representative";
                            else if ($row['contestant']) echo "Contestant";
                            echo "</td><td>";
                            echo $row['admin_email'] . "<br />" . $row['admin_phone_mobile'] . "<br />" . $row['admin_phone_mobile2'] . "<br />" . $row['admin_phone_home'];
                            echo "</td><td>";
                            if ($row['date_paid']) echo $row['date_paid'];
                            echo "</td><td><button class='print'>print</button>";
                            echo "</td><td><input type='checkbox' class='confirmEnrollment' ";
                            if ($row['confirmed']) echo "checked ";
                            echo "/>";
                            echo "</td></tr>";
                        }
                    }
                }
                ?>
            </table>
            <br />
        <?php endforeach; ?>
        <a class='button' id="prev_page" href='/enrollment.php'><i class="fa fa-arrow-left"></i> Activate Enrollment</a>
        <a class='button' id="next_page" href='/chidon_review.php'>Print Enrollment Info <i class="fa fa-arrow-right"></i></a>
    </BODY>
    <script>
        $(".print").click( function() {
            var id = $(this).parent().parent().attr('id');
            location.href = 'chidon_review.php?id=' + id;
        });
        
        $(".confirmEnrollment").click( function() {
            var id = $(this).parent().parent().attr('id');
            var val = $(this).is(":checked") ? 1 : 0;
            $.post('ajax/updateChidon.php', { id : id, field : 'confirmed', val : val }, function( error ) {
                if (parseInt(error) != 0) {
                    alert('Error updating.');
                }
            });
        });
    </script>
</HTML>