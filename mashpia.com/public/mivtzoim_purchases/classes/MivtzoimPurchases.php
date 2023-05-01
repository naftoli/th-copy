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

    /**
     * send email to admin with list of purchases done for specific year / item
     */
    public function sendEmail($info, $details, $cc) {
        $to = $this->getEmail($info['admin']);
        $subject = "Yahadus Book Order Confirmation";
        $message = $this->getMessage($info['amount'], $details, $cc);
        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        // Additional headers
        $headers[] = 'From: chidon@tzivoshashem.org';
        // Mail it
        $success = mail($to, $subject, $message, implode("\r\n", $headers));
        return $success;
    }

    private function getEmail($admin_id) {
        $stmt = $this->pdo->prepare("
            SELECT admin_email FROM admins WHERE admin_id = :admin
        ");
        $stmt->execute(['admin' => $admin_id]);
        $row = $stmt->fetch();
        return $row['admin_email'];
    }

    private function getMessage($amount, $details, $ccInfo) {
        // get items info
        $itemsInfo = [];
        $items_all = $this->getItemsByType('Yahadus Book Sale');
        foreach ($items_all as $item) {
            $itemsInfo[$item['mivtzoim_item_id']] = $item;
        }

        $message = '
            <b>Thank you for your order!</b>
            <br /><br />
            We will start shipping all books to your school <b>after</b> the sale is over, 
            with the goal for them to arrive before the end of the school year so you can start learning over the summer! 
            Please be patient.
            <br /><br />
            <b>Items</b><br />
        ';

        foreach ($details as $items) {
            foreach ($items as $item => $qty) {
                $message .= $itemsInfo[$item]['item'] . " - $" . ($qty * 40) . "<br />";
            }
        }
        $message .= "<br /><b>Total: $" . $amount . "</b><br /><br />";

        if ($ccInfo->on_file == 1) {
            $name = '';
            $digits = $ccInfo->last_four;
        } else if ($ccInfo->on_file == 0) {
            $name = $ccInfo->name;
            $number = $ccInfo->num;
            $digits = substr($number, -4);
        }

        $message .= "
            <b>Billing Information</b>
            <br />";

        if ($name) $message .= "Name on Card: $name<br />";
        else $message .= "Used Credit Card on File<br />";
        $message .= "Last 4 digits: $digits<br />";

        $message .= "
            <br />
            If you have any questions about your order, please contact your school's Chidon Coordinator. 
            All transactions are non refundable. Hatzlocha with the learning!
            <br /><br />
            P.S. Ordering a book is <b>NOT</b> considered enrollment for next year's Chidon, enrollment will be at a later date.
            <br />
            <br />
            <span style='font-size: small'>
              To unsbuscribe from this mailing list, please click <a href='http://www.mashpia.com/unsubscribe.php'>here</a>
          </span>
        ";

        return $message;
    }
}