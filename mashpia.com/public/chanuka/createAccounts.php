<?php
//ini_set('display_errors', 1);
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$missions_arr = [
    ['name' => "I lit Menorah", 'mission-img' => 'Asset 10.svg', 'prize' => "Silver menorah", 'prize-img' => "menorah.png", 'amount' => 2],
    ['name' => "I said V’al Hanisim", 'mission-img' => 'Asset 6.svg', 'prize' => "Helicopter ride (for 2)", 'prize-img' => "helicopter.png", 'amount' => 3],
    ['name' => "I received Chanukah Gelt", 'mission-img' => 'Asset 5.svg', 'prize' => "Set of shas", 'prize-img' => "shas.png", 'amount' => 2],
    ['name' => "I ate food fried in oil", 'mission-img' => 'Asset 9.svg', 'prize' => "Latest camera", 'prize-img' => "camera.png", 'amount' => 3],
    ['name' => "I played Dreidel", 'mission-img' => 'Asset 8.svg', 'prize' => "Hoverboard", 'prize-img' => "hoverbord.png", 'amount' => 3],
    ['name' => "I hosted or attended a Chanukah party", 'mission-img' => 'Asset 1.svg', 'prize' => "Electronic keyboard", 'prize-img' => "eleckeybord.png", 'amount' => 3],
    ['name' => "I got another person to light Menorah", 'mission-img' => 'Asset 4.svg', 'prize' => "$600 at a nearby seforim store", 'prize-img' => "bookcase.png", 'amount' => 2, 'notes' => "You found someone who was not planning to light Menorah & encouraged him to light."],
    ['name' => "I publicized the miracle of Chanukah", 'mission-img' => 'Asset 3.svg', 'prize' => "Electric scooter", 'prize-img' => "elecScooter.png", 'amount' => 3, 'notes' => "This can be done by sending a message on social media, putting up a menorah on your family car or any other way of publicizing Chanukah."],
    ['name' => "I did a mitzvah to bring Moshiach", 'mission-img' => 'Asset 2.svg', 'prize' => "Drone", 'prize-img' => "drone.png", 'amount' => 3, 'notes' => "You can choose any random Mitzvah. It might be your Mitzvah that will tip the scale & bring Moshiach!"]
];

$info = json_decode($_POST['data']);
$fields = ['new_account', 'serial_number', 'first_name', 'last_name', 'dob', 'email_address', 'tasks'];
foreach ($fields as $field) {
    $$field = $info->$field;
}

$user_id = 0;
$success = true;
$marked = true;
$admin_id = 0;

if ($new_account) {
    require $_SERVER['DOCUMENT_ROOT'] . '/newClasses/newParent.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/newClasses/newSoldier.php';

    // first find out if there's an admin already that exists with this email
    $sql = "select * from admins where admin_email = '" . $email_address . "'";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $parent = mysql_fetch_object($result);
    } else {
        $parent = new NewParent();
        $parent->action([
            'username' => $email_address,
            'password' => '1234',
            'admin_email' => $email_address
        ]);
    }

    if ($parent->admin_id) {
        $admin_id = $parent->admin_id;
        $child = new NewSoldier($parent, $first_name, $last_name, $dob, '', 640, 6988, '', '');
        $child->setSchoolType( 50 ); // indicates new child that needs to have type set when logging into mobile site
        if (
            $child->create()
        ) {
            $user_id = $child->getUserID();
            // create private rank for child and update start date in users table
            $sql1 = "update users set user_start_date = " . unixtojd() . " where user_start_date is null and user_id = " . $user_id;
            $sql2 = "insert ignore into rank_marks set rank_ord = 1, user_id = " . $user_id . ", date_promoted = " . unixtojd();
            mysql_query($sql1);
            mysql_query($sql2);
        } else {
            $success = false;
        }
    } else {
        $success = false;
    }
} else {
    $sql = "select user_id from users where user_serial = " . $serial_number;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $user_id = $row['user_id'];
    } else {
        $success = false;
    }
}

// save missions marked to db
if ($user_id) {
    $qrys = [];
    foreach ($tasks as $task_num) {
        $qrys[] = "insert ignore into chanuka_missions 
                    set user_id = " . $user_id . ", 
                    task = \"" . addslashes($missions_arr[intval($task_num) - 1]['name']) . "\",
                    task_num = " . intval($task_num) . ", 
                    year = " . GlobalSettings::getCurrentYear();
    }
    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($qrys as $qry) {
        if (!mysql_query($qry)) {
            $marked = false;
            break;
        }
    }
    if ($marked) {
        mysql_query('commit');
        mysql_query('set autocommit=1');
    } else {
        mysql_query('rollback');
        mysql_query('set autocommit=1');
    }
}

if ($success && $marked) {
    // send email to child
    $to = $email_address;
    $subject = "Chanukah Challenge";
    $msg = "<strong>CONGRATULATIONS!</strong><br />
Thank you for entering the Chanukah Challenge!<br /><br />
You have completed the following mission(s):<br />
<ul>";
    foreach ($tasks as $task_num) {
        $msg .= "<li>" . $missions_arr[intval($task_num) - 1]['name'] . "</li>";
    }
    $msg .= "
</ul>
You have entered the grand raffle to win:<br />
<ul>";
    foreach ($tasks as $task_num) {
        $msg .= "<li>" . $missions_arr[intval($task_num) - 1]['prize'] . "</li>";
    }
    $msg .= "</ul>
If you did not yet complete all the missions, you can come back to www.chanukahchallenge.com and continue filling out more missions to win more great prizes.
<br /><br />
Tzivos Hashem International wishes you a HAPPY CHANUKAH!";

    if ($new_account) {
        $msg .= "<br /><br />
<strong>Did you know?</strong><br />
Tzivos Hashem has missions for you all year round, for which you can win prizes and be promoted in rank to become a general in Hashem's army. 
<a href='http://mashpia.com/mobile'>Sign up today!</a> 
";
    }

    // To send HTML mail, the Content-type header must be set
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    // Additional headers
    $headers[] = 'From: Tzivos Hashem <cth@tzivoshashem.org>';
    $headers[] = 'Reply-To: Tzivos Hashem <cth@tzivoshashem.org>';
    @mail($to, $subject, $msg, implode("\r\n", $headers));

    // encrypt admin ID
    require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
    $admin = encrypt_decrypt('encrypt', $admin_id);

    echo json_encode([
        'success'   => true,
        'admin'     => $admin
    ]);
} else {
    exit;
    if (!$success && $new_account) $error = 'There was an error creating your account.';
    else if (!$success && !$new_account) $error = 'Incorrect Serial Number.';
    else if ($success && !$marked) $error = 'There was an error saving your missions.';
    echo json_encode([
        'success'   => false,
        'error'     => $error
    ]);
}
