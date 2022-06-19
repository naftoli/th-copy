<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/classes/recruits.php';
$r = new Recruits(7745260);
$error = $r->sendEmail('Michel Rapoport');
if (intval($error) != 1) echo $error;