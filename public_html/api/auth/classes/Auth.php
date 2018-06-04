<?php
namespace mashpia\api\auth;

class Auth {
    /**
     * Auth::authenticate
     * 
     * Authenticate the user params that are passed in. If valid return ID, else return false.
     *
     * @param array $token
     * @param string $type
     * @return int/boolean
     */
    public static function authenticate( $token, $type ) {
        if ( $type == 'legacy' )
            return self::legacyAuth( $token['admin_id'], $token['key'] );
        else if ( $type == 'mobile' )
            return self::mobileAuth( $token['key'] );
    }

    /**
     * self::legacyAuth
     * 
     * handles legacy mashpia.com authentication
     *
     * @param string $admin_id
     * @param string $key
     * @return int/boolean
     */
    private static function legacyAuth( $admin_id, $key ) {
        global $pdo;

        $query = $pdo->prepare(
            "SELECT username, password FROM admins WHERE admin_id = ?;"
        );
        $query->execute([ $admin_id ]);
        $row = $query->fetch();

        $valid_key = hash_hmac(
            'ripemd128', strtolower($row['username']) . $row['password'], 
            '53fdc95857aac68970159dd07e7c3782' 
        );

        return $key === $valid_key ? $admin_id : false;
    }

    /**
     * mobileAuth
     * 
     * handles authentication from mobile site
     *
     * @param string $key
     * @return int/boolean
     */
    private static function mobileAuth( $key ){
        require_once( __DIR__ . '/../../../mobile/reg/ajax/encrypt.php' );
        return encrypt_decrypt('decrypt', $key);
    }
}