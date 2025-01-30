<?php
//require_once '../../../../api/header/db.php'; // can't use document root b/c this needs to be accessed from chidondrive
// which is a different domain

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
                admin_id, SUM(amount)
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
        return $bal;
    }
}