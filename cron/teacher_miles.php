<?php
// load up the database connection
include_once( __DIR__ . '/../public_html/api/header/db.php' );

$status = $MASHPIA_DB->query(<<<'SQL'
UPDATE classes JOIN (
    SELECT class_id, SUM(miles_per_soldier) AS total, miles_balance
    FROM classes LEFT JOIN users USING (class_id)
    GROUP BY class_id
) c USING (class_id) 
SET classes.miles_balance = (c.miles_balance + c.total);
SQL
);

if ( $status ) {
    echo "Miles Successfully Awarded\n";
    die();
}

echo $MASHPIA_DB->errorInfo()."\n";
die();
