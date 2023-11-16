<?php
//ini_set('display_errors', 1);
require_once __DIR__ . '/../api/header/db.php';
require_once __DIR__ . '/../class.globalSettings.php';
/**
 * Class ChidonTests
 * various functions for working with chidon tests
 */
class ChidonTests
{
    private $year;
    private $db;
    private $children;
    private $scores;
    private $types;
    private $testQuestions;
    private $marks;
    private $genderOnly;
    private $start;
    private $end;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonRegYear();
        $this->children = [];
        $this->scores = [];
        $this->marks = [];
        $this->types = [
            'maven' => 'Yesod',
            'pro'   => 'Yediah',
            'expert'=> 'Havonah',
            'genius'=> 'Iyun'
        ];
        // hardcode number of questions per test type
        $this->testQuestions = [
            'maven' => 10,
            'pro'   => 10,
            'expert'=> 20,
            'genius'=> 10
        ];
        $this->genderOnly = false;
    }

    public function setLimmudDates( $start, $end ) {
        $this->start = $start;
        $this->end = $end;
    }

    public function getTypes() {
        return $this->types;
    }

    public function getTestQuestions() {
        return $this->testQuestions;
    }

    public function setGender($gender) {
        $this->genderOnly = $gender;
    }

    public function setStudents($school_id = 0, $class_id = 0, $user_id = 0) {
        $qry = "
            SELECT 
                tc.th_chidon_id, tc.user_id, tc.test_type, tc.parent_id, tc.khk_reg, tc.school_rep, tc.regional_rep, 
                tc.intl_rep, tc.school_team, tc.regional_team, tc.intl_team, tc.reward_type, tc.date_paid, 
                tci.highest_track, 
                u.first, u.last, u.gender, u.user_serial, 
                c.class_id, c.class_grade, c.class_sub, 
                s.school_id, s.school_name 
            FROM
                th_chidon tc
                    JOIN
                users u USING (user_id)
                    JOIN
                schools s on s.school_id = u.school_id
                    JOIN
                classes c ON c.class_id = u.class_id 
                    LEFT JOIN 
                th_chidon_info tci on tc.year = tci.year and tc.user_id = tci.user_id  
            WHERE
                tc.year = :year 
        ";
        if ($school_id > 0) $qry .= " AND u.school_id = :school";
        if ($class_id > 0) $qry .= " AND u.class_id = :grade";
        if ($user_id > 0) $qry .= " AND u.user_id = :user";
        if ($this->genderOnly) $qry .= " AND u.gender = :gender";
        // order by
        $qry .= " ORDER BY school_name, class_grade, class_sub, last, first";
//        echo $qry . "<br />";
        $stmt = $this->db->prepare($qry);
        if ($this->genderOnly) {
            if ($school_id > 0 && $class_id > 0 && $user_id > 0) {
                $res = $stmt->execute([
                    ':year' => $this->year,
                    ':school'   => $school_id,
                    ':grade'    => $class_id,
                    ':user'     => $user_id,
                    ':gender'   => strtoupper($this->genderOnly)
                ]);
            } else if ($school_id > 0 && $class_id > 0) {
                $res = $stmt->execute([
                    ':year' => $this->year,
                    ':school'   => $school_id,
                    ':grade'    => $class_id,
                    ':gender'   => strtoupper($this->genderOnly)
                ]);
            } else if ($school_id > 0) {
                $res = $stmt->execute([
                    ':year' => $this->year,
                    ':school'   => $school_id,
                    ':gender'   => strtoupper($this->genderOnly)
                ]);
            } else {
                $res = $stmt->execute([
                    ':year' => $this->year,
                    ':gender'   => strtoupper($this->genderOnly)
                ]);
            }
        } else {
            if ($school_id > 0 && $class_id > 0 && $user_id > 0) {
                $res = $stmt->execute([
                    ':year' => $this->year,
                    ':school'   => $school_id,
                    ':grade'    => $class_id,
                    ':user'     => $user_id
                ]);
            } else if ($school_id > 0 && $class_id > 0) {
                $res = $stmt->execute([
                    ':year' => $this->year,
                    ':school'   => $school_id,
                    ':grade'    => $class_id
                ]);
            } else if ($school_id > 0) {
                $res = $stmt->execute([
                    ':year' => $this->year,
                    ':school'   => $school_id
                ]);
            } else {
                $res = $stmt->execute([':year' => $this->year]);
            }
        }
        if ($res) {
            $this->children = $stmt->fetchAll();
        }
    }

    public function getStudents() {
        return $this->children;
    }

    public function setScores() {
        $stmt = $this->db->prepare("
            SELECT 
                *
            FROM
                th_chidon_marks
            WHERE
                th_chidon_id = :id AND test_type = :type
        ");
        foreach ($this->children as $child) {
            $id = $child['th_chidon_id'];
            foreach ($this->types as $type => $desc) {
                $res = $stmt->execute([
                    ':id'   => $id,
                    ':type' => $type
                ]);
                if ($res) {
                    $rows = $stmt->fetchAll();
                    foreach ($rows as $row)
                        $this->scores[$id][$row['test_number']][$type] = $row['answered_correctly'];
                }
            }
        }
//        echo "<pre>"; print_r($this->scores); echo "</pre>";
    }

    public function getScores() {
        return $this->scores;
    }

    public function insertScores($info) {
        $success = true;
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO th_chidon_marks 
            SET 
                th_chidon_id = :id, 
                test_type = :type, 
                test_number = :number, 
                total_questions = :questions, 
                answered_correctly = :answered
            ON DUPLICATE KEY UPDATE 
                answered_correctly = :answered
        ");
        foreach ($info as $id => $more) {
            foreach ($more as $testNum => $details) {
                foreach ($this->testQuestions as $type => $questions) {
//                    if ($details[$type] > 0) {
                        if (! $stmt->execute([
                                ':id' => $id,
                                ':type' => $type,
                                ':number' => $testNum,
                                ':questions' => $questions,
                                ':answered' => $details[$type]
                            ])) {
                            $success = false;
                        }
//                    }
                }
            }
        }
        return $success;
    }

    public function setTestTypes($types) {
        $stmt = $this->db->prepare("
            UPDATE th_chidon 
            SET 
                test_type = :type 
            WHERE 
                th_chidon_id = :id
        ");
        foreach ($types as $id => $type) {
            $stmt->execute([
                ':id'   => $id,
                ':type' => $type
            ]);
        }
    }

    public function setRewardTypes($types) {
        $stmt = $this->db->prepare("
            UPDATE th_chidon 
            SET 
                reward_type = :type 
            WHERE 
                th_chidon_id = :id
        ");
        foreach ($types as $id => $type) {
            $stmt->execute([
                ':id'   => $id,
                ':type' => $type
            ]);
        }
    }

    public function calculateMarks() {
        foreach ($this->scores as $id => $more) {
            foreach ($more as $testNum => $details) {
                foreach ($this->testQuestions as $type => $questions) {
                    $mark = floatval($details[$type] / $questions);
                    $this->marks[$id][$testNum][$type] = $mark * 100;
                }
            }
        }
    }

    public function getMarks() {
        return $this->marks;
    }

    public function getHighestTrackEligible( $marks, $user_id ) {
        foreach ($this->types as $type => $value) {
            $avgs[$type] = 0;
        }

        if (count($marks) > 0) {
            foreach ($marks as $num => $more) {
                foreach ($more as $type => $mark) {
                    $avgs[$type] += $mark;
                }
            }

            $highest = 'maven';
            $passingAvgs = $this->getPassingAvgs($user_id);
            foreach ($avgs as $type => $avg) {
                $avgs[$type] /= $num;
                $avg = $avgs[$type];
                if ($avg >= $passingAvgs[$type]) $highest = $type;
            }
            return $highest;
        }
        return '';
    }

    public function getLearned( $dates, $untilToday = false ) {
        $dateArr = explode('/', $dates[0]);
        $start = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
        if ($untilToday) $end = unixtojd();
        else {
            $dateArr = explode('/', $dates[count($dates)-1]);
            $end = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
        }
        $stmt = $this->db->prepare("
            SELECT 
                dtm.* 
            FROM
                date_tasks_marks dtm
                    JOIN
                date_tasks dt USING (date_task_id)
            WHERE
                dt.grid_id = 20010 
                    AND dtm.mark_date >= :start
                    AND dtm.mark_date <= :end 
                    AND done_qty > 0");
        $stmt->execute([
            ':start'    => $start,
            ':end'      => $end
        ]);
        return $stmt->fetchAll();
    }

    public function getTotalMinutesLearned( $user_id, $dates, $untilToday = false ) {
        $dateArr = explode('/', $dates[0]);
        $start = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
        if ($untilToday) $end = unixtojd();
        else {
            $dateArr = explode('/', $dates[count($dates)-1]);
            $end = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
        }
        $stmt = $this->db->prepare("
            SELECT 
                IFNULL(SUM(done_qty), 0) AS total
            FROM
                date_tasks_marks dtm
                    JOIN
                date_tasks dt USING (date_task_id)
            WHERE
                dt.grid_id = 20010 
                    AND dtm.mark_date >= :start
                    AND dtm.mark_date <= :end
                    AND user_id = :user");
        $stmt->execute([
            ':start'    => $start,
            ':end'      => $end,
            ':user'     => $user_id
        ]);
//        $stmt->debugDumpParams();
        return $stmt->fetch()['total'];
    }

    public function getTotalDaysLearned( $user_id, $dates, $untilToday = false ) {
        $dateArr = explode('/', $dates[0]);
        $start = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
        if ($untilToday) $end = unixtojd();
        else {
            $dateArr = explode('/', $dates[count($dates)-1]);
            $end = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
        }
        $stmt = $this->db->prepare("
            SELECT 
                IFNULL(count(*), 0) AS total
            FROM
                date_tasks_marks dtm
                    JOIN
                date_tasks dt USING (date_task_id)
                    JOIN
                date_tasks_missions dtmm USING (date_tasks_mission_id)
            WHERE
                dt.grid_id = 20010 
                    AND dtmm.start_date >= :start
                    AND dtmm.end_date <= :end
                    AND user_id = :user 
                    AND dtm.done_qty > 0");
        $stmt->execute([
            ':start'    => $start,
            ':end'      => $end,
            ':user'     => $user_id
        ]);
        return $stmt->fetch()['total'];
    }

    public function getLimmudInfo($user_id) {
        $stmt = $this->db->prepare("
            SELECT
                u.user_id, 
                u.user_serial,
                u.first,
                u.last, 
                c.school_id, 
                c.class_id, 
                c.class_grade,
                c.class_sub, 
                tc.th_chidon_id, 
                tc.test_type 
            FROM
                users u
                    JOIN
                th_chidon tc USING (user_id)
                    JOIN
                classes c ON c.class_id = u.class_id
            WHERE
                tc.year = :year AND u.user_id = :user
        ");
        $stmt->execute([
            'year'  => $this->year,
            'user'  => $user_id
        ]);
        return $stmt->fetch();
    }

    public function getLimmudDetails($user_id, $dates) {
        $dateArr = explode('/', $dates[0]);
        $start = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
        $dateArr = explode('/', $dates[count($dates)-1]);
        $end = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);

        $info = [];
        $stmt = $this->db->prepare("
            SELECT 
                dt.grid_id, dtm.*
            FROM
                date_tasks_marks dtm
                    JOIN
                date_tasks dt USING (date_task_id)
            WHERE
                dt.grid_id in (20010, 20011) AND user_id = :user 
                    AND mark_date >= :start 
                    AND mark_date <= :end
        ");
        $stmt->execute([
            'user'  => $user_id,
            'start' => $start,
            'end'   => $end
        ]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['mark_date']][$row['grid_id']][] = $row['done_qty'];
        }

        $details = [];
        foreach ($dates as $day => $date) {
            $details[$day]['minutes'] = [];
            $details[$day]['upToDate'] = false;

            $dateArr = explode('/', $date);
            $jd = gregoriantojd($dateArr[0], $dateArr[1], '20' . $dateArr[2]);
            if (isset($info[$jd][20010])) {
                // could have multiple entries
                // find largest amount
                $largest = 0;
                foreach ($info[$jd][20010] as $amount) {
                    if ($amount > $largest) $largest = $amount;
                }
                $details[$day]['minutes'] = $largest;
            }
            else $details[$day]['minutes'] = 0;
            if (isset($info[$jd][20011])) $details[$day]['upToDate'] = true;
        }

        return $details;
    }

    public function getHighestTrackPassed( $child, $numTests = 3 ) {
        $this->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
        $this->setScores();
        $this->calculateMarks();
        // get child mark info
        if (! isset($this->marks[$child['th_chidon_id']])) {
            return [
                'avg' => 0,
                'highest_track' => '',
                'highest_track_avg' => 0
            ];
        }
        $childMarkInfo = $this->marks[$child['th_chidon_id']];

        $marksPerType = [];
        $avgs = [];
        foreach ($this->types as $type => $val) {
            $marksPerType[$type] = 0;
            $avgs[$type] = 0;
        }

        for ($i = 1; $i <= $numTests; $i++) {
            if (isset($childMarkInfo[$i])) {
                foreach ($childMarkInfo[$i] as $type => $mark) {
                    if ($mark > 0) {
                        $marksPerType[$type] += $mark;
                    }
                }
            }
        }

        // needed avgs
//        $neededAvgs['maven']    = 80;
//        $neededAvgs['pro']      = 80;
//        $neededAvgs['expert']   = 80;
//        $neededAvgs['genius']   = 80;
        $passingAvgs = $this->getPassingAvgs($child['user_id']);

        // calculate avgs and highest type currently eligible for
        $highest_type = '';
        $highest_mark = 0;
        foreach ($this->types as $type => $val) {
            if ($numTests && ($marksPerType[$type])) {
                $avg = $marksPerType[$type] / $numTests;
                $avgs[$type] = $avg;
                if ($avg >= $passingAvgs[$type]) {
                    $highest_type = $type;
                    $highest_mark = $avg;
                }
            }
        }

        // check if child has a reward type set
        $sql = "select test_type, reward_type from th_chidon where th_chidon_id = " . $child['th_chidon_id'];
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $testType = $row['test_type'];
        $rewardType = $row['reward_type'];

        // check which type is higher
        if (!empty($highest_type) && $rewardType && $rewardType != 'highest track passed') {
            $indexes = array_keys($this->types);
            $key = array_search($testType, $indexes);
            $key1 = array_search($highest_type, $indexes);
            $key2 = array_search($rewardType, $indexes);
            // make sure child passed the track they are on
            if ($key1 >= $key && $key2 > $key1) $highest_type = $rewardType;
        }

        $markInfo = [];
        $markInfo['avg'] = $avgs[$child['test_type']] ?? 0;
        $markInfo['highest_track'] = $highest_type;
        $markInfo['highest_track_avg'] = round($highest_mark, 2);
        $markInfo['test_type'] = $testType;
        $markInfo['reward_type'] = $rewardType;

        return $markInfo;
    }

    public function getPrevHighestTrack($year, $user_id) {
        $sql = "select * from th_chidon_info where year = " . $year . " and user_id = " . $user_id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['highest_track'];
    }

    private function getPassingAvgs($user_id, $type = '') {
        $table = 'chidon_passing_avgs';
        if ($type == 'finals') $table = 'chidon_final_passing_avgs';

        $stmt = $this->db->prepare("
            select * from :table 
            where year = :year 
            and (
                user_id = :user 
                or school_id = (
                    select school_id from users where user_id = :user
                )
                or class_id = (
                    select class_id from users where user_id = :user
                )
        ");
        $stmt->execute([
            ':table' => $table,
            ':year' => $this->year,
            ':user' => $user_id
        ]);

        $avgs = [];
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if ($row['user_id'] > 0) $avgs['user'][$row['track']] = $row['avg'];
            else if ($row['class)id'] > 0) $avgs['class'][$row['track']] = $row['avg'];
            else if ($row['school_id'] > 0) $avgs['school'][$row['track']] = $row['avg'];
        }
        // if there's no setting, default to 80
        $passingAvgs = [];
        foreach ($this->types as $type => $desc) {
            $passingAvgs[$type] = $avgs['user'][$type] ?? $avgs['class'][$type] ?? $avgs['school'][$type] ?? 80;
        }
        return $passingAvgs;
    }

    private function getLevel($user_id, $type = '')
    {
        $levels = [];
        foreach (['tests', 'finals'] as $test_type) {
            if ($type && $type != $test_type) continue;
            $stmt = $this->db->prepare("
                select * from chidon_test_levels 
                where year = :year 
                and test_type = :type 
                and (
                    user_id = :user 
                    or school_id = (
                        select school_id from users where user_id = :user
                    )
                    or class_id = (
                        select class_id from users where user_id = :user
                    )
                )
            ");
            $stmt->execute([
                ':year' => $this->year,
                ':type' => $type,
                ':user' => $user_id
            ]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                if ($row['user_id'] > 0) $levels['user'][$row['test_type']] = $row['test_level'];
                else if ($row['class_id'] > 0) $levels['class'][$row['test_type']] = $row['test_level'];
                else if ($row['school_id'] > 0) $levels['school'][$row['test_type']] = $row['test_level'];
            }
        }

        // if there's no setting, default to 2
        $test_level = $levels['user']['tests'] ?? $levels['class']['tests'] ?? $levels['school']['tests'] ?? 2;
        $final_level = $levels['user']['finals'] ?? $levels['class']['finals'] ?? $levels['school']['finals'] ?? 2;

        if ($type == 'tests') return $test_level;
        else if ($type == 'finals') return $final_level;
        else {
            return [
                'test_level' => $test_level,
                'final_level' => $final_level
            ];
        }
    }

    public function getSettings($school_id, $class_id, $user_id) {
        $info = [];
        foreach(['passing_avgs', 'final_passing_avgs', 'test_levels'] as $table) {
            $table = 'chidon_' . $table;
            if ($user_id > 0) {
                $stmt = $this->db->prepare("
                    SELECT * FROM `$table` WHERE year = :year 
                    AND user_id = :id
                ");
                $stmt->execute([
                    ':year' => $this->year,
                    ':id'   => $user_id
                ]);
            } else if ($class_id > 0) {
                $stmt = $this->db->prepare("
                    SELECT * FROM `$table` WHERE year = :year 
                    AND class_id = :id
                ");
                $stmt->execute([
                    ':year' => $this->year,
                    ':id'   => $class_id
                ]);
            } else if ($school_id > 0) {
                $stmt = $this->db->prepare("
                    SELECT * FROM `$table` WHERE year = :year 
                    AND school_id = :id
                ");
                $stmt->execute([
                    ':year' => $this->year,
                    ':id'   => $school_id
                ]);
            }
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                if ($table == 'test_levels') {
                    if ($row['user_id'] > 0) $info[$row['user_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                    else if ($row['class_id'] > 0) $info[$row['class_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                    else if ($row['school_id'] > 0) $info[$row['school_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                } else {
                    if ($row['user_id'] > 0) $info[$row['user_id']][$table][$row['track']] = $row['avg'];
                    else if ($row['class_id'] > 0) $info[$row['class_id']][$table][$row['track']] = $row['avg'];
                    else if ($row['school_id'] > 0) $info[$row['school_id']][$table][$row['track']] = $row['avg'];
                }
            }
        }

        return $info;
    }

    public function getSettingsForReport($school) {
        $info = [];
        foreach(['passing_avgs', 'final_passing_avgs', 'test_levels'] as $table) {
            $table_name = 'chidon_' . $table;
            $stmt = $this->db->prepare("
                SELECT * FROM `$table_name` WHERE year = :year 
                AND (
                    school_id = :school 
                    OR class_id IN (
                        SELECT class_id FROM classes WHERE school_id = :school AND class_era = 0
                    )
                    OR user_id IN (
                        SELECT user_id FROM users WHERE school_id = :school
                    )
                )
            ");
            $stmt->execute([
                ':year'     => $this->year,
                ':school'   => $school
            ]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                if ($table == 'test_levels') {
                    if ($row['user_id'] > 0) $info['user'][$row['user_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                    else if ($row['class_id'] > 0) $info['class'][$row['class_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                    else if ($row['school_id'] > 0) $info['school'][$row['school_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                } else {
                    if ($row['user_id'] > 0) $info['user'][$row['user_id']][$table][$row['track']] = $row['avg'];
                    else if ($row['class_id'] > 0) $info['class'][$row['class_id']][$table][$row['track']] = $row['avg'];
                    else if ($row['school_id'] > 0) $info['school'][$row['school_id']][$table][$row['track']] = $row['avg'];
                }
            }
        }
        echo "<pre>"; print_r($info); echo "</pre>";

        $details = [];
        if (isset($info['user'])) {
            $user_ids = array_keys($info['user']);
            $this->db->query("
                SELECT * FROM users u 
                JOIN classes c on c.class_id = u.class_id 
                WHERE user_id IN (" . implode(',', $user_ids) . ") 
            ");
            $rows = $this->db->fetchAll();
            foreach ($rows as $row) {
                $details['user'][$row['user_id']] = $row;
            }
        }

        if (isset($info['class'])) {
            $class_ids = array_keys($info['class']);
            $this->db->query("
                SELECT * FROM classes WHERE class_id IN (" . implode(',', $class_ids) . ")
            ");
            $rows = $this->db->fetchAll();
            foreach ($rows as $row) {
                $details['class'][$row['class_id']] = $row;
            }
        }
        echo "<pre>"; print_r($details); echo "</pre>";

        return [
            'settings'  => $info,
            'details'   => $details
        ];
    }
}

class KHK {
    public static $khkFee = 18;

    /**
     * Algorithm to determine if child is eligible for khk registration / tests
     * takes array of user ids
     * and returns two arrays
     * one is whether that child is eligible for khk
     * the other is the details of which yr the child was or wasn't eligible
     */
    public static function getKHKEligibility( array $ids, $year = 0, $numYrs = 4 ) {
        // yr that we don't check registration but rather check highest track passed
        $rollover = 5782;

        // figure out which years we need to check
        $years = [];
        $curYr = $year > 0 ? $year : GlobalSettings::getChidonRegYear();
        $i = $numYrs;
        $yr = $curYr - $i;
        for (; $i > 0; $i--) {
            $years[] = $yr++;
        }

        foreach ($ids as $id) {
            $details[$id] = [];
            foreach ($years as $yr) {
                if ($yr >= $rollover) {
                    // check highest track passed
                    $sql = "select highest_track from th_chidon_info where user_id = " . $id . " and year = " . $yr;
                    $result = mysql_query($sql);
                    if (mysql_num_rows($result) > 0) {
                        $highest_track = mysql_fetch_assoc($result)['highest_track'];
                        // make sure child is at least on the yediah track (not on 'yesod')
                        if ($highest_track == 'yesod') $details[$id][$yr] = false;
                        else $details[$id][$yr] = true;
                    }
                    else $details[$id][$yr] = false;
                } else {
                    $sql = "select * from th_chidon where date_paid > 0 and user_id = " . $id . " and year = " . $yr;
                    $result = mysql_query($sql);
                    if (mysql_num_rows($result) == 0) $details[$id][$yr] = false;
                    else $details[$id][$yr] = true;
                }
            }
        }

        // for each child find out if final result is eligible or not
        foreach ($details as $id => $yrs) {
            $khk[$id] = true;

            // check if bc indicated that child is eligible
            $sql = "select khk_eligible from users where user_id = " . $id;
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            if ($row['khk_eligible'] == 1) {
                // no need to check all years
                continue;
            }

            foreach ($yrs as $eligible) {
                if (! $eligible) {
                    $khk[$id] = false;
                    break;
                }
            }
        }

        return [$khk, $details];
    }
}