<?
$sqlReg = "select count(*) as registered 
        from users u
        where u.user_registered > 0 
        and u.school_id = $school_id";
$resReg = mysql_query( $sqlReg );
$rowReg = mysql_fetch_assoc( $resReg );
$registered = $rowReg['registered'];

$sqlTeacher = "select count(*) as teachers 
        from classes c 
        where c.school_id = $school_id  
        and c.class_era = 0";
$resTeacher = mysql_query( $sqlTeacher );
$rowTeacher = mysql_fetch_assoc( $resTeacher );            
$teachers = $rowTeacher['teachers'];
?>