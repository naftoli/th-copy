<?php
class AdminAuth extends ActiveRecord\Model {
    static $belongs_to = [
        [ 'admin' ],
        [ 'user',
            'foreign_key' => 'id', # key in linked (this) table
            'primary_key' => 'user_id',  # key in "parent" table
        ]
    ];
}