<?php
$admin_auth = array('school');
require_once( $_SERVER['DOCUMENT_ROOT'] . "/header.php" );

/****************** MISHNA INFORMATION ******************/
require_once( $_SERVER['DOCUMENT_ROOT'] . "/class.mishnaInfo.php" );

$he_chars = array(
	1	=>	'א',	2	=>	'ב',	3	=>	'ג',	4	=>	'ד',	5	=>	'ה',
	6	=>	'ו',	7	=>	'ז',	8	=>	'ח',	9	=>	'ט',	10	=>	'י',
	11	=>	'יא',	12	=>	'יב',	13	=>	'יג',	14	=>	'יד',	15	=>	'טו',
	16	=>	'טז',	17	=>	'יז',	18	=>	'יח',	19	=>	'יט',	20	=>	'כ',
	21	=>	'כא',	22	=>	'כב',	23	=>	'כג',	24	=>	'כד',	25	=>	'כה',
	26	=>	'כו',	27	=>	'כז',	28	=>	'כח',	29	=>	'כט',	30	=>	'ל'
);

$mishna_bulk_query = mysql_query(
    "SELECT sedorim.seder_id, seder, mesechto_id, mesechto, perek, mishna, num_lines 
    FROM mishnos
    JOIN sedorim USING (seder_id)
    JOIN mesechtos USING (mesechto_id)
    ORDER BY sedorim.seder_id, mesechtos.mesechto_id , perek , mishna"
);

$mishna_info = []; $seder_names = []; $mesechto_names = [];

while( $row = mysql_fetch_assoc( $mishna_bulk_query ) ){
    $mishna_info[ $row['seder_id'] ][ $row['mesechto_id'] ][ $row['perek'] ][ $row['mishna'] ] = $row['num_lines'];

    if ( !isset( $seder_names[ $row['seder_id'] ] ) )
        $seder_names[ $row['seder_id'] ] = $row[ 'seder' ];

    if ( !isset( $mesechto_names[ $row['mesechto_id'] ] ) )
        $mesechto_names[ $row['mesechto_id'] ] = $row[ 'mesechto' ];
}
// go through the sedorim
$response = [];

foreach( $mishna_info as $sader_id => $mesechtos ) {
    $sader_item = ["name" => $seder_names[ $sader_id ], "mesechtos" => []];
    
    foreach( $mesechtos as $mesechto_id => $perokim ) {
        $mesechto_item = [ "name" => $mesechto_names[ $mesechto_id ], "perokim" => [] ];

        foreach( $perokim as $perek => $mishnos ) {
            $perek_item = [ "name" => ("פרק " . $he_chars[ intval($perek) ]), "mishnos" => [] ];

            foreach( $mishnos as $mishna => $num_lines ) {
                $perek_item["mishnos"][] = [ "name" => ("Mishna " . $he_chars[ $mishna ]), "lines" => $num_lines ];
            }

            $mesechto_item["perokim"][] = $perek_item;
        }
        $sader_item["mesechtos"][] = $mesechto_item;
    }
    $response[] = $sader_item;
}

echo json_encode( $response );