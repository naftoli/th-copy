<?php
namespace mashpia\api\auth;
require_once( __DIR__ . '/../../../chabad_org/classes/ChabadShliach.php' );

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
        // cast the username to lowercase
        $username = strtolower( $username );
        // find the account with the username and password
        $admin = \Admin::find_by_username( $username );
        // if the username does not exist, try to find the account with that email
        if ( !$admin ) {
            $admin = \Admin::find_by_admin_email( $username );
        }
        // if the password is valid, authenticate the user
        if ( $admin && $admin->authenticate( $password ) ) {
            return self::generateKeys( $admin  );
        }

        return false;
    }

    public static function chabadLogin( $key ) {
        // make sure the token is valid
        $shliach = new \ChabadShliach( $key );
        if ( !$shliach->authenticate() )
            return false;

        $admin = \Admin::find_by_chabad_org_shliach_id( $shliach->shliachID );

        if ( $admin ) // check if there is even a single admin
            return self::generateKeys( $admin );
        
        $shliach->setPersonalInfo();
        $shliach->setMosdos();

        return $shliach;
    }

    public static function googleLogin( $id_token ) {
        // load the google client
        $client = new \Google_Client([ 'client_id' => GOOGLE_CLIENT_ID, 'client_secret' => GOOGLE_CLIENT_SECRET ]);
        // parse the JSON web token
        $payload = $client->verifyIdToken( $id_token );
        // make sure it has a payload
        if ( !$payload )
            return false;
        // get the google_id
        $google_id = $payload['sub'];
        $admin = \Admin::find_by_google_id( $google_id );

        if ( $admin ) // check if there is even a single admin
            return self::generateKeys( $admin );
        
        return false;
    }

    private static function generateKeys( $admin ){
        // make sure we have an admin
        if ( !$admin instanceof \Admin )
            return false;

        // generate the keys
        $legacy = self::legacyKey( $admin->username, $admin->password );
        $mobile = self::mobileKey( $admin->admin_id );
        // set the cookies
        $_COOKIE['admin'] = $admin->admin_id;
        $_COOKIE['admin_id'] = $mobile;
        $_COOKIE['admin_auth'] = $legacy;
        // return the results
        return [
            'id' => $admin->admin_id,
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