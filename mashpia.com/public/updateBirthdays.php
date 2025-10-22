<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);

require_once 'db.php';
require_once 'api/header/db.php';
require_once 'class.heDob.php';
require_once 'class.birthdayEn.php';
require_once 'class.birthdayYi.php';
require_once 'class.birthdayHe.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getBirthdayYear();

function needsUpdate($user_id) {
    global $MASHPIA_DB;
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            start_date
        FROM
            birthdays b
                JOIN
            date_tasks_missions dtm USING (date_tasks_mission_id)
        WHERE
            b.user_id = :user 
        GROUP BY start_date
    ");
    $stmt->execute([
        'user' => $user_id
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $start_date = $result['start_date'];
    // figure out jewish year from start_date
    $info = explode('/', jdtojewish($start_date));
    $jewish_year = intval($info[2]);
    if ($jewish_year > 5786) return true;
    return false;
}

$users = [];
$sql = "select user_id from users u 
        join user_registration ur using (user_id) 
        where user_registered > 0 
        and ur.year = " . $year;
$result = mysql_query($sql);
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row['user_id'];
}

$updated = 0;
foreach ($users as $user_id) {
    if (needsUpdate($user_id)) {
        $h = new HeDob( $user_id, true );
        $h->setHeDob();
        // create birthday missions
        $b = new BirthdayEn( $user_id );
        $b->setBirthday();
        $bi = new BirthdayYi( $user_id );
        $bi->setBirthday();
        $bh = new BirthdayHe( $user_id );
        $bh->setBirthday();
        $updated++;
    }
}
echo "Updated $updated users";
?>