<?php
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

    public static function updateSchoolExceptions($prize_id, $exceptions) {
        $qrys = [];
        // first delete all existing exceptions for this prize
        $qrys[] = "delete from chidon_prize_school_exceptions where prize_id = $prize_id";
        foreach ($exceptions as $exception) {
            // then insert the new exceptions
            if (is_numeric($exception) && is_numeric($prize_id))
                $qrys[] = "insert into chidon_prize_school_exceptions (prize_id, school_id) values ($prize_id, $exception)";
        }

        mysql_query('set autocommit=0');
        mysql_query('start transaction');

        $succeeded = true;
        foreach ($qrys as $qry) {
            if (!mysql_query($qry)) {
                $succeeded = false;
                $error = $qry;
                break;
            }
        }

        if ($succeeded) {
            mysql_query('commit');
            mysql_query('set autocommit=1');
        } else {
            mysql_query('rollback');
            mysql_query('set autocommit=1');
        }

        return [$succeeded, $error];
    }
}