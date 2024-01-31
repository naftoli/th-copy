<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
class TripRegistration
{
    /**
     * figure out how much the family pre-registered, and how much was already used
     */
    public static function getFamilyBalance($admin_id, $year) {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT 
                prepaid, used 
            FROM
                family_prepaid_balances
            WHERE
                year = :year AND admin_id = :admin
        ");
        $stmt->execute([
            ':year'     => $year,
            ':admin'    => $admin_id
        ]);
        $result = $stmt->fetch();
        if ($result) {
            $paid = floatval($result['prepaid']);
            $used = floatval($result['used']);
            $bal = $paid - $used;
            return $bal > 0 ? $bal : 0;
        }
        return 0;
    }
}