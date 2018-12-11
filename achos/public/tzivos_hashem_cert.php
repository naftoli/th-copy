<?
//print_r( $_GET );
$school_id = $_GET['school'];
$class_id = $_GET['class'];
$user_id = $_GET['user'];

require 'db.php';

$names = array();
$sql = "select first, last, first_he, last_he 
        from users 
        where school_id = $school_id 
        and user_registered > 0";
if ( $class_id > 0 ) {
    $sql .= " and class_id = " . $class_id;
}
if ( $user_id > 0 ) {
    $sql .= " and user_id = " . $user_id;
}

$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    if ( !empty( $row['first_he'] ) && !empty( $row['last_he'] ) ) {
        $names[] = $row['first_he'] . '<br />' . $row['last_he'];
    } else {
        $names[] = $row['first'] . '<br />' . $row['last'];
    }
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            @font-face {
                font-family: DirtyEgo;
                src: url('fonts/DIRTYEGO.TTF');
                
            }
            @font-face {
                font-family: Gothic;
                src: url('fonts/HWYGWDE.TTF');
                
            }
            .page {
                width: 11in.;
                height: 6.5cm;
            }
            .name {
                margin: auto;
                width: 16cm;
                font-size: 90px;
                font-weight: bold;
                text-align: center;
                font-family: DirtyEgo;
            }
            .page-break {
                page-break-after: always;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
            @media screen {
                .no-print { 
                    margin-left: 38%;
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 12px;
                }
                .print {
                    margin-left: 16%;
                }
            }
        </style>
    </head>
    
    <body>
        <div class="no-print">
            <p>Printing Instructions:<br />
            Step 1: Set the Orientation to <u>Landscape</u><br />
            Step 2: Check 'Shrink to fit Page Width'<br />
            Step 3: In Options check 'Print Background (colors & images)'<br />
            Step 4: In the second tab set all Margins to 0.0 inches (All Sides)<br />
            Step 5: Set all Headers & Footers to Blank</p>
            <p class='print'>
                <input type="button" value="Print" onclick="window.print()" />
            </p>
        </div>
        <? foreach ( $names as $name ) { ?> 
        <div class="page"></div>
        <div class="name">
            <?=$name?>
        </div>
        <div class="page-break"></div>
        <? } ?>
    </body>
</html>
