<?php

function rest_router( $router ){
    global $current_user;
    // get ID from GET or POST
    $id = isset( $_GET['id'] ) ? $_GET['id'] : false;
    // if ( !$id ) $id = isset( $_POST['id'] ) ? $_POST['id'] : false;
    try {
        // authenticate()
        if ( method_exists( $router, 'authenticate' ) && !$router->authenticate( $current_user ) ){
            json_error( "Access Denied" );
        }
        // ?action=<action>
        if ( isset( $_GET['action'] ) && $_GET['action'] ){
            if ( method_exists( $router, $_GET['action']) ) {
                return $router->{ $_GET['action'] }( $id );
            }
        // GET
        } else if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
            // GET with ID calls show
            if ( $id && method_exists( $router, 'show' ) ) {
                return $router->show( $id );
            // GET without an ID calls index
            } else if ( method_exists( $router, 'index' ) ) {
                return $router->index();
            }
        // POST
        } else if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
            // post with an ID calls update
            if ( $id && method_exists( $router, 'update' ) ) {
                return $router->update( $id );
            // post without an id calls create
            } else if ( method_exists( $router, 'create' ) ) {
                return $router->create();
            }
        // PATCH
        } else if ( $_SERVER['REQUEST_METHOD'] == "PATCH" ) {
            // PATCH calls update
            if ( method_exists( $router, 'update' ) ) {
                return $router->update( $id );
        } 
        // DELETE with an ID calls destroy
        } else if ( $_SERVER['REQUEST_METHOD'] == "DELETE" && $id && method_exists( $router, 'destroy' ) ) {
            return $router->destroy( $id );
        // DELETE without an id calls delete
        } else if ( $_SERVER['REQUEST_METHOD'] == "DELETE" && method_exists( $router, 'delete' ) ) {
            return $router->delete();
        } 
        // Default 404 response
        http_response_code( 404 );
    } catch ( Exception $e ) {
        return json_error( $e->getMessage() );
    }
}

// interface to note that all functions are implamented
interface RestRouter {
    public function index();
    public function show( $id );
    public function create();
    public function update( $id );
    public function destroy( $id );
}