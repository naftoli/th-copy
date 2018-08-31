<?php
define( 'MASHPIA_AUTH_REQUIRED', true );
require_once( __DIR__ . '/../header/header.php' );

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

    private function serializeAccount( $admin ) {
        return $admin->to_array([
            'only' => [
                'admin_id', 'username', 'title', 'first', 'last', 'lang',
                'home_phone', 'cell_phone', 'admin_email', 'chabad_org_shliach_id',
                'admin_phone_work', 'admin_phone_mobile', 'photo',
                'admin_address1', 'admin_address2', 'admin_city', 'admin_state', 
                'admin_postal', 'admin_country'
            ],
            'methods' => [ 'logins', /* 'customerProfile' */ ]
        ]);
    }
}

rest_router( new ProfilesRouter );
