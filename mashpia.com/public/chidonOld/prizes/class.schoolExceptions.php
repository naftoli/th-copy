<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

class SchoolExceptions {
    public static function getSchoolExceptions() {
        $exceptions = [];
        $sql = "select * from chidon_prize_school_exceptions";
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $exceptions[$row['prize_id']][] = $row['school_id'];
        }
        return $exceptions;
    }

    public static function getSchoolExceptionsByPrize($id) {
        $exceptions = [];
        $sql = "select * from chidon_prize_school_exceptions where prize_id = $id";
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $exceptions[] = $row['school_id'];
        }
        return $exceptions;
    }
}