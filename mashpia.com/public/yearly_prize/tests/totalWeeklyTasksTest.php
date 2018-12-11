<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'vendor/autoload.php';

require_once("public_html/yearly_prize/class.totalWeeklyTasks.php");

use PHPUnit\Framework\TestCase;

class TotalWeeklyTasksTest extends TestCase {
    
    protected $subject;
    
    public function setUp(){
        $this->subject = new TotalWeeklyTasks(0, 2458068);
    }
    
    public function testConstructor() {
        $subject = new TotalWeeklyTasks(0, 2458068);
        // make sure it creates the week_dates array
        $this->assertInternalType('array', $subject->week_dates);
        // assert that it sets the dates correctly
        $this->assertEquals(2458047, $subject->start_date);
        $this->assertEquals(2458068, $subject->end_date);
    }
    
    public function testGetWeekDates() {
        $subject = new TotalWeeklyTasks(0, 2458068);
        // run the function
        $subject->get_week_dates();
        // assert that it finds 4 weeks
        $this->assertEquals(4, count($subject->week_dates));
        // test that it includes the week of the end date
        $this->assertEquals(2458068, $subject->week_dates[3][0]);
        $this->assertEquals(2458074, $subject->week_dates[3][1]);
    }
    
}

