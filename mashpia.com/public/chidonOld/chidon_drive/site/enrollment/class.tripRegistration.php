<?php
if (! isset($MASHPIA_DB)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
}

class TripRegistration
{
    public function __construct($admin_id, $year) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->admin_id = $admin_id;
        $this->year = $year;
    }

    /**
     * figure out how much the family pre-registered, and how much was already used
     */
    public function getFamilyBalance() {
        return $this->getBalance();
    }

    private function getBalance() {
        $bal = 0;
        $stmt = $this->db->prepare("
            SELECT 
                admin_id, IFNULL(SUM(amount), 0) as bal 
            FROM
                registration_charges
            WHERE
                type = 'RRFAM' AND year = :year 
                    AND admin_id = :admin_id
        ");
        $stmt->execute([
            'year' => $this->year,
            'admin_id' => $this->admin_id
        ]);
        $row = $stmt->fetch();
        $bal = $row['bal'];
        
        return $bal;
    }
}