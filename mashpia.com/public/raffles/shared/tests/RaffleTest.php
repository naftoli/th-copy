<?php

// TODO write unit tests

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'vendor/autoload.php';

require_once("public_html/raffles/shared/classes/Raffle.php");
require_once("public_html/raffles/shared/tests/DBMock.php");

use raffles\weekly\Raffle as Raffle;
use \DateTime;

use PHPUnit\Framework\TestCase;

class TotalWeeklyTasksTest extends TestCase {
    
    protected $subject;
    
    public function testConstructor() {
        $subject = new Raffle();
        
        $this->assertEquals([], $subject->prizes, "Creates an empty prizes array");
        $this->assertEquals(0, $subject->prize_count, "sets the prize_count to 0");
        $this->assertEquals([], $subject->eligable_user_ids, "Creates an empty eligable_user_ids array");
    }
    // test the loading function
    public function testLoad() {
        
        $db_mock = new DBMock();
        $db_mock->add_single_result("SELECT * FROM raffles WHERE raffle_id = 1 LIMIT 1",
                                    ['raffle_id' => 1, 'name' => "test", "run_date" => "10/10/2017", "start_date" => 2458064, "end_date" => 2458070,
                                     'type' => "test", 'show_on_mobile' => "0"]);
        $subject = Raffle::load(1, $db_mock);
        
        $this->assertInstanceOf('raffles\weekly\Raffle', $subject);
        $this->assertEquals(1, $subject->raffle_id, "Sets internal raffle id");
        $this->assertInstanceOf('DateTime', $subject->run_date, "Casts the run_date to a datetime instance");
        $this->assertEquals("test", $subject->type, "It sets the type to the value from the database");
        
        // test a bad load
        $db_mock->clear_results("SELECT * FROM raffles WHERE raffle_id = 1 LIMIT 1"); // clear the results from the mock
        $subject = Raffle::load(1, $db_mock);
        
        $this->assertEquals(false, $subject, "When there is no record it just returns false");
    }
    // test the load from row function
    public function testLoadFromRow() {
        $row = ['raffle_id' => 5, 'date_ran' => "10/10/2017", 'date_created' => "10/11/2017"];
        // load the subject
        $subject = Raffle::loadFromRow($row);
        // test the results
        $this->assertEquals(5, $subject->raffle_id, "Sets internal raffle id");
        $this->assertInstanceOf('DateTime', $subject->date_created, "Casts the date_created to a datetime instance");
        $this->assertInstanceOf('DateTime', $subject->date_ran, "Casts the date_ran to a datetime instance");
    }
    
}