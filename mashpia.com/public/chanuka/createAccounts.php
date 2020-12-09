<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

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

$fields = ['new_account', 'serial_number', 'first_name', 'last_name', 'email_address', 'tasks'];
foreach ($fields as $field) {
    $$field = mysql_real_escape_string($_POST['data'][$field]);
}

$user_id = 0;
$success = true;
$marked = true;

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
        $child = new NewSoldier($parent, $first_name, $last_name, '', '', '0', '0', '', '');
        $child->setSchoolType( 50 ); // indicates new child that needs to have type set when logging into mobile site
        if (
            $child->create()
        ) {
            $user_id = $child->getUserID();
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
    foreach ($tasks as $idx => $task) {
        $qrys[] = "insert into chanuka_missions 
                    set user_id = " . $user_id . ", 
                    task = '" . $missions_arr[$idx]['name'] . "', 
                    task_num = " . intval($task)  + 1;
    }
    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($qrys as $qry) {
        if (!mysql_query($qrys)) {
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
    echo json_encode([
        'success'   => true
    ]);
} else {
    if (!$success && $new_account) $error = 'There was an error creating your account.';
    else if (!$success && !$new_account) $error = 'Incorrect Serial Number.';
    else if ($success && !$marked) $error = 'There was an error saving your missions.';
    echo json_encode([
        'success'   => false,
        'error'     => $error
    ]);
}
