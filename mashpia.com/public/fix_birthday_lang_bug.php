<?php
ini_set('display_errors', 1);
require 'db.php';
require 'class.birthday.php';
require 'class.birthdayYi.php';

$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$updated = 0;
foreach ($users as $user) {
    $sql = "select * from birthdays b
            join date_tasks_missions dtm using (date_tasks_mission_id) 
            where b.user_id = " . $user;
    $result = mysql_query($sql);
    $num = mysql_num_rows($result);
    if ($num > 0) {
        // find out if missions are after rosh hashana and if there's both english and yiddish
        $missions = array();
        while ($row = mysql_fetch_assoc($result)) {
            $lang = $row['lang_id'];
            $start = $row['start_date'];
            if ($start >= 2457662) {
                $missions[$lang] = $row;
            }
        }
        if (array_key_exists(1, $missions) && !array_key_exists(2, $missions)) {
            // create yiddish mission
            $by = new BirthdayYi($user);
            $by->setBirthday();
            $updated++;
        } else if (array_key_exists(2, $missions) && !array_key_exists(1, $missions)) {
            // create english mission
            $b = new Birthday($user);
            $b->setBirthday();
            $updated++;
        }
    } else {
        $b = new Birthday($user);
        $b->setBirthday();
        $by = new BirthdayYi($user);
        $by->setBirthday();
        $updated++;
    }
}
echo "Updated: " . $updated;