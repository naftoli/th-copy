<?php

function rest_router( $router ){
    // get ID from GET or POST
    $id = isset( $_GET['id'] ) ? $_GET['id'] : false;
    if ( !$id ) $id = isset( $_POST['id'] ) ? $_POST['id'] : false;

    if ( method_exists( $router, 'authenticate' ) && !$router->authenticate() ){
        json_error( "Access Denied" );
    }

    if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
        if ( $id ) {
            return $router->show( $id );
        } else {
            return $router->index();
        }
    } else if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
        if ( $id ) {
            return $router->update( $id );
        } else {
            return $router->create();
        }
    } else if ( $_SERVER['REQUEST_METHOD'] == "DELETE" && $id ) {
        return $router->destroy( $id );
    } else if ( $_GET['action'] && method_exists( $router, $_GET['action']) ) {
        return $router->{ $_GET['action'] }( $id );
    }
}

interface RestRouter {
    public function index();
    public function show( $id );
    public function create();
    public function update( $id );
    public function destroy( $id );
}