<?php
class School extends ActiveRecord\Model {
    // relationships
    static $has_many = [ ['school_reg_infos'] ];
}