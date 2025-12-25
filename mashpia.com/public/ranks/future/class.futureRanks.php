<?php
/**
 * gets future ranks for a school
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/medals/future/class.futureMedals.php';

class FutureRanks {
    private $schools;
    private $end_date;
    private $year;
    private $rank_medals;

    public function __construct($year, $schools, $end_date) {
        $this->schools = $schools;
        $this->end_date = $end_date;
        $this->year = $year;
        $this->rank_medals = $this->getRankMedals();
    }

    private function getRankMedals() {
        global $MASHPIA_DB;
        $sql = "SELECT * FROM ranks";
        $stmt = $MASHPIA_DB->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $rank_medals[$row['rank_ord']] = $row['medals_required'];
        }
        return $rank_medals;
    }

    private function getCurrentMedals() {
        global $MASHPIA_DB;

        $current_medals = [];
        $sql = "SELECT user_id, MAX(medal_ord) as medal 
                FROM medal_marks mm
                JOIN users u ON u.user_id = mm.user_id
                WHERE u.school_id IN (" . implode(',', $this->schools) . ")
                AND u.user_registered IS NOT NULL 
                AND u.user_registered > '0000-00-00 00:00:00'
                GROUP BY user_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $current_medals[$row['user_id']] = $row['medal'];
        }
        return $current_medals;
    }

    public function getFutureRanks() {
        $fm = new FutureMedals($this->year, $this->end_date, $this->schools);
        $future_medals = $fm->getFutureMedals();
        $current_medals = $this->getCurrentMedals();
        echo "<pre>";
        print_r($future_medals);
        print_r($current_medals);
        echo "</pre>";
        exit;

        $future_ranks = [];
        foreach ($future_medals as $user_id => $medals) {
            $total_medals = $medals + $current_medals[$user_id];
            foreach ($this->rank_medals as $rank_ord => $medals_required) {
                if ($medals_required > $total_medals) {
                    break;
                }
            }
            $future_ranks[$user_id] = $rank_ord;
        }
        return $future_ranks;
    }
}