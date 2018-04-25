<?php

require(dirname(__FILE__).'/../../db.php');
require_once( dirname(__FILE__) . "/../yearly/classes/YearlyRaffle.php" );

use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
$yearly_raffle = new YearlyRaffle();
$yearly_raffle->get_eligible_users( true );