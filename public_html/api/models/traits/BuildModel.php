<?php
namespace traits;

trait BuildModel {
    public static function build( $attributes ) {
        $instance = new self( [], true, false, false );
        $valid_attributes = array_keys( $instance->attributes() );
        foreach( $attributes as $key => $value ){
            if ( in_array( $key, $valid_attributes ) )
                $instance->{ $key } = $value;
        }
        return $instance;
    }
}