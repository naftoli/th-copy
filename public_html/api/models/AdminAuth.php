<?php
class AdminAuth extends ActiveRecord\Model {
    static $belongs_to = [
        [ 'admin' ]
    ];
}