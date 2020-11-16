<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if ( isset( $_FILES['chidon'] ) ) {
    $name = $_FILES['chidon']['tmp_name'];
    if ( $handle = fopen($name, "r") ) {
        while ( $data = fgetcsv( $handle ) ) {
            $size = strtolower( $data[0] );
            $book = $data[1];
            $serial = $data[2];

            $sql = "
                SELECT 
                    user_id, school_id, admin_id 
                FROM
                    users u
                        JOIN
                    admin_auths aa ON aa.id = u.user_id
                WHERE
                    u.user_serial = " . $serial;
            $result = mysql_query( $sql );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $row['book'] = $book;
                $row['size'] = $size;
                $info[] = $row;
            }
        }
        //echo "<pre>"; print_r( $info ); echo "</pre>"; exit;

        $updated = 0;
        $success = true;
        mysql_query('set autocommit=0');
        mysql_query('begin');

        foreach ( $info as $row ) {
            $school_id = $row['school_id'];
            $user_id = $row['user_id'];
            $parent = $row['admin_id'];
            $book = $row['book'];
            $size = $row['size'];

            if ( $user_id ) {
                $sql = "insert into registration_charges 
                        set user_id = " . $user_id . ", 
                        school_id = " . $school_id . ", 
                        type = 'chidon', 
                        amount = 10.00, 
                        date = now(), 
                        year = " . $year;
                //echo $sql . "<br />";
                if ( !mysql_query( $sql ) ) {
                    $success = false;
                    break;
                }
                $sql = "insert into th_chidon 
                        set year = " . $year . ", 
                        school_id = " . $school_id . ", 
                        user_id = " . $user_id . ", 
                        size = '" . $size . "', 
                        reg_date = now(), 
                        book = " . $book . ", 
                        parent_id = " . $parent;
                //echo $sql . "<br /><br />";
                if ( !mysql_query( $sql ) ) {
                    $success = false;
                    break;
                }
                $updated++;
            }
        }

        if ( $success ) {
            echo "Updated: " . $updated;
            mysql_query('commit');
        } else {
            echo mysql_error();
            mysql_query('rollback');
        }
        mysql_query('set autocommit=1');
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
        <form action="register_for_chidon_csv.php" method="post" enctype="multipart/form-data">
            <input type="file" name="chidon" />
            <input type="submit" name="sumbit" value="submit" />
        </form>
    </body>
</html>