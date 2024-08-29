<?php
require 'db.php';
require 'class.globalSettings.php';
$sm = calculateSM( GlobalSettings::getRegistrationYear() );
echo "<pre>"; print_r($sm); echo "</pre>";