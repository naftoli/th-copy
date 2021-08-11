<?php
class Discount
{
    public function __construct($year, $school_id, $amount, $reason, $created_by, $used = null, $created = '') {
        $this->year = $year;
        $this->school_id = $school_id;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->created_by = $created_by;
        $this->used = $used;
        $this->created = $created;
    }
}

class StudentDiscount
{
    public function __construct($year, $user_id, $amount, $reason, $created_by, $used = null, $created = '') {
        $this->year = $year;
        $this->user_id = $user_id;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->created_by = $created_by;
        $this->used = $used;
        $this->created = $created;
    }
}

class DiscountManager
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createDiscount(Discount $d) {
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
            ':year'     => $d->year,
            ':school'   => $d->school_id,
            ':amount'   => $d->amount,
            ':reason'   => $d->reason,
            ':created_by'   => $d->created_by
        ]);
    }

    public function createStudentDiscount(StudentDiscount $d) {
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
            ':year'     => $d->year,
            ':user'   => $d->user_id,
            ':amount'   => $d->amount,
            ':reason'   => $d->reason,
            ':created_by'   => $d->created_by
        ]);
    }

    public function getAllDiscounts() {
        $stmt = $this->db->query("SELECT * FROM discounts where school_id > 0");
        return $stmt->fetchAll();
    }

    public function getAllStudentDiscounts() {
        $stmt = $this->db->query("SELECT d.*, u.first, u.last FROM discounts d JOIN users u USING(user_id) where d.user_id > 0");
        return $stmt->fetchAll();
    }

    public function getDiscountsForYear($year) {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE school_id > 0 AND year = :year");
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
            SELECT * FROM discounts WHERE year = :year AND school_id = :school
        ");
        $stmt->execute([
            ':year'     => $year,
            ':school'   => $school_id
        ]);
        return $stmt->fetch();
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