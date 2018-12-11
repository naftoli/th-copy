<?php
require_once 'db.php';
require_once 'class.campaignEnrollment.php';
$c = new CampaignEnrollment(13743);
$c->enroll();