<?php
chdir('../../../');
require 'db.php';
$user_id = mysql_real_escape_string($_POST['user']);

require_once 'class.campaignEnrollment.php';
$c = new CampaignEnrollment( $user_id );
$c->enroll();

require_once 'class.birthday.php';
$b = new Birthday( $user_id );
$b->setBirthday();
require_once 'class.birthdayYi.php';
$bi = new BirthdayYi( $user_id );
$bi->setBirthday();

//set dob for syncing with wp
require_once 'class.heDob.php';
$hdob = new HeDob( $user_id );
$hdob->setHeDob();