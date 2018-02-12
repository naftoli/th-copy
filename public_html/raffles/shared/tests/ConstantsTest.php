<?php

// TODO write unit tests

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'vendor/autoload.php';

require_once("public_html/raffles/shared/classes/Constants.php");

use raffles\shared\Constants as Constants;

use PHPUnit\Framework\TestCase;

class ConstantsTest extends TestCase {
    // test the max winners array
    public function testGetRaffleSchoolMaxWinners() {
        $subject = Constants::get_raffle_school_max_winners();
        
        $this->assertEquals(100, array_sum($subject), "Assert that the sum of all the max amounts is 100");
        $this->assertEquals(0, $subject[82], "Assert that the test school (id 82) has 0 prizes");
    }
    // tets the weekly task requirment total
    public function testGetWeeklyTaskRequirment() {
        $subject = Constants::get_weekly_task_requirment();
        
        $this->assertEquals(5, $subject, "Total Weekly Task Requirment is set to 5");
    }
    // tets the monthly task requirment total
    public function testGetMonthlyTaskRequirment() {
        $subject = Constants::get_monthly_task_requirment();
        
        $this->assertEquals(20, $subject, "Total Monthly Task Requirment is set to 20");
    }
    
}