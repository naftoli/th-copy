<?php
class SchoolRegInfo extends ActiveRecord\Model {
    // relationships
    static $belongs_to = [ ['school'] ];
}