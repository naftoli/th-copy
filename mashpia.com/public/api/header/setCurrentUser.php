<?php

function setCurrentUser(){
  include_once( __DIR__ . "/../auth/classes/Auth.php" );
  $headers = getallheaders();

  // handle authorization header
  if ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) && $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) {
    $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
  }
  // handle authorization header case sensitivity
  if ( isset( $headers['authorization'] ) && $headers['authorization'] && !$headers['Authorization'] ) {
    $headers['Authorization'] = $headers['authorization'];
  }

  // handle login case sensitivity
  if( isset( $headers['Login'] ) && !isset( $headers['login'] ) ) {
    $headers['login'] = $headers['Login'];
  }

  // check if we are explicitly told that we are on mobile
  $mobile = false;
  if ( defined('MASHPIA_AUTH_MOBILE') && MASHPIA_AUTH_MOBILE )
      $mobile = true;

  // check if we have the proper header set or are coming from /mobile
  if (  ( isset( $headers['mobile'] ) && $headers['mobile'] === 'true' ) || 
        ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], '/mobile' ) > 0 )
  ) {
    $mobile = true;
  }
  
  // get the login token
  $token = false;
  if ( isset( $headers['Authorization'] ) && $headers['Authorization'] ) {
      $token = explode( ' ',  $headers['Authorization'] )[1];
  }

  // set the token as cookies
  if ( $token ) {
    if ( $mobile ) {
        $_COOKIE['admin'] = $token;
    } else {
        $_COOKIE['admin_id'] = explode( '-', $token )[0];
        $_COOKIE['admin_auth'] = explode( '-', $token )[1];
    }
  }

  // get the current user
  $admin_id = false;
  if ( $mobile && $_COOKIE['admin'] ) {
      $admin_id = \mashpia\api\auth\Auth::authenticate(
          [ "key" => $_COOKIE['admin'] ], "mobile"
      );
  } else if ( isset( $_COOKIE['admin_auth'] ) && isset( $_COOKIE['admin_id'] ) ) {
      $admin_id = \mashpia\api\auth\Auth::authenticate(
          [ "key" => $_COOKIE['admin_auth'], "admin_id" => $_COOKIE['admin_id'] ],
          "legacy"
      );
  }
  // get the current users from the admin id
  $current_user = $admin_id ? \Admin::find([ $admin_id ]) : false;

  // return false if there is no current user
  if ( !$current_user ) {
    return false;
  }

  // if we are on mobile use the parent login
  if ( $mobile ) {
    $current_user->setLogin( 'PARENT', $current_user->admin_id, true );
  }

  if ( !$mobile ) { // get the current login
    $login_parts = [];
    // check for the header
    if ( isset( $headers['login'] ) ) {
        $login_parts = explode('-', $headers['login']);
    // do the same if it is in a cookie
    } else if ( isset( $_COOKIE['login'] ) ) {
        $login_parts = explode( '-', $_COOKIE['login'] );
    }
    // if we have the correct amount of parts
    if ( count($login_parts) == 2 ) { // set the login below
      $current_user->setLogin( $login_parts[0], $login_parts[1] );
    }
  // for mobile make sure it is the parent login (even if it does not exist yet)
  } else {
      $current_user->setLogin( 'PARENT', $current_user->admin_id, true );
  }

  // make sure we always have a login
  if ( !$current_user->login ) {
    $current_user->setLogin();
  }

  return $current_user;
}