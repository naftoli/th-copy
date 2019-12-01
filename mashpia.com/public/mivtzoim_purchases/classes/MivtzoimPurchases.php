<?php
/**
 * data concerning the purchases table
 */
class MivtzoimPurchases {
    private $pdo;

    public function __construct() {
        global $MASHPIA_DB;
        $this->pdo = $MASHPIA_DB;
        // $this->items = [];
        // $stmt = $MASHPIA_DB->query("SELECT * FROM mivtzoim_purchases.mivtzoim_items");
        // $rows = $stmt->fetchAll();
        // foreach ( $rows as $row ) {
        //     $this->items[$row['mivtzoim_item_id']] = $row['item'];
        // }
    }

    /**
     * gets list of purchases done for specific year / item
     * if item doesn't exist in list of items, returns error msg
     */
    public function getPurchases( $year, $item_id, $admin_id = 0 ) {
        if ( $admin_id > 0 ) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    *
                FROM
                    mivtzoim_purchases.purchases
                WHERE
                    admin_id = :admin AND year = :year
                        AND item_id = :item
            ");
            $success = $stmt->execute([
                ':admin'    =>  $admin_id, 
                ':year'     =>  $year, 
                ':item'     =>  $item_id
            ]);
            if ( $success )
                return $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->prepare("
                SELECT 
                    *
                FROM
                    mivtzoim_purchases.purchases
                WHERE
                    year = :year AND item_id = :item
            ");
            $success = $stmt->execute([
                ':year'     =>  $year, 
                ':item'     =>  $item_id
            ]);
            if ( $success )
                return $stmt->fetchAll();
        }
        // if we get here there were some errors
        return false;
    }

    /**
     * gets list of purchases done for specific year / item within specific school
     * if item doesn't exist in list of items, returns error msg
     */
    public function getPurchasesBySchool( $year, $item_id, $school_id ) {
        $stmt = $this->pdo->prepare("
            SELECT 
                *
            FROM
                mivtzoim_purchases.purchases
            WHERE
                year = :year AND item_id = :item
                    AND admin_id IN (SELECT 
                        admin_id
                    FROM
                        admin_auths
                    WHERE
                        role_id = 1
                            AND id IN (SELECT 
                                user_id
                            FROM
                                users
                            WHERE
                                school_id = :school))
        ");
        $success = $stmt->execute([
            ':year'     =>  $year, 
            ':item'     =>  $item_id, 
            ':school'   =>  $school_id
        ]);
        if ( $success )
            return $stmt->fetchAll();

        // if we get here there were some errors
        return false;
    }


}