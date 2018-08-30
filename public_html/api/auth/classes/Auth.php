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

    public static function login( $username, $password ) {
        global $MASHPIA_DB;
        $username = strtolower( $username );

        $user_query = $MASHPIA_DB->prepare(
            "SELECT admin_id, username, password FROM admins WHERE username = ? and password = ?"
        );
        $user_query->execute([ $username, $password ]);

        if ( $user_query->rowCount() == 0 ) return false;

        $row = $user_query->fetch();
        // generate the keys
        $legacy = self::legacyKey( $row['username'], $row['password'] );
        $mobile = self::mobileKey( $row['admin_id'] );
        // set the cookies
        $_COOKIE['admin'] = $row['admin_id'];
        $_COOKIE['admin_id'] = $mobile;
        $_COOKIE['admin_auth'] = $legacy;
        // return the results
        return [
            'id' => $row['admin_id'],
            'legacy' => $legacy,
            'mobile' => $mobile
        ];
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
        global $MASHPIA_DB;

        $query = $MASHPIA_DB->prepare(
            "SELECT username, password FROM admins WHERE admin_id = ?;"
        );
        $query->execute([ $admin_id ]);
        $row = $query->fetch();

        $valid_key = self::legacyKey( $row['username'], $row['password'] );

        return $key === $valid_key ? $admin_id : false;
    }

    private static function legacyKey( $username, $password ){
        return hash_hmac(
            'ripemd128', strtolower( $username ) . $password, 
            '53fdc95857aac68970159dd07e7c3782' 
        );
    }

    public static function mobileKey( $admin_id ) {
        require_once( __DIR__ . '/../../../mobile/reg/ajax/encrypt.php' );
        return encrypt_decrypt('encrypt', $admin_id);
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