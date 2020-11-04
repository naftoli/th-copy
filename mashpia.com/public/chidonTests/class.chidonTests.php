<?php
ini_set('display_errors', 1);
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
    private $marks;
    private $testQuestions;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonYear();
        $this->children = [];
        $this->marks = [];
        // hardcode number of questions per test type
        $this->testQuestions = [
            'maven' => 10,
            'pro'   => 10,
            'expert'=> 15,
            'trophy'=> 10
        ];
    }

    public function setStudents($school_id = 0) {
        $qry = "
            SELECT 
                tc.th_chidon_id, tc.user_id, tc.test_type,
                u.first,
                u.last,
                c.class_grade,
                c.class_sub,
                s.school_name
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
        if ($school_id) $qry .= " AND u.school_id = :school";
        // order by
        $qry .= " ORDER BY school_name, class_grade, class_sub, last, first";
        $stmt = $this->db->prepare($qry);
        if (!$school_id) $res = $stmt->execute([':year' => $this->year]);
        else $res = $stmt->execute([
            ':year' => $this->year,
            ':school'   => $school_id
        ]);
        if ($res) {
            $this->children = $stmt->fetchAll();
        }
    }

    public function getStudents() {
        return $this->children;
    }

    public function setMarks() {
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
                    $this->marks[$id][$row['test_number']][$row['test_type']] = $row['answered_correctly'];
                }
            }
        }
    }

    public function getMarks() {
        return $this->marks;
    }

    public function insertMarks($info) {
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
                    if ($details[$type] > 0) {
                        $stmt->execute([
                            ':id' => $id,
                            ':type' => $type,
                            ':number' => $testNum,
                            ':questions' => $questions,
                            ':answered' => $details[$type]
                        ]);
                    }
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
}