<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

require 'class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();
//$year = 5777;

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$users = array();
foreach ($schools as $id => $school) {
    $users[$id] = array();
    $sql = "select tc.*, u.first, u.last, u.gender, u.first_he, u.last_he, c.*, a.admin_email, a.admin_phone_mobile, a.admin_phone_mobile2, a.admin_phone_home "
        ."from th_chidon tc "
        ."join users u using (user_id) "
        ."join classes c on u.class_id = c.class_id "
        ."join admin_auths aa on aa.id = u.user_id "
        ."join admins a using (admin_id) "
        ."where tc.year = " . $year . " "
        ."and aa.auth = 'user' "
        ."and tc.contestant = 1 "
        ."and tc.can_enroll = 1 "
        ."and u.school_id = " . $id;
    if (isset($_GET['id'])) $sql .= " and tc.th_chidon_id = " . $_GET['id'];
    $sql .= " order by class_grade, class_sub, u.last, u.first";
    echo "<input type='hidden' name='sql' value='" . $sql . "' />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $users[$id][] = $row;
    }
}
//echo "<pre>"; print_r($users); echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Child Chidon Info</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style type='text/css'>
            .userInfo {
                font-size: 16px;
                line-height: 1.4;
                padding: 18px;
                page-break-after: always;
            }
            @media print{
                .noPrint {
                    display: none;
                }
                .userInfo {
                    margin-top: auto;
                    margin-bottom: auto;
                }
            }
            a.button{display: inline-block;}
            a#next_page{float: right;}
            a#prev_page{float: left;}
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); // require a password to use this page... ?>
        <h1 class="noPrint">Child Chidon Info</h1>
        
        <h3 class="noPrint">
            Please print out the final Chidon enrollment information for your contestants to review with their parents for accuracy and return signed.
        </h3>
        
        <div class="noPrint" align="center">
            <a class='button' id="prev_page" href='/review_enrollment.php'><i class="fa fa-arrow-left"></i> Review Enrollment</a>
            <br style="clear: both"/>
            <a class="button" onclick="window.print();">Print</a>
        </div>
        
        <?php
        foreach ($schools as $id => $school) {
            echo "<h2 class='noPrint'>" . $school . "</h2>";
            foreach ($users[$id] as $user) {
                echo "<div class='userInfo'>";
                echo "School: " . $school . "<br />";
                echo "Grade: " . $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']) . "<br />";
                echo "First Name: " . $user['first'] . "<br />";
                echo "Last Name: " . $user['last'] . "<br />";
                echo "Hebrew First Name: " . $user['first_he'] . "<br />";
                echo "Hebrew Last Name: " . $user['last_he'] . "<br />";
                echo "Book: " . $user['book'] . "<br />";
                echo "Gender: " . $user['gender'] . "<br />";
                echo "Avg Part 1: ";
                echo number_format(($user['test1a'] + $user['test2a'] + $user['test3a']) / 3, 2) . "<br />";
                echo "Test Language: ";
                if ($user['test_lang'] == 'en') echo "english<br />";
                else if ($user['test_lang'] == 'yi') echo "yiddish<br />";
                echo "Sweater Size: " . $user['size'] . "<br />";
                echo "Shoe Size: " . $user['shoe_size'] . "<br />";
                echo "Sandwich: " . $user['sandwich'] . "<br />";
                echo "Allergies: " . (empty($user['allergies']) ? 'none' : $user['allergies']) . "<br />";
                echo "Allow walking home alone: ";
                if ($user['walk_night']) echo "yes ";
                else if ($user['walk_day']) echo "only by day ";
                else echo "no ";
                echo "<br />";
                echo "Host Name: " . $user['host'] . "<br />";
                echo "Host Address: " . $user['host_address1'] . (empty($user['host_address2']) ? '' : ' ' . $user['host_address2']) . "<br />";
                echo "Between Streets: " . $user['between_streets'] . "<br />";
                echo "Host Phone Number: " . $user['host_number'] . "<br />";
                echo "Parent Email: " . $user['admin_email'] . "<br />";
                echo "Parent Contact Number(s): ";
                if (!empty($user['admin_phone_mobile'])) echo $user['admin_phone_mobile'] . ' ';
                if (!empty($user['admin_phone_mobile2'])) echo $user['admin_phone_mobile2'] . ' ';
                if (!empty($user['admin_phone_home'])) echo $user['admin_phone_home'] . ' ';
                echo "<br /><br />";
                echo "Please verify above information and fix anything which is mistaken.<br /><br />";
                echo "<input type='checkbox'> I verify that I have looked over the information above and fixed anything which is wrong.<br /><br />";
                echo "Parent Signature: ______________________________________<br /><br />";
                echo "Child Signature: _______________________________________";
                echo "</div>";
            }
        }
        ?>