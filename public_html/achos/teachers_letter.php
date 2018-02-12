<?php
$admin_auth = array('school');
require('header.php');
$admin_id = $admin_user['admin_id'];

$sql = "select id from admin_auths where admin_id = " . $admin_id;
$res = mysql_query( $sql );
$r = mysql_fetch_assoc( $res );
$school_id = $r['id'];

//check for hebrew schools
$h_school = false;
$sql = "select inst_id from schools where school_id = " . $school_id;
$res = mysql_query( $sql );
$row = mysql_fetch_assoc( $res );
$inst_id = $row['inst_id'];
if ( $inst_id == 4 ) {
    $h_school = true;
}
            
$sql = "SELECT s.school_id, s.school_name ";
$sql = $sql . "FROM admin_auths AS aa ";
$sql = $sql . "JOIN schools AS s ON (aa.id = s.school_id) ";
$sql = $sql . "WHERE aa.admin_id=" . $admin_id . " AND aa.auth = 'school'";
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
if ($num_rows > 1) {
    $select = "<SELECT name='school_id'>";
    while ($row = mysql_fetch_assoc($query)) {
        $select = $select . "<OPTION value='" . $row['school_id'] . "'>" . $row['school_name'] . "</OPTION>";
    }
    $select = $select . "</SELECT>";
}
else {
    $row = mysql_fetch_assoc($query);
    produce_report($row['school_id']);
}

function produce_report($school_id) {
    global $children;
    global $director_first;
    global $director_last;
    global $director_title;
    
    if ($school_id == 0) $school_id = $_POST['school_id'];
    
    // PROGRAM DIRECTOR //
    $sql = "SELECT a.title, a.first, a.last, a.admin_phone_work FROM admin_auths AS aa JOIN admins AS a USING (admin_id) WHERE aa.auth='school' AND aa.id=" . $school_id . " AND aa.role_id=18";
    $query = mysql_query($sql);
    $row = mysql_fetch_assoc($query);
    $director_title = $row["title"];
    $director_first = $row["first"];
    $director_last = $row["last"];
    $director_phone = trim($row["admin_phone_work"]);
    
    $sql = "SELECT a.first, a.last ";
    $sql = $sql . "FROM admin_auths AS aa ";
    $sql = $sql . "JOIN admins AS a USING (admin_id) ";
    $sql = $sql . "WHERE aa.id=" . $school_id . " AND aa.role_id=18";
    $query = mysql_query($sql);
    $row = mysql_fetch_assoc($query);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

    <HEAD>
        <TITLE>Teacher's Letter</TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <STYLE type="text/css">
        </STYLE>
        
        <script>
        </script>
        
        <style type="text/css">
            noprint {
                .noprint { 
                    display: none; 
                }
            }
            .letter {
                width: 7in;
            }
        </style>
        
    </HEAD>
    
    <BODY>
<DIV class="noprint">
    <input type="button" value="PRINT" onclick="window.print();"> 
</DIV>
            
            <br />
                    
            <DIV class="letter" style="position:absolute; left:50px;">
                    
                    <br />
                    <br />
                    <br />
                    
                    <DIV style="text-align:left;">
                        Dear Teachers, שיחיו
                    </DIV>
                    <br />
                    <br />
                    <br />
                    
                    <DIV style="text-align:left; font-weight: bold">
                        We need your help!
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        We are thrilled to open our fifth year of Chayolei Tzivos Hashem. We have simplified the program and created many new features for this year in order that you can integrate Tzivos Hashem in your class curriculum.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        We added a special Teacher Calendar for your convenience. The Commanders Calendar will give you a clear picture of what needs to be taught each week for the entire year, so that you can plan accordingly.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        Each week we will be sending you a Hachayol magazine with a lesson plan so that you can focus on what the children need to know that week.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        By simply following the instructions in the teacher calendar and using the weekly Hachayol Magazine and teacher resources, you can correctly and easily incorporate Tzivos Hashem in your class.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        Please understand that in order for Tzivos Hashem to work in your class, every student must be an active soldier. Please ensure that all students in your class register and are a part of Tzivos Hashem.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        Your excitement of the program as well as your constant support and encouragement to your students is what makes Tzivos Hashem a success!
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        I am here to help you out in any way you need to keep your platoon (class) and soldiers (students) running in the most structured and productive way possible!
                    </DIV>
                    <br />
                    <br />
                    <br />
                    
                    <DIV style="text-align:left;">
                        Sincerely,
                    </DIV>
                    
                    <DIV style="text-align:left;">
                        <br />
                        <?=$director_title;?> <?=$director_first;?> <?=$director_last;?>
                        <?
                        if (!empty($director_phone)) {
                            echo "<br />";
                            echo $director_phone;
                        }
                        ?>
                    </DIV>
                        
                    <DIV style="height:100px;">
                    </DIV>
                    
                    <DIV style="page-break-after:always">
                    </DIV>
                
            </DIV>         
        
    </BODY>
    
</HTML>
