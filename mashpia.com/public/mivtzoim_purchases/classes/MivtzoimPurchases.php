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
        // $stmt = $MASHPIA_DB->query("SELECT * FROM mashpia_purchases.mivtzoim_items");
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
                    mashpia_purchases.purchases
                        JOIN
                    mashpia_purchases.purchase_details USING (purchase_id)
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
                    mashpia_purchases.purchases
                        JOIN
                    mashpia_purchases.purchase_details USING (purchase_id)
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
     * gets number of purchases done for specific year / item
     * if item doesn't exist in list of items, returns error msg
     */
    public function getNumPurchases( $year, $item_id, $admin_id = 0 ) {
        if ( $admin_id > 0 ) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    IFNULL(SUM(qty), 0) AS total
                FROM
                    mashpia_purchases.purchases
                        JOIN
                    mashpia_purchases.purchase_details USING (purchase_id)
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
                    IFNULL(SUM(qty), 0) AS total
                FROM
                    mashpia_purchases.purchases
                        JOIN
                    mashpia_purchases.purchase_details USING (purchase_id)
                WHERE
                    year = :year AND item_id = :item
            ");
            $success = $stmt->execute([
                ':year'     =>  $year, 
                ':item'     =>  $item_id
            ]);
            if ( $success ) {
                $row = $stmt->fetch();
                return $row['total'];
            }
        }
        // if we get here there were some errors
        return false;
    }

    /**
     * gets number of purchases done for each item in the specific yom tov
     */
    public function getPurchasesPerItem( $year, $yom_tov ) {
        $stmt = $this->pdo->prepare("
            SELECT 
                item_id, IFNULL(SUM(qty), 0) AS total
            FROM
                mashpia_purchases.purchases p
                    JOIN
                mashpia_purchases.purchase_details d USING (purchase_id)
                    JOIN
                mashpia_purchases.mivtzoim_items i ON i.mivtzoim_item_id = d.item_id
            WHERE
                year = :year AND yom_tov = :yom_tov 
            GROUP BY item_id
        ");
        $stmt->execute([
            ':year'     =>  $year,
            ':yom_tov'  => $yom_tov
        ]);
//        $stmt->debugDumpParams();
        $info = [];
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['item_id']] = $row['total'];
        }
        return $info;
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
                mashpia_purchases.purchases
                    JOIN
                mashpia_purchases.purchase_details USING (purchase_id)
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

    public function createPurchase( $info, $details ) {
        $year = $info['year'];
        $admin = $info['admin'];
        $amount = $info['amount'];
        $authorization = $info['auth'];

        $stmt = $this->pdo->prepare("
            INSERT INTO mashpia_purchases.purchases 
            SET 
                year = :year, 
                admin_id = :admin, 
                amount_paid = :amount, 
                authorization = :auth
        ");

        $stmt2 = $this->pdo->prepare("
            INSERT INTO mashpia_purchases.purchase_details 
            SET 
                purchase_id = :id, 
                user_id = :user, 
                item_id = :item, 
                qty = :qty
        ");

        $this->pdo->beginTransaction();
        $res = $stmt->execute([
            ':year'     =>  $year, 
            ':admin'    =>  $admin, 
            ':amount'   =>  $amount, 
            ':auth'     =>  $authorization
        ]);
        // $stmt->debugDumpParams(); exit;
        $purchase_id = $this->pdo->lastInsertId();
        
        $success = true;
        foreach ( $details as $user => $items ) {
            foreach ( $items as $item => $qty ) {
                $res2 = $stmt2->execute([
                    ':id'   =>  $purchase_id, 
                    ':user' =>  $user, 
                    ':item' =>  $item, 
                    ':qty'  =>  $qty
                ]);
                // $stmt2->debugDumpParams(); exit;
                if ( !$res2 ) {
                    $success = false;
                    break 2;
                }
            }
        }

        if ( $res && $success ) {
            $this->pdo->commit();
            return true;
        } else {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * gets list of items available for type / yom tov
     */
    public function getItemsByType($type) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM mashpia_purchases.mivtzoim_items WHERE yom_tov = :type
        ");
        $stmt->execute(['type' => $type]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
}