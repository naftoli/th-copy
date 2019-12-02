<?php
/**
 * Mivtzoim settings represent a row in the mivtzoim purchases db and the school settings table
 */
class MivtzoimSetting {
    private $year;
    private $school;
    private $item;
    private $pdo;

    public function __construct( $year, $school, $item ) {
        global $MASHPIA_DB;
        $this->year = $year;
        $this->school = $school;
        $this->item = $item;
        $this->pdo = $MASHPIA_DB;
    }

    /**
     * gets the current row in the db based on year / school / type
     */
    public function getSettings() {
        $stmt = $this->pdo->prepare("
            SELECT 
                *
            FROM
                mivtzoim_purchases.school_settings
            WHERE
                school_id = :school AND year = :year
                    AND item_id = :item
        ");
        $stmt->execute([
            ':school'   =>  $this->school, 
            ':year'     =>  $this->year, 
            ':item'     =>  $this->item
        ]);
        return $stmt->fetch();
    }

    /**
     * sets the purchase price for the school based on school / year / type
     * if the update or insert has an error, return false
     * else return true
     */
    public function enablePurchases( $shipping_fee ) {
        // figure out if updating or adding
        $row = $this->getSettings();
        if ( !empty( $row ) && floatval( $row['shipping_charge'] ) != floatval( $shipping_fee ) ) {
            // update
            $stmt = $this->pdo->prepare("
                UPDATE mivtzoim_purchases.school_settings 
                SET 
                    allow_purchases = 1,
                    shipping_charge = :fee
                WHERE
                    year = :year AND school_id = :school
                        AND item_id = :item
            ");
        } else if ( empty( $row ) ) {
            // add row to db
            $stmt = $this->pdo->prepare("
                INSERT INTO mivtzoim_purchases.school_settings 
                SET 
                    allow_purchases = 1, 
                    shipping_charge = :fee, 
                    year = :year, 
                    school_id = :school, 
                    item_id = :item
            ");
        }
        if ( isset( $stmt ) ) {
            $success = $stmt->execute([
                ':fee'      =>  $shipping_fee, 
                ':year'     =>  $this->year, 
                ':school'   =>  $this->school, 
                ':item'     =>  $this->item
            ]);
            if ( !$success || $stmt->rowCount() == 0 ) {
                return false;
            }
        }
        return true;
    }

    /**
     * sets school to not allow purchases for this school / year / type
     * if the update or insert has an error, return false
     * else return true
     */
    public function disablePurchases() {
        $row = $this->getSettings();
        if ( !empty( $row ) && intval( $row['allow_purchases'] ) != 0 ) {
            // update
            $stmt = $this->pdo->prepare("
                UPDATE mivtzoim_purchases.school_settings 
                SET 
                    allow_purchases = 0,
                    shipping_charge = 0
                WHERE
                    year = :year AND school_id = :school
                        AND item_id = :item
            ");
        } else if ( empty( $row ) ) {
            // add row to db
            $stmt = $this->pdo->prepare("
                INSERT INTO mivtzoim_purchases.school_settings 
                SET 
                    allow_purchases = 0, 
                    shipping_charge = 0, 
                    year = :year, 
                    school_id = :school, 
                    item_id = :item
            ");
        }
        if ( isset( $stmt ) ) {
            $success = $stmt->execute([
                ':year'     =>  $this->year, 
                ':school'   =>  $this->school, 
                ':item'     =>  $this->item
            ]);
            if ( !$success || $stmt->rowCount() == 0 ) {
                return false;
            }
        }
        return true;
    }

    /**
     * static function to find all schools that have shipping enabled for specific year / items
     */
    public static function getEnabledSchools( $year, $items ) {
        global $MASHPIA_DB;
        $items = implode(',', $items);
        $stmt = $MASHPIA_DB->prepare("
            SELECT * FROM mivtzoim_purchases.school_settings 
            WHERE allow_purchases = 1 
                AND year = :year AND item_id in ($items) 
        ");
        $stmt->execute([
            ':year'     =>  $year
        ]);
        return $stmt->fetchAll();
    }
}