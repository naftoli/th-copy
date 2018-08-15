<?php
require '../db.php';

$admin_id = mysql_real_escape_string($_POST['id']);
$username = mysql_real_escape_string($_POST['username']);
$password = mysql_real_escape_string($_POST['password']);
$class_id = mysql_real_escape_string($_POST['class_id']);
$first = mysql_real_escape_string($_POST['first']);
$last = mysql_real_escape_string($_POST['last']);
$email = mysql_real_escape_string($_POST['email']);
$cell = mysql_real_escape_string($_POST['cell']);

$refresh = false;

if ($class_id) {
    // $sql = "update classes set
    //         class_teacher = '" . $name . "',
    //         email = '" . $email . "',
    //         cell = '" . $cell . "', 
    //         where class_id = " . $class_id;
    // @mysql_query($sql);
    
    $sql = "INSERT INTO admins
            SET username = '$username',
            password = '$password',
            first = '$first', last = '$last',
            admin_email = '$email',
            admin_phone_mobile = '$cell'";
    
    if ( mysql_query($sql) ) {
        $admin_id = mysql_insert_id();
    } else {
        $admin_id = mysql_query("SELECT admin_id FROM admins WHERE admin_email = '$email'");
        $admin_id = mysql_fetch_assoc($admin_id)['admin_id'];
        $refresh = true;
    }
    // connect the two accounts
    $sql = "INSERT INTO admin_auths
            SET admin_id = '$admin_id',
            id = '$class_id',
            auth = 'class',
            role_id = 13";
    if (mysql_query($sql)) {
        echo json_encode(['success' => true, 'refresh' => $refresh]);
    } else {
        echo json_encode(['success' => false, 'message' => mysql_error()]);
    }
    exit;
} else {
    $sql = "select id from admin_auths where auth = 'class' and admin_id = " . $admin_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $class_id = $row['id'];

    $email_admin_id = mysql_query("SELECT admin_id FROM admins WHERE admin_email = '$email'");
    $email_admin_id = mysql_fetch_assoc($email_admin_id)['admin_id'];

    if ( $email_admin_id && $email_admin_id !== $admin_id ) {
        $sql = "UPDATE admin_auths SET admin_id=$email_admin_id WHERE auth='class' AND admin_id='$admin_id'";
        $refresh = true;
    } else {
        $sql = "UPDATE admins SET
            username = '$username',
            password = '$password',
            first = '$first',
            last = '$last',
            admin_email = '$email',
            admin_phone_mobile = '$cell' 
            where admin_id = $admin_id";
    }

    if (mysql_query($sql)) {
        // send email to hq about change
        $sql = "SELECT class_grade, class_sub, school_name
                FROM classes c
                JOIN schools s USING (school_id)
                WHERE c.class_id = " . $class_id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $school = $row['school_name'];
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: Mashpia Admin <admin@mashpia.com>';
        $to = "cth@mashpia.com";
        $subject = "Teacher Info Changed";
        $msg = "The teacher info from <b>" . $school . " Grade: " . $grade . "</b> has just been changed.<br />
Username: " . $username . "<br />
Password: " . $password . "<br />
Name: " . $first . " " . $last . "<br />
Email: " . $email . "<br />
Phone: " . $cell . "<br />";
        mail($to, $subject, $msg, implode("\r\n", $headers));
        
        echo json_encode(['success' => true, 'refresh' => $refresh]);
    } else {
        echo json_encode(['success' => false, 'message' => mysql_error()]);
    }
}
?>