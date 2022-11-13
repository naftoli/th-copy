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
        $this->year = GlobalSettings::getChidonYear();
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
                tc.th_chidon_id, tc.user_id, tc.test_type, tc.parent_id, tc.khk_reg, tc.school_rep, tc.reward_type, tc.date_paid,  
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
                        $stmt->execute([
                            ':id' => $id,
                            ':type' => $type,
                            ':number' => $testNum,
                            ':questions' => $questions,
                            ':answered' => $details[$type]
                        ]);
//                    }
                }
            }
        }
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
                    switch ($type) {
                        case 'maven':
                            $mark = floatval($details[$type] / $questions);
                            break;
                        case 'pro':
                            $mark = floatval(($details['maven'] + $details[$type]) / ($this->testQuestions['maven'] + $questions));
                            break;
                        case 'expert':
                            $mark = floatval(($details['maven'] + $details['pro'] + $details[$type]) / ($this->testQuestions['maven'] + $this->testQuestions['pro'] + $questions));
                            break;
                        case 'genius':
                            $mark = floatval(($details['maven'] + $details['pro'] + $details['expert'] + $details[$type]) /
                                ($this->testQuestions['maven'] + $this->testQuestions['pro'] + $this->testQuestions['expert'] + $questions));
                            break;
                    }
                    $this->marks[$id][$testNum][$type] = $mark * 100;
                }
//                // eligibility for trophy can come in this way as well
//                $this->marks[$id][$testNum]['trophy_extra'] = floatval(
//                    ($details['pro'] + $details['expert'] + $details['trophy']) /
//                    ($this->testQuestions['pro'] + $this->testQuestions['expert'] + $this->testQuestions['trophy'])
//                ) * 100;
            }
        }
    }

    public function getMarks() {
        return $this->marks;
    }

    public function getHighestTrackEligible( $marks ) {
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
            foreach ($avgs as $type => $avg) {
                $avgs[$type] /= $num;
                $avg = $avgs[$type];
                if ($type != 'genius') {
                    if ($avg >= 70) $highest = $type;
                } else {
                    if ($avg >= 90) $highest = $type;
                }
            }
            return $highest;
        }
        return '';
    }

    public function getLearned( $dates ) {
        $today = unixtojd();
        $dateArr = explode('/', $dates[1]);
        $start = gregoriantojd(intval($dateArr[0]), intval($dateArr[1]), intval($dateArr[2]));
        $dateArr = explode('/', $dates[count($dates)]);
        $end = gregoriantojd(intval($dateArr[0]), intval($dateArr[1]), intval($dateArr[2]));
        if ($today < $end) $end = $today;
        $stmt = $this->db->prepare("
            SELECT 
                dtm.* 
            FROM
                date_tasks_marks dtm
                    JOIN
                date_tasks dt USING (date_task_id)
                    JOIN
                date_tasks_missions dtmm USING (date_tasks_mission_id)
            WHERE
                dt.grid_id = 20010 
                    AND dtmm.start_date >= :start
                    AND dtmm.end_date <= :end");
        $stmt->execute([
            ':start'    => $start,
            ':end'      => $end
        ]);
        return $stmt->fetchAll();
    }

    public function getTotalMinutesLearned( $user_id, $dates ) {
        $today = unixtojd();
        $dateArr = explode('/', $dates[1]);
        $start = gregoriantojd(intval($dateArr[0]), intval($dateArr[1]), intval($dateArr[2]));
        $stmt = $this->db->prepare("
            SELECT 
                IFNULL(SUM(done_qty), 0) AS total
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
                    AND user_id = :user");
        $stmt->execute([
            ':start'    => $start,
            ':end'      => $today,
            ':user'     => $user_id
        ]);
        return $stmt->fetch()['total'];
    }

    public function getTotalDaysLearned( $user_id, $dates ) {
        $today = unixtojd();
        $dateArr = explode('/', $dates[1]);
        $start = gregoriantojd(intval($dateArr[0]), intval($dateArr[1]), intval($dateArr[2]));
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
                    AND user_id = :user");
        $stmt->execute([
            ':start'    => $start,
            ':end'      => $today,
            ':user'     => $user_id
        ]);
        return $stmt->fetch()['total'];
    }

    public function getLimmudInfo($user_id) {
        $stmt = $this->db->prepare("
            SELECT 
                u.user_serial,
                u.first,
                u.last,
                c.class_grade,
                c.class_sub,
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
        $details = [];
        $today = unixtojd();
        $dateArr = explode('/', $dates[1]);
        $start = gregoriantojd(intval($dateArr[0]), intval($dateArr[1]), intval($dateArr[2]));

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
            'end'   => $today
        ]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['mark_date']][$row['grid_id']] = $row['done_qty'];
        }

        foreach ($dates as $day => $date) {
            $dateArr = explode('/', $date);
            $jd = gregoriantojd(intval($dateArr[0]), intval($dateArr[1]), intval($dateArr[2]));
            if (isset($info[$jd][20010])) {
                $details[$day]['minutes'] = $info[$jd][20010];
                $details[$day]['upToDate'] = isset($info[$jd][20011]);
            } else {
                $details[$day]['minutes'] = 0;
                $details[$day]['upToDate'] = false;
            }
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

        // calculate avgs and highest type currently eligible for
        $highest_type = '';
        $highest_mark = 0;
        foreach ($this->types as $type => $val) {
            if ($numTests && ($marksPerType[$type])) {
                $avg = $marksPerType[$type] / $numTests;
                $avgs[$type] = $avg;
                if (($type != 'genius' && $avg >= 70) || ($type == 'genius' && $avg >= 90)) {
                    $highest_type = $type;
                    $highest_mark = $avg;
                }
            }
        }

        $markInfo = [];
        $markInfo['avg'] = $avgs[$child['test_type']] ?? 0;
        $markInfo['highest_track'] = $highest_type;
        $markInfo['highest_track_avg'] = round($highest_mark, 2);

        return $markInfo;
    }
}

class KHK {
    public static $khkFee = 18;

    /*
     * Algorithm to determine if child is eligible for khk registration / tests
     * takes array of user ids
     * and returns two arrays
     * one is whether that child is eligible for khk
     * the other is the details of which yr the child was or wasn't eligible
     */
    public static function getKHKEligibility( array $ids, $year = 0 ) {
        // yr that we don't check registration but rather check highest track passed
        $rollover = 5782;

        // figure out which years we need to check
        $years = [];
        $curYr = $year > 0 ? $year : GlobalSettings::getChidonRegYear();
        $i = 4;
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