<?php
/**
 * gets future ranks for a school
 */

class FutureRanks {
    private $school_id;
    private $end_date;
    private $year;

    public function __construct($year, $school_id, $end_date) {
        $this->school_id = $school_id;
        $this->end_date = $end_date;
        $this->year = $year;
    }

    public function getFutureRanks() {
        $fm = new FutureMedals($this->year, $this->end_date, [$this->school_id]);
        $future_medals = $fm->getFutureMedals();


    }
}