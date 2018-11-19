<?php
define( 'MASHPIA_AUTH_REQUIRED', true );
require_once( __DIR__ . '/../header/header.php' );

require_once( __DIR__ . '/../../chabad_org/classes/ChabadShliach.php' );

class ProfilesRouter {

    public function index() {
        global $current_user;
        return json_response(
            $this->serializeAccount( $current_user )
        );
    }

    // functions as update
    public function create() {
        global $current_user;

        if ( isset( $_POST['password']) && !$_POST['password'] )
            unset( $_POST['password'] );

        if ( isset( $_POST['username']) && !$_POST['username'] )
            unset( $_POST['username'] );
        
        // old validation code TODO remove
        if ( isset( $_POST['password']) &&
            isset( $_POST['old_password'] ) &&
            $_POST['old_password'] !== $current_user->password
        ) return json_error('Invalid Password. No updates applied');

        // current validation code. TODO refactor
        if ( ( isset( $_POST['password']) || isset( $_POST['username']) ) &&
            isset( $_POST['current_password'] ) &&
            $_POST['current_password'] !== $current_user->password
        ) return json_error('Current Password incorrect. No updates applied');

        $current_user->bulkUpdate( $_POST );

        if ( !$current_user->is_valid() )
            return json_error( $current_user->errors->__toString() );

        if ( !$current_user->save() )
            return json_error( 'Could not update account information' );

        return json_response([
            'account' => $this->serializeAccount( $current_user ),
            'tokens' => mashpia\api\auth\Auth::login(
                $current_user->username,
                $current_user->password
            )
        ]);
    }

    public function connectAccount() {
        global $current_user;
        $key = $_POST['key'];   $type = $_POST['type'];
        // connect chabad.org account
        if ( $type === 'chabad' ) {
            $shliach = new \ChabadShliach( $key );
            // make sure the token is valid
            if ( !$shliach->authenticate() )
                return json_error('Chabad.org key invalid');
            // set the shliach id
            $current_user->shliach_id = $shliach->shliachID;
        // connect google account
        } else if ( $type === 'google' ) {
            $client = new \Google_Client([ 'client_id' => GOOGLE_CLIENT_ID, 'client_secret' => GOOGLE_CLIENT_SECRET ]);
            $payload = $client->verifyIdToken( $key );
            // make sure the token was valid
            if ( !$payload )
                return json_error('Google token invalid');
            // get the google_id
            $current_user->google_id = $payload['sub'];
        }
        // save the updates
        if ( !$current_user->is_valid() || !$current_user->save() )
            return json_error('Another account is already this external account for login.');
        // return the status of both keys
        return json_response([
            'google_id' => $current_user->google_id,
            'chabad_org_shliach_id' => $current_user->shliach_id
        ]);
    }

    public function disconnectAccount() {
        global $current_user;
        $type = $_POST['type'];
        // connect chabad.org account
        if ( $type === 'chabad' )
            $current_user->shliach_id = null;
        // connect google account
        else if ( $type === 'google' )
            $current_user->google_id = null;
        // make sure that we can save
        if ( !$current_user->is_valid() || !$current_user->save() )
            return json_error('Could not disconnect external account access');
        // save update
        return json_response([
            'google_id' => $current_user->google_id,
            'chabad_org_shliach_id' => $current_user->shliach_id
        ]);
    }

    private function serializeAccount( $admin ) {
        return $admin->to_array([
            'only' => [
                'admin_id', 'username', 'title', 'first', 'last', 'lang',
                'home_phone', 'cell_phone', 'admin_email', 'chabad_org_shliach_id',
                'admin_phone_work', 'admin_phone_mobile', 'photo', 'google_id'
            ],
            'methods' => [ 'logins', /* 'customerProfile' */ ]
        ]);
    }
}

rest_router( new ProfilesRouter );
