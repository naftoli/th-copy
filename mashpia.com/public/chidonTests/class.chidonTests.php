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
    private $levels;
    private $dates;

    public function __construct($yr = 0) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = $yr > 0 ? $yr : GlobalSettings::getChidonYear();
        $this->children = [];
        $this->scores = [];
        $this->marks = [];
        $this->levels = [];
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
        $this->dates = [
            [
                1 => '09/16/2025',
                2 => '11/05/2025'
            ],
            [
                1 => '11/06/2025',
                2 => '12/10/2025'
            ],
            [
                1 => '12/11/2025',
                2 => '01/21/2026'
            ]
        ];
    }

    public function getDates() {
        return $this->dates;
    }

    public function figureOutTestNum() {
        $testNum = 0;
        $today = new DateTime();
        foreach ($this->dates as $test_num => $date) {
            $start = new DateTime($date[1]);
            $end = new DateTime($date[2]);
            if ($today >= $start && $today <= $end) {
                $testNum = $test_num + 1;
                break;
            }
        }
        if ($today > $end) $testNum = $test_num + 1;
        return $testNum;
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
                tc.*,
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
            WHERE
                tc.year = :year 
        ";
        if ($school_id > 0) $qry .= " AND u.school_id = :school";
        if ($class_id > 0) $qry .= " AND u.class_id = :grade";
        if ($user_id > 0) $qry .= " AND u.user_id = :user";
        if ($this->genderOnly) $qry .= " AND u.gender = :gender";
        // order by
        $qry .= " ORDER BY s.school_name, class_grade, class_sub, last, first";
//        echo $qry; exit;
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
        } else {
            $this->children = [];
        }
        // reset scores; marks
        $this->scores = [];
        $this->marks = [];
    }

    public function overrideStudents($students) {
        $this->children = $students;
        $this->scores = [];
        $this->marks = [];
    }

    public function getStudents() {
        return $this->children;
    }

    public function setPrevYrStudents($yr, $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                *
            FROM
                th_chidon tc 
                    JOIN
                users u ON u.user_id = tc.user_id
                    JOIN
                classes c ON c.class_id = u.class_id
                    JOIN
                schools s ON s.school_id = u.school_id
                    LEFT JOIN
                th_chidon_info tci ON tc.year = tci.year
                    AND tc.user_id = tci.user_id
            WHERE
                tc.year = :year AND u.school_id = :id 
            ORDER BY s.school_name, class_grade, class_sub, last, first
        ");
        $stmt->execute([
            ':year' => $yr,
            ':id'   => $school_id
        ]);
        $this->children = $stmt->fetchAll();
    }

    public function setScores() {
        $stmt = $this->db->prepare("
            SELECT 
                *
            FROM
                th_chidon_marks
            WHERE
                th_chidon_id = :id 
        ");
        foreach ($this->children as $child) {
            $id = $child['th_chidon_id'];
            $res = $stmt->execute([
                ':id' => $id,
            ]);
            if ($res) {
                $rows = $stmt->fetchAll();
                if (! empty($rows)) {
                    foreach ($rows as $row) {
                        $this->scores[$id][$row['test_number']][$row['test_type']] = $row['answered_correctly'];
                        if (! isset($this->levels[$id][$row['test_number']]))
                            $this->levels[$id][$row['test_number']] = $row['level'];
                    }
                }
            }
        }
    }

    public function getScores() {
        return $this->scores;
    }

    public function getLevels() {
        return $this->levels;
    }

    /**
     * @param $info
     * @param $levels
     * @return bool
     *
     * Inserts / updates scores and test levels into db based on the child's chidon ID
     * For iyun marks, if child is on level 1, it automatically changes mark to 0 before inserting / updating
     */
    public function insertScores($info, $levels) {
        $success = true;
        $stmtInsert = $this->db->prepare("
            INSERT IGNORE INTO th_chidon_marks 
            SET 
                th_chidon_id = :id, 
                test_type = :type, 
                test_number = :number, 
                total_questions = :questions, 
                answered_correctly = :answered, 
                level = :level
            ON DUPLICATE KEY UPDATE 
                answered_correctly = :answered, 
                level = :level
        ");
        foreach ($info as $id => $more) {
            foreach ($more as $testNum => $details) {
                foreach ($this->testQuestions as $type => $questions) {
                    // if (isset($details[$type]) && intval($details[$type]) > 0) {
                    // if we have a mark, then set it / update it
                    // if not, do nothing
                    if (isset($details[$type])) {
                        if ($type == 'genius' && $details[$type] > 0 && $levels[$id][$testNum] == 1) {
                            $details[$type] = 0; // set mark to 0 if it's genius track but level 1
                        }
                        if (! $stmtInsert->execute([
                                ':id' => $id,
                                ':type' => $type,
                                ':number' => $testNum,
                                ':questions' => $questions,
                                ':answered' => $details[$type],
                                ':level'    => isset($levels[$id][$testNum]) ? $levels[$id][$testNum] : 1
                            ])) {
                            $success = false;
                        }
                    } else if ($type == 'genius' && $levels[$id][$testNum] == 1) {
                        if (! $stmtInsert->execute([
                            ':id' => $id,
                            ':type' => $type,
                            ':number' => $testNum,
                            ':questions' => $questions,
                            ':answered' => 0,
                            ':level'    => 1
                        ])) {
                            $success = false;
                        }
                     }
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
                    if (isset($details[$type])) {
                        $mark = floatval($details[$type] / $questions);
                        $this->marks[$id][$testNum][$type] = round($mark * 100);
                    } else {
                        $this->marks[$id][$testNum][$type] = 0;
                    }
                }
            }
        }
    }

    public function calculateCumulative($child, $scores, $test_num = 0) {
        $types = $this->getTypes();
        $cumulative = $this->getCumulativeScore($scores, $test_num);
        if (empty($cumulative)) return '';

        $iyun_avg = 90;
        $avgs = $this->getPassingAvgs($child['user_id']);
        if ($cumulative['genius'] >= $iyun_avg) return strtolower($types['genius']);
        else if ($cumulative['expert'] >= $avgs['expert']) return strtolower($types['expert']);
        else if ($cumulative['pro'] >= $avgs['pro']) return strtolower($types['pro']);
        else if ($cumulative['maven'] >= $avgs['maven']) return strtolower($types['maven']);
        else return '';
    }

    public function getCumulativeScore($scores, $test_num = 0) {
        $test_num = $test_num > 0 ? $test_num : $this->figureOutTestNum();
        $num_questions = $this->getTestQuestions();
        $types = $this->getTypes();

        $questions = [];
        $cumulative_scores = [];

        // initialize cumulative scores
        foreach ($types as $type => $desc) {
            $cumulative_scores[$type] = 0;
        }

        $questions['maven'] = $num_questions['maven'];
        $questions['pro'] = $num_questions['maven'] + $num_questions['pro'];
        $questions['expert'] = $num_questions['maven'] + $num_questions['pro'] + $num_questions['expert'];
        $questions['genius'] = $num_questions['maven'] + $num_questions['pro'] + $num_questions['expert'] + $num_questions['genius'];

        for ($i = 1; $i <= $test_num; $i++) {
            foreach ($types as $type => $desc) {
                if (isset($scores[$i][$type]) && $scores[$i][$type] > 0) $cumulative_scores[$type] += intval($scores[$i][$type]);
            }
        }
        
        $cumulative_scores['genius'] += $cumulative_scores['expert'] + $cumulative_scores['pro'] + $cumulative_scores['maven'];
        $cumulative_scores['expert'] += $cumulative_scores['pro'] + $cumulative_scores['maven'];
        $cumulative_scores['pro'] += $cumulative_scores['maven'];

        $cumulative = [];
        foreach ($types as $type => $desc) {
            if ($cumulative_scores[$type] > 0)
                $cumulative[$type] = round(($cumulative_scores[$type] / ($questions[$type] * $test_num)) * 100);
            else
                $cumulative[$type] = 0;
        }

        return $cumulative;
    }

    public function getMarks() {
        return $this->marks;
    }

    public function getLearned( $dates, $untilToday = false ) {
        $dateArr = explode('/', $dates[0]);
        $start = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
        if ($untilToday) $end = unixtojd();
        else {
            $dateArr = explode('/', $dates[count($dates)-1]);
            $end = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
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

    public function getTotalMinutesLearned( $user_id, $test_num, $untilToday = false ) {
        $dates = $this->getDates()[$test_num-1];
        $dateArr = explode('/', $dates[1]);
        $start = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
        if ($untilToday) $end = unixtojd();
        else {
            $dateArr = explode('/', $dates[2]);
            $end = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
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
        // $stmt->debugDumpParams();
        return $stmt->fetch()['total'];
    }

    public function getTotalDaysLearned( $user_id, $dates, $untilToday = false ) {
        $dateArr = explode('/', $dates[0]);
        $start = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
        if ($untilToday) $end = unixtojd();
        else {
            $dateArr = explode('/', $dates[count($dates)-1]);
            $end = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
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
        $start = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
        $dateArr = explode('/', $dates[count($dates)-1]);
        $end = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);

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
            $jd = gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]);
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

    public function getHighestTrackPassed( $child, $numTests = 3, $overwrite = true ) {
        if ($overwrite) {
            $this->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
            $this->setScores();
            $this->calculateMarks();
        }

        // get child mark info
        if (! isset($this->marks[$child['th_chidon_id']])) {
            return [
                'avg' => 0,
                'highest_track' => '',
                'highest_track_avg' => 0
            ];
        }

        $highest = $this->getHighestTrack($this->marks[$child['th_chidon_id']], $child['user_id'], false, $numTests, true);
        $highest_type = $highest['highest'];
        $highest_avg = $highest['avg'];

        // check if child has a reward type set
        $sql = "select test_type, reward_type from th_chidon where th_chidon_id = " . $child['th_chidon_id'];
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $testType = $row['test_type'];
        $rewardType = $row['reward_type'];

        // check which type is higher
        if (!empty($highest_type) && !empty($rewardType) && $rewardType != 'highest track passed') {
            $indexes = array_keys($this->types);
            $key1 = array_search($highest_type, $indexes);
            $key2 = array_search($rewardType, $indexes);
            if ($key2 > $key1) $highest_type = $rewardType;
        } else if (empty($highest_type) && !empty($rewardType) && $rewardType != 'highest track passed') {
            $highest_type = $rewardType;
        }

        $markInfo = [];
        $markInfo['avg'] = $avgs[$testType] ?? 0;
        $markInfo['highest_track'] = $highest_type;
        $markInfo['highest_track_avg'] = $highest_avg;
        $markInfo['test_type'] = $testType;
        $markInfo['reward_type'] = $rewardType;

        return $markInfo;
    }

    public function getHighestTrack($marks, $user_id, $forEligibility = false, $numTests = 3, $needAvg = false, $forIyun = false, $ht = false) {
        // check if we already determined the track
        $highest_track = $ht ?? '';
        // if (!$ht) {
        //     $stmt = $this->db->prepare("
        //         SELECT highest_track FROM th_chidon_info 
        //         WHERE year = :year AND user_id = :user
        //     ");
        //     $stmt->execute([
        //         'user'  => $user_id,
        //         'year'  => $this->year
        //     ]);
        //     $rowTrack = $stmt->fetch();
        //     if ($rowTrack && !$needAvg) {
        //         $highest_track = $rowTrack['highest_track'];
        //     }
        // } 
        $key = $highest_track ? array_search($highest_track, $this->types) : false;

        $highest = $forEligibility ? 'maven' : '';
        $avgs = $this->getPassingAvgs($user_id);

        $marksByTrack = [];
        foreach ($this->types as $type => $desc) {
            $marksByTrack[$type] = 0;
        }

        // make sure we pass each track on each test
        if ($marks && count($marks) > 0) {
            foreach ($marks as $test_num => $details) {
                if ($test_num <= $numTests) { // only calculate marks based on test number that's passed in
                    foreach ($details as $track => $mark) {
                        $marksByTrack[$track] += intval($mark);
                    }
                }
            }
        }

        // calculate avgs and highest type currently eligible for
        $actualAvg = 0;
        foreach ($marksByTrack as $track => $total) {
            $avg = round($total / $numTests);
            $avgNeeded = $avgs[$track];
            if ($forIyun) $avgNeeded = $avgs['genius'];
            if ($avg >= $avgNeeded) {
                $highest = $track;
                $actualAvg = $avg;
            }
            else break; // can't go higher if lower one was not passed
        }

        // if highest track is genius, check for iyun non cumulative score based on 80 avg needed on each track
        // when track is genius, the child can only get it if all tracks are at least 80%
        if ($highest == 'genius') {
            $actualAvg = 0;
            foreach ($marksByTrack as $track => $total) {
                $avg = round($total / $numTests);
                $avgNeeded = $avgs['genius'];
                if ($avg >= $avgNeeded) {
                    $iyunHighest = $track;
                    $iyunActualAvg = $avg;
                }
                else break; // can't go higher if lower one was not passed
            }
            if ($iyunHighest != 'genius') $highest = 'expert';
            else {
                $highest = $iyunHighest;
                $actualAvg = $iyunActualAvg;
            }
        }

        // compare with key
        if (isset($key) && $key !== false) {
            $keys = array_keys($this->types);
            $idx = array_search($key, $keys);
            $idx2 = array_search($highest, $keys);
            if ($idx > $idx2) $highest = $key;
        }

        if ($needAvg) {
            return [
                'highest' => $highest,
                'avg'   => $actualAvg
            ];
        }

        return $highest;
    }

    public function getPrevHighestTrack($year, $user_id) {
        $sql = "select * from th_chidon_info where year = " . $year . " and user_id = " . $user_id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['highest_track'];
    }

    public function getPassingAvgs($user_id, $type = '') {
        $table = 'chidon_passing_avgs';
        if ($type == 'finals') $table = 'chidon_final_passing_avgs';

        $stmt = $this->db->prepare("
            select * from $table  
            where year = :year 
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
            ':user' => $user_id
        ]);

        $avgs = [];
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if ($row['user_id'] > 0) $avgs['user'][$row['track']] = $row['avg'];
            else if ($row['class_id'] > 0) $avgs['class'][$row['track']] = $row['avg'];
            else if ($row['school_id'] > 0) $avgs['school'][$row['track']] = $row['avg'];
        }
        // if there's no setting, default to 80
        $passingAvgs = [];
        foreach ($this->types as $type => $desc) {
            $passingAvgs[$type] = $avgs['user'][$type] ?? $avgs['class'][$type] ?? $avgs['school'][$type] ?? 80;
        }
        return $passingAvgs;
    }

    public function getLevel($user_id, $type = '')
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

        // if there's no setting, default to 1
        $test_level = $levels['user']['tests'] ?? $levels['class']['tests'] ?? $levels['school']['tests'] ?? 1;
        $final_level = $levels['user']['finals'] ?? $levels['class']['finals'] ?? $levels['school']['finals'] ?? 1;

        if ($type == 'tests') return $test_level;
        else if ($type == 'finals') return $final_level;
        else {
            return [
                'tests' => $test_level,
                'finals' => $final_level
            ];
        }
    }

    public function getSettings($school_id, $class_id, $user_id, $year = 0) {
        $info = [];
        foreach(['passing_avgs', 'final_passing_avgs', 'test_levels'] as $table) {
            $table = 'chidon_' . $table;
            if ($user_id > 0) {
                $stmt = $this->db->prepare("
                    SELECT * FROM `$table` WHERE year = :year 
                    AND user_id = :id
                ");
                $stmt->execute([
                    ':year' => $year > 0 ? $year : $this->year,
                    ':id'   => $user_id
                ]);
            } else if ($class_id > 0) {
                $stmt = $this->db->prepare("
                    SELECT * FROM `$table` WHERE year = :year 
                    AND class_id = :id
                ");
                $stmt->execute([
                    ':year' => $year > 0 ? $year : $this->year,
                    ':id'   => $class_id
                ]);
            } else if ($school_id > 0) {
                $stmt = $this->db->prepare("
                    SELECT * FROM `$table` WHERE year = :year 
                    AND school_id = :id
                ");
                $stmt->execute([
                    ':year' => $year > 0 ? $year : $this->year,
                    ':id'   => $school_id
                ]);
            }
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                if ($table == 'chidon_test_levels') {
                    if ($row['user_id'] > 0) $info[$row['user_id']]['chidon_test_levels'][$row['test_type']] = $row['test_level'];
                    else if ($row['class_id'] > 0) $info[$row['class_id']]['chidon_test_levels'][$row['test_type']] = $row['test_level'];
                    else if ($row['school_id'] > 0) $info[$row['school_id']]['chidon_test_levels'][$row['test_type']] = $row['test_level'];
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
        $class_ids = [];
        $user_ids = [];

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
                    if ($row['user_id'] > 0) {
                        if (! in_array($row['user_id'], $user_ids)) $user_ids[] = $row['user_id'];
                        $info['user'][$row['user_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                    } else if ($row['class_id'] > 0) {
                        if (! in_array($row['class_id'], $class_ids)) $class_ids[] = $row['class_id'];
                        $info['class'][$row['class_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                    } else if ($row['school_id'] > 0) {
                        $info['school'][$row['school_id']]['test_levels'][$row['test_type']] = $row['test_level'];
                    }
                } else {
                    if ($row['user_id'] > 0) {
                        if (! in_array($row['user_id'], $user_ids)) $user_ids[] = $row['user_id'];
                        $info['user'][$row['user_id']][$table][$row['track']] = $row['avg'];
                    } else if ($row['class_id'] > 0) {
                        if (! in_array($row['class_id'], $class_ids)) $class_ids[] = $row['class_id'];
                        $info['class'][$row['class_id']][$table][$row['track']] = $row['avg'];
                    } else if ($row['school_id'] > 0) {
                        $info['school'][$row['school_id']][$table][$row['track']] = $row['avg'];
                    }
                }
            }
        }

        $details = [];
        if (count($user_ids)) {
            $stmt = $this->db->query("
                SELECT * FROM users u 
                JOIN classes c on c.class_id = u.class_id 
                WHERE user_id IN (" . implode(',', $user_ids) . ") 
            ");
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $details['user'][$row['user_id']] = $row;
            }
        }

        if (count($class_ids)) {
            $stmt = $this->db->query("
                SELECT * FROM classes WHERE class_id IN (" . implode(',', $class_ids) . ")
            ");
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $details['class'][$row['class_id']] = $row;
            }
        }

        return [
            'settings'  => $info,
            'details'   => $details
        ];
    }

    /**
     * Create function to find out which dates to open marking
     * @param string $date
     * @return boolean
     */
    public static function getOpeningDates() {
        $shutdown = [];
        $opening[1] = new DateTime('2025-10-19 00:00:00', new DateTimeZone('America/New_York'));
        $opening[2] = new DateTime('2025-12-02 00:00:00', new DateTimeZone('America/New_York'));
        $opening[3] = new DateTime('2026-01-21 00:00:00', new DateTimeZone('America/New_York'));
        $opening[4] = new DateTime('2026-02-19 00:00:00', new DateTimeZone('America/New_York'));
        return $opening;
    }

    /**
     * Create function to find out which dates to close marking
     * @param string $date
     * @return boolean
     */
    public static function getClosingDates() {
        $shutdown = [];
        $shutdown[1] = new DateTime('2025-11-13 00:00:00', new DateTimeZone('America/New_York'));
        $shutdown[2] = new DateTime('2025-12-25 00:00:00', new DateTimeZone('America/New_York'));
        $shutdown[3] = new DateTime('2026-01-30 00:00:00', new DateTimeZone('America/New_York'));
        $shutdown[4] = new DateTime('2026-02-23 00:00:00', new DateTimeZone('America/New_York'));
        return $shutdown;
    }
}

class KHK {
    public static $khkFee = 18;

    // find out if child is eligible to enroll into khk track
    public static function enrollmentEligibility(array $user_ids) {
        $info = [];
        $year = GlobalSettings::getChidonRegYear();
        $exceptions = self::getEligibilityExceptions($year);
        foreach ($user_ids as $user_id) {
            if (isset($exceptions[$user_id])) {
                $info[$user_id] = true;
                continue;
            }
            // check if child enrolled into chidon in last 4 years
            $sql = 'select * from th_chidon where user_id = ' . $user_id . ' and year >= ' . ($year - 4);
            $result = mysql_query($sql);
            $info[$user_id] = mysql_num_rows($result) >= 4;
        }
        return $info;
    }

    public static function getEligibilityExceptions($year) {
        $exception = [];
        $sql = "select * from khk_enrollment_eligibility where year = " . $year;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $exception[$row['user_id']] = true;
        }
        return $exception;
    }

    public static function getEligibilityFromHistory(array $user_ids, $num_yrs = 4) {
        $year = GlobalSettings::getChidonRegYear();
        
        $info = [];
        $sql = "SELECT * FROM th_chidon_info where year >= " . ($year - $num_yrs);
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            if (! in_array($row['user_id'], $user_ids)) continue;
            $info[$row['user_id']][$row['year']] = $row;
        }
        return $info;
    }

    // find out if child is eligible for ultimate trip from override
    public static function eligibility(array $user_ids) {
        $info = [];
        $year = GlobalSettings::getChidonYear();
        foreach ($user_ids as $id) {
            // check if override was turned on
            $sql = "select khk_override from th_chidon where year = " . $year . " and user_id = " . $id;
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0) {
                $row = mysql_fetch_assoc($result);
                $info[$id] = intval($row['khk_override']) ? true : false;
            } else {
                $info[$id] = false;
            }
        }
        return $info;
    }

    /**
     * Algorithm to determine if child is eligible for ultimate trip
     * takes array of user ids
     * and returns two arrays
     * one is whether that child is eligible for ultimate trip
     * the other is the details of which yr the child was or wasn't eligible
     */
    public static function getUltimateTripEligibility( array $ids, $year = 0, $numYrs = 4, array $marks = [], $nextYr = false ) {
        // yr that we don't check registration but rather check highest track passed
        // $rollover = 5782;
        // first make sure child was enrolled into chidon for last 4 years
        $enrollmentEligibility = KHK::enrollmentEligibility($ids);

        if ($numYrs > 4) $numYrs = 4; // make sure it's not more than 4

        $years = [];
        if ($nextYr) {
            $yr = $year - $numYrs;
            for (; $numYrs > 0; $numYrs--) { // does not include the year that is passed in
                $years[] = $yr++;
            }
        } else {
            // figure out which years we need to check
            $curYr = $year > 0 ? $year : GlobalSettings::getChidonYear();
            $yr = $curYr - $numYrs;
            for (; $numYrs >= 0; $numYrs--) { // includes the year that is passed in or current yr
                $years[] = $yr++;
            }

            // for current yr, check if passed
            $passed = KHK::getCurrentYrPassing($curYr, $ids, $marks);
        }

        $overriden = KHK::eligibility($ids);
        foreach ($ids as $id) {
            $details[$id] = [];
            foreach ($years as $yr) {
                // exceptions
                $exceptions = [];
                $details[$id][$yr] = false;
                // for current yr, check if passed
                if (!$nextYr && $yr == $curYr) $details[$id][$yr] = $passed[$id];
                else {
                    if (! $enrollmentEligibility[$id]) continue;
                    // check highest track passed
                    $sql = "select highest_track from th_chidon_info where user_id = " . $id . " and year = " . $yr;
                    $result = mysql_query($sql);
                    if (mysql_num_rows($result) > 0) {
                        $highest_track = mysql_fetch_assoc($result)['highest_track'];
                        // make sure child is at least on the yediah track (not on 'yesod')
                        if ($highest_track != 'yesod') $details[$id][$yr] = true;
                    }
                }
            }
        }

        // for each child find out if final result is eligible or not
        foreach ($details as $id => $yrs) {
            $khk[$id] = true;
            foreach ($yrs as $eligible) {
                if (!$eligible && !$overriden[$id]) {
                    $khk[$id] = false;
                    break;
                }
            }
        }

        return [$khk, $details];
    }

    private static function getCurrentYrPassing(string $year, array $ids, array $marks) {
        $info = [];
        // get the school_id, class_id and th_chidon_id for all children
        $sql = "select u.user_id, u.school_id, class_id, th_chidon_id, reward_type  
                from users u 
                join th_chidon tc using (user_id) 
                where u.user_id IN (" . implode(',', $ids) . ") and tc.year = " . $year;
//        echo $sql;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $info[$row['user_id']] = $row;
        }

        if (! count($info)) {
            // return false for all ids
            $passed = [];
            foreach ($ids as $id) {
                $passed[$id] = false;
            }
            return $passed;
        }

        if (empty($marks)) {
            foreach ($ids as $id) {
                // get marks
                $ct = new ChidonTests($year);
                $ct->setStudents($info[$id]['school_id'], $info[$id]['class_id'], $id);
                $ct->setScores();
                $ct->calculateMarks();
                $marks += $ct->getMarks();
            }
        }

        $passed = [];
        $ct = new ChidonTests($year);
        foreach ($ids as $id) {
            if (! isset($marks[$info[$id]['th_chidon_id']])) {
                $passed[$id] = false;
                continue;
            }
            $highest_track = $ct->getHighestTrack($marks[$info[$id]['th_chidon_id']], $id);
            if ($info[$id]['reward_type'] != 'highest track passed' && $info[$id]['reward_type'] != '')
                $highest_track = $info[$id]['reward_type'];
            // if highest track is expert or genius, child is eligible
            switch ($highest_track) {
                case 'expert':
                case 'genius':
                    $passed[$id] = true;
                    break;
                default:
                    $passed[$id] = false;
                    break;
            }
        }
        return $passed;
    }
}