<?php
class CouponCode 
{
    private $db;
    private $code;
    private $type;
    private $year;

    public function __construct( $db, $year, $type = 'chidon' ) {
        $this->db = $db;
        $this->code = 0;
        $this->type = $type;
        $this->year = $year;
    }

    public function getCouponCode( $length ) {
        // make sure we have a unique code
        $i = 0;
        while ( !$this->code ) {
            $i++;
            $code = $this->generateCode( $length );
            if ( !$this->codeExists( $code ) ) {
                $this->code = $code;
            }
            if ( $i == 10 ) return false; // if we don't find a unique code after 10 tries then exit
        }
        return $this->code;
    }

    public function generateCode( $length ) {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shuffled = str_shuffle( $permitted_chars );
        return substr( $shuffled, 0, $length );
    }

    public function codeExists( $code ) {
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes WHERE code = :code
        ");
        $stmt->execute([':code' => $code]);
        if ( !empty( $stmt->fetch() ) ) {
            return true;
        } 
        return false;
    }

    public function isValidCode( $code ) {
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes WHERE code = :code AND type = :type AND used = 0
        ");
        $stmt->execute([
            ':code' => $code,
            ':type' => $this->type
        ]);
        $row = $stmt->fetch();
        if ( !empty( $row ) ) {
            return $row['value'];
        }
        return false;
    }

    public function findCode( $code ) {
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes WHERE code = :code AND type = :type AND used = 0
        ");
        $stmt->execute([
            ':code' => $code,
            ':type' => $this->type
        ]);
        $row = $stmt->fetch();
        if ( !empty( $row ) ) {
            return $row;
        }
        return false;
    }

    public function saveCode( $value, $created_by, $reason, $admin_id ) {
        $stmt = $this->db->prepare("
            INSERT INTO coupon_codes 
            SET 
                code = :code, 
                value = :value, 
                type = :type, 
                year = :year, 
                created_by = :created_by, 
                reason = :reason, 
                admin_id = :admin
        ");
        $stmt->execute([
            ':code'     => $this->code, 
            ':value'    => $value, 
            ':type'     => $this->type, 
            ':year'     => $this->year,
            ':created_by' => $created_by, 
            ':reason'   => $reason,
            ':admin'    => $admin_id
        ]);
        if ( $stmt->rowCount() ) return true;
        else return false;
    }

    private function isValidSerialNumber($user_serial) {
        $stmt = $this->db->query("select user_id from users where user_serial = " . $user_serial);
        $row = $stmt->fetch();
        if (! empty($row)) return true;
        else return false;
    }

    public function saveUserCode( $value, $created_by, $reason, $user_serial ) {
        if ($this->isValidSerialNumber($user_serial)) {
            $stmt = $this->db->prepare("
                INSERT INTO coupon_codes 
                SET 
                    code = :code, 
                    value = :value, 
                    type = :type, 
                    year = :year, 
                    created_by = :created_by, 
                    reason = :reason, 
                    serial_num = :serial
            ");
            $res = $stmt->execute([
                ':code' => $this->code,
                ':value' => $value,
                ':type' => $this->type,
                ':year' => $this->year,
                ':created_by' => $created_by,
                ':reason' => $reason,
                ':serial' => $user_serial
            ]);
            if ($res) return 1;
            return 0;
        }
        return -1;
    }

    public static function getListOfCodes( $db, $year, $type = 'chidon' ) {
        $stmt = $db->prepare("
            SELECT * FROM coupon_codes 
            WHERE 
                year = :year AND type = :type
        ");
        $stmt->execute([
            ':year' => $year, 
            ':type' => $type
        ]);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function checkForCode($admin_id) {
        $code = [];
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes 
            WHERE 
                year = :year 
                AND admin_id = :admin 
                AND type = :type
                AND used = 0
        ");
        $stmt->execute([
            ':year' => $this->year,
            ':admin'=> $admin_id,
            ':type' => $this->type
        ]);
        $row = $stmt->fetch();
        if ($row['value']) {
            $code['id'] = $row['coupon_code_id'];
            $code['amount'] = $row['value'];
        }
        return $code;
    }

    public function checkForUserCode($user_serial) {
        $amount = 0;
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes 
            WHERE 
                year = :year 
                AND serial_num = :user 
                AND type = :type
                AND used = 0
        ");
        $stmt->execute([
            ':year' => $this->year,
            ':user' => $user_serial,
            ':type' => $this->type
        ]);
        $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $amount += array_reduce($rows, function($carry, $item) {
            return $carry + intval($item['value']);
        }, 0);

        return $amount;
    }

    public function checkForUsedUserCode($user_serial) {
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes 
            WHERE 
                year = :year 
                AND serial_num = :user 
                AND type = :type 
                AND used = 1
        ");
        $stmt->execute([
            ':year' => $this->year,
            ':user' => $user_serial,
            ':type' => $this->type
        ]);
        $row = $stmt->fetch();
        if ($row['value']) {
            return $row['value'];
        }
        return false;
    }

    public function useUserCode($user_serial) {
        $stmt = $this->db->prepare("
            UPDATE coupon_codes 
            SET used = 1, date_redeemed = now() 
            WHERE year = :year 
            AND serial_num = :serial
            AND type = :type
        ");
        $stmt->execute([
            ':year'     => $this->year,
            ':serial'   => $user_serial,
            ':type'     => $this->type
        ]);

        if ($stmt->errorCode() > 0) return false;
        else return true;
    }
}