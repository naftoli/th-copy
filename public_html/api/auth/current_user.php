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

    // * connects the current_user account to the correct chabad.org shliach_id
    public function connectChabad() {
        global $current_user; global $MASHPIA_DB;

        $key = $_POST['key'];

        $shliach = new \ChabadShliach( $key );
        // make sure the token is valid
        if ( !$shliach->authenticate() )
            return json_error('Chabad.org key invalid');

        $current_user->shliach_id = $shliach->shliachID;

        if ( !$current_user->is_valid() || !$current_user->save() )
            return json_error('Another account is already this Chabad.org account to login.');

        return json_response([ 'chabad_org_shliach_id' => $current_user->shliach_id ]);
    }

    public function disconnectChabad() {
        global $current_user;

        $current_user->shliach_id = null;

        if ( !$current_user->is_valid() || !$current_user->save() )
            return json_error('Could not disconnect Chabad.org Account Access');

        return json_response([ 'chabad_org_shliach_id' => $current_user->shliach_id ]);
    }

    private function serializeAccount( $admin ) {
        return $admin->to_array([
            'only' => [
                'admin_id', 'username', 'title', 'first', 'last', 'lang',
                'home_phone', 'cell_phone', 'admin_email', 'chabad_org_shliach_id',
                'admin_phone_work', 'admin_phone_mobile', 'photo',
            ],
            'methods' => [ 'logins', /* 'customerProfile' */ ]
        ]);
    }
}

rest_router( new ProfilesRouter );
