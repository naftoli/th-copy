<?php
namespace traits;

trait SetRelatedModel {
    public function setRelatedModel( $name, $value ) {
        // If a relationship is assigned an object, translate it to a reference id
        $table = parent::table();
        if ( ( $table->has_relationship( $name ) ) && ( $class = get_class( $value ) ) ) {
            $class = '\\' . $class; // remove opening slash
            $relation = $table->get_relationship( $name );
            // if the name matches the relationship
            if ( $class == $relation->class_name ) {
                if ( ( $relationship = $table->get_relationship($name) ) )
                    $this->set_relationship_from_eager_load( $value, $name );
                // set the foreign key
                return $this->assign_attribute($relation->foreign_key[0], $value->id);
            }
            else {
                throw new \RelationshipException();
            }
        }
        throw new \RelationshipException();
	}
}