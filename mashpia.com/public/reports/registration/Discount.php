<?php
abstract class DiscountBase {
    public function __construct($year, $amount, $reason, $created_by, $used = null, $created = '') {
        $this->year = $year;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->created_by = $created_by;
        $this->used = $used;
        $this->created = $created;
    }
    abstract public function createDiscountCoupon();
}

class SchoolDiscount extends DiscountBase
{
    public function __construct($year, $school_id, $amount, $reason, $created_by, $used = null, $created = '') {
        parent::__construct($year, $amount, $reason, $created_by, $used, $created);
        $this->school_id = $school_id;
    }

    public function createDiscountCoupon() {
        $stmt = $this->db->prepare("
            INSERT INTO discounts 
            SET year = :year, 
                school_id = :school, 
                amount = :amount, 
                reason = :reason, 
                created_by = :created_by,
                created = now()
        ");
        return $stmt->execute([
            ':year'     => $this->year,
            ':school'   => $this->school_id,
            ':amount'   => $this->amount,
            ':reason'   => $this->reason,
            ':created_by'   => $this->created_by
        ]);
    }
}

class StudentDiscount extends DiscountBase
{
    public function __construct($year, $user_id, $amount, $reason, $created_by, $used = null, $created = '') {
        parent::__construct($year, $amount, $reason, $created_by, $used, $created);
        $this->user_id = $user_id;
    }

    public function createDiscountCoupon() {
        $stmt = $this->db->prepare("
            INSERT INTO discounts 
            SET year = :year, 
                user_id = :user, 
                amount = :amount, 
                reason = :reason, 
                created_by = :created_by,
                created = now()
        ");
        return $stmt->execute([
            ':year'     => $this->year,
            ':user'     => $this->user_id,
            ':amount'   => $this->amount,
            ':reason'   => $this->reason,
            ':created_by'   => $this->created_by
        ]);
    }
}

class DiscountManager
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createDiscount(DiscountBase $d) {
        return $d->createDiscountCoupon();
    }

    public function getAllDiscounts() {
        $stmt = $this->db->query("SELECT * FROM discounts where school_id > 0");
        return $stmt->fetchAll();
    }

    public function getAllStudentDiscounts() {
        $stmt = $this->db->query("
            SELECT d.*, u.first, u.last FROM discounts d 
            JOIN users u USING(user_id) 
            where d.user_id > 0
        ");
        return $stmt->fetchAll();
    }

    public function getAllStudentDiscountsForYear($year) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.first, u.last 
            FROM discounts d 
            JOIN users u USING(user_id) 
            WHERE d.user_id > 0 AND d.year = :year");
        $stmt->execute([':year' => $year]);
        // $stmt->debugDumpParams();
        return $stmt->fetchAll();
    }

    public function getAllStudentDiscountsForSchoolYear($year, $school_id) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.first, u.last 
            FROM discounts d 
            JOIN users u USING(user_id) 
            WHERE d.user_id > 0 AND d.year = :year AND u.school_id = :school");
        $stmt->execute([
            ':year'     => $year,
            ':school'   => $school_id
        ]);
        // $stmt->debugDumpParams();
        return $stmt->fetchAll();
    }

    public function getDiscountsForYear($year) {
        $stmt = $this->db->prepare("
            SELECT d.*, s.school_name FROM discounts d 
            LEFT JOIN schools s ON d.school_id = s.school_id 
            WHERE d.school_id > 0 AND d.year = :year");
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }

    public function getStudentDiscountsForYear($year) {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE user_id > 0 AND year = :year");
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }

    public function getDiscountsForSchool($school_id) {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE school_id = :school");
        $stmt->execute([':school' => $school_id]);
        return $stmt->fetchAll();
    }

    public function getDiscountsForUser($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE user_id = :user");
        $stmt->execute([':user' => $user_id]);
        return $stmt->fetchAll();
    }

    public function getDiscountsForSchoolYear($year, $school_id) {
        $stmt = $this->db->prepare("
            SELECT d.*, s.school_name FROM discounts d 
            LEFT JOIN schools s ON d.school_id = s.school_id 
            WHERE d.year = :year AND d.school_id = :school
        ");
        $stmt->execute([
            ':year'     => $year,
            ':school'   => $school_id
        ]);
        return $stmt->fetchAll();
    }

    public function getDiscountForUserYear($year, $user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM discounts WHERE year = :year AND user_id = :user
        ");
        $stmt->execute([
            ':year'   => $year,
            ':user'   => $user_id
        ]);
        return $stmt->fetchAll();
    }
}