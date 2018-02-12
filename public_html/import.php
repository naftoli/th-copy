#!/usr/bin/php
<?
ini_set('default_charset', 'UTF-8');
mb_internal_encoding("UTF-8");
error_reporting(E_ALL);

if($argc != 3) exit("Enter school_id and school_type_id\n");
$school_id = $argv[1];
$school_type_id = $argv[2];

require('db.php');
$classes = array();
$usernames = mysql_fetch_column(mq('SELECT LOWER(username) username, user_id FROM users'));

while ($line = fgets(STDIN)){
  $data = mb_split("\t",$line);

  $class = trim($data[1]);
  $sub = trim($data[2]);
  $teacher = trim($data[3]);

  $first = trim($data[5]);
  $last = trim($data[4]);
  $first_he = trim($data[6]);
  $last_he = trim($data[7]);
  $address1 = trim($data[11]);
  $address2 = trim($data[12]);
  $city = trim($data[13]);
  $state = trim($data[14]);
  $postal = trim($data[15]);
  $country = trim($data[16]);
  $phone = trim($data[17]);
  $gender = trim($data[8]);
  $dob = trim($data[9]);
  $dob_he = trim($data[10]);
  $email = trim($data[18]);

  if(!isset($classes["$class-$sub"])) {
    mq("INSERT INTO classes (school_id, class_grade, class_sub, class_teacher, default_level) VALUES ($school_id, " . ms($class) . ', ' . ms($sub) . ', ' . ms($teacher) .', 1)');
    $classes["$class-$sub"] = mysql_insert_id();
  }

  if(mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) trigger_error('could not get lock', E_USER_ERROR);
  $count = 0;
  do {
    if($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
    $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
  } while(mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);

  mq("INSERT INTO users (user_code, username, email, first, last, first_he, last_he, lang, school_type_id, school_id, class_id, user_serial, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, gender, dob, dob_he) VALUES ($user_code, " . ms(getusername($first,$last)) . ',' . ms($email) . ', ' . ms($first) . ',' . ms($last) . ',' . ms($first_he) . ',' . ms($last_he) . ", 'en', $school_type_id, $school_id, {$classes["$class-$sub"]}" . ',' . mysql_result(mq("(SELECT IFNULL(MAX(user_serial), 0)+1 FROM users users_max)"), 0) . ',' . ms($address1) . ',' . ms($address2) . ',' . ms($city) . ',' . ms($state) . ',' . ms($postal) . ',' . ms($country) . ',' . ms($phone) . ',' . nullif_ms($gender, '') . ',' . nullif_ms($dob, '') . ',' . ms($dob_he) . ')');
  $new_user_id = mysql_result(mq("SELECT LAST_INSERT_ID()"), 0);

  mq("SELECT RELEASE_LOCK('users')");

  mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, $new_user_id user_id, " . unixtojd() . ' date_promoted FROM ranks WHERE medals_required <= 0');
}

function getusername($first, $last) {
  global $usernames;
  $username = mb_strtolower(mb_substr($first,0,1)) . preg_replace('/\P{L}/u', '', mb_strtolower($last));
  $count = '';
  while(isset($usernames[$username.$count])) $count++;
  $usernames[$username.$count] = 1;
  return $username.$count;
}
?>
