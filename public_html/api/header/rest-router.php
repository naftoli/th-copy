<?php

function rest_router( $router ){
    // get ID from GET or POST
    $id = isset( $_GET['id'] ) ? $_GET['id'] : false;
    if ( !$id ) $id = isset( $_POST['id'] ) ? $_POST['id'] : false;

    if ( method_exists( $router, 'authenticate' ) && !$router->authenticate() ){
        json_error( "Access Denied" );
    }

    if ( isset( $_GET['action'] ) && $_GET['action'] ){
        if ( method_exists( $router, $_GET['action']) ) {
            return $router->{ $_GET['action'] }( $id );
        }
    } else if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
        
        if ( $id && method_exists( $router, 'show' ) ) {
            return $router->show( $id );
        } else if ( method_exists( $router, 'index' ) ) {
            return $router->index();
        }

    } else if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
        
        if ( $id && method_exists( $router, 'update' ) ) {
            return $router->update( $id );
        } else if ( method_exists( $router, 'create' ) ) {
            return $router->create();
        }

    } else if ( $_SERVER['REQUEST_METHOD'] == "DELETE" && $id && method_exists( $router, 'destroy' ) ) {
        
        return $router->destroy( $id );

    } 

    http_response_code( 404 );
}

// interface to note that all functions are implamented
interface RestRouter {
    public function index();
    public function show( $id );
    public function create();
    public function update( $id );
    public function destroy( $id );
}