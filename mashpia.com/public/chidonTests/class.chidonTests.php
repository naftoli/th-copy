<?php
//ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
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

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonYear();
        $this->children = [];
        $this->scores = [];
        $this->marks = [];
        $this->types = [
            'maven' => 'Maven',
            'pro'   => 'Maven / Pro',
            'expert'=> 'Pro / Expert'
        ];
        // hardcode number of questions per test type
        $this->testQuestions = [
            'maven' => 10,
            'pro'   => 10,
            'expert'=> 15,
            'trophy'=> 10
        ];
        $this->genderOnly = false;
    }

    public function setGender($gender) {
        $this->genderOnly = $gender;
    }

    public function setStudents($school_id = 0, $class_id = 0, $user_id = 0) {
        $qry = "
            SELECT 
                tc.th_chidon_id, tc.user_id, tc.test_type, tc.parent_id,
                u.first, u.last,
                c.class_id, c.class_grade, c.class_sub,
                s.school_id, s.school_name, a.admin_email
            FROM
                th_chidon tc
                    JOIN
                users u USING (user_id)
                    JOIN
                schools s on s.school_id = u.school_id
                    JOIN
                classes c ON c.class_id = u.class_id 
                    JOIN
                admins a on a.admin_id = tc.parent_id 
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
                    ':school'   => $school_id,
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
                th_chidon_id = :id
        ");
        foreach ($this->children as $child) {
            $id = $child['th_chidon_id'];
            $res = $stmt->execute([':id' => $id]);
            if ($res) {
                $rows = $stmt->fetchAll();
                foreach ($rows as $row) {
                    $this->scores[$id][$row['test_number']][$row['test_type']] = $row['answered_correctly'];
                }
            }
        }
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

    public function getTypes() {
        return $this->types;
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
                            $mark = floatval(($details['pro'] + $details[$type]) / ($this->testQuestions['pro'] + $questions));
                            break;
                        case 'trophy':
                            $mark = floatval(
                                ($details['pro'] + $details['expert'] + $details[$type]) /
                                ($this->testQuestions['pro'] + $this->testQuestions['expert'] + $questions)
                            );
                    }
                    $this->marks[$id][$testNum][$type] = $mark * 100;
                }
            }
        }
    }

    public function getMarks() {
        return $this->marks;
    }
}