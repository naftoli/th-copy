<?php

require(dirname(__FILE__).'/../../db.php');
require_once( dirname(__FILE__) . "/../yearly/classes/YearlyRaffle.php" );

echo "Running refresh_yearly_cache.php on ".date('m/d/Y H:m:s e')."\n";

use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
$yearly_raffle = new YearlyRaffle();

$start = microtime(true);
echo "Updating Cache...\n";

$yearly_raffle->get_eligible_users( true );

$end = microtime(true);
echo 'Updating Cache compleated in ' . ($end-$start) . ' seconds.';