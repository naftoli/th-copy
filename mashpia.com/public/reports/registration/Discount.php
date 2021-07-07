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

    public function getAllDiscounts() {
        $stmt = $this->db->query("SELECT * FROM discounts");
        return $stmt->fetchAll();
    }

    public function getDiscountsForYear($year) {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE year = :year");
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }

    public function getDiscountsForSchool($school_id) {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE school_id = :school");
        $stmt->execute([':school' => $school_id]);
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
        return $stmt->fetchAll();
    }
}