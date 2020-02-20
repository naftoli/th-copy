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
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()_-+={}[]<>?,.';
        $shuffled = str_shuffle( $permitted_chars );
        return substr( $shuffled, 0, $length );
    }

    public function codeExists( $code ) {
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes WHERE code = :code
        ");
        $stmt->execute([':code' => $code]);
        $rows = $stmt->fetchAll();
        if ( !empty( $rows ) ) {
            return true;
        } 
        return false;
    }

    public function isValidCode( $code ) {
        $stmt = $this->db->prepare("
            SELECT * FROM coupon_codes WHERE code = :code AND used = 0
        ");
        $stmt->execute([':code' => $code]);
        if ( $row = $stmt->fetch() ) {
            return $row['value'];
        }
        return false;
    }

    public function saveCode( $value, $created_by, $reason ) {
        $stmt = $this->db->prepare("
            INSERT INTO coupon_codes 
            SET 
                code = :code, 
                value = :value, 
                type = :type, 
                year = :year, 
                created_by = :created_by, 
                reason = :reason
        ");
        $res = $stmt->execute([
            ':code'     => $this->code, 
            ':value'    => $value, 
            ':type'     => $this->type, 
            ':year'     => $this->year,
            ':created_by' => $created_by, 
            ':reason'   => $reason
        ]);
        if ( $res ) return true;
        return false;
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
}