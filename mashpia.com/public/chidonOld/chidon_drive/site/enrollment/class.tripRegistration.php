<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
class TripRegistration
{
    public function __construct($admin_id, $year)
    {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->admin_id = $admin_id;
        $this->year = $year;
    }

    /**
     * figure out how much the family pre-registered, and how much was already used
     */
    public function getFamilyBalance() {
        $balance = $this->getBalance();
        $prizes_subtract = $this->subtractForPrizes();
        $balance -= $prizes_subtract;
        return $balance;
    }

    private function getBalance() {
        $bal = 0;
        $stmt = $this->db->prepare("
            SELECT 
                prepaid, used, refund_amount  
            FROM
                family_prepaid_balances
            WHERE
                year = :year AND admin_id = :admin
        ");
        $stmt->execute([
            ':year'     => $this->year,
            ':admin'    => $this->admin_id
        ]);
        $result = $stmt->fetch();
        if ($result) {
            $paid = floatval($result['prepaid']);
            $used = floatval($result['used']);
            $refund = floatval($result['refund_amount']);
            $bal = $paid - $used - $refund;
        }
        return $bal;
    }

    private function subtractForPrizes() {
        $toSubtract = 0;
        $subtract = [
            75 => [284, 285, 286, 287, 288, 289, 290, 291, 295, 302, 303],
            35 => [395],
            30 => [380, 381]
        ];

        $stmt = $this->db->prepare("
            SELECT 
                *
            FROM
                chidon_user_prizes
            WHERE
                year = :year
                    AND user_id IN (SELECT 
                        id
                    FROM
                        admin_auths
                    WHERE
                        admin_id = :admin)"
        );
        $res = $stmt->execute([
            ':year'     => $this->year,
            ':admin'    => $this->admin_id
        ]);
        if ($res) {
            $prizes = $stmt->fetchAll();
            foreach ($prizes as $prize) {
                if (! empty($prize['he_name'])) {
                    $amount = array_search($prize['prize_id'], $subtract);
                    if ($amount) $toSubtract += $amount;
                }
            }
        }
        return $toSubtract;
    }
}