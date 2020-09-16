<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$schools = [];
$schoolsStmt = $MASHPIA_DB->query("SELECT * FROM schools");
foreach ( $schoolsStmt->fetchAll() as $row ) {
    $schools[$row['school_id']] = $row['school_name'];
}

$stmt = $MASHPIA_DB->prepare("
    SELECT a.*, u.first as uFirst, u.last as uLast FROM users u 
    JOIN admin_auths aa ON aa.id = u.user_id 
    JOIN admins a USING (admin_id) 
    WHERE u.user_serial = :serial
");

$info = [
    'Parent Registration (chayolei: 165 chidon: 20) 5781: 7749611:105, 7754259:105, 7763425:105, 7774578:105',
    'Parent Registration (chayolei: 110 chidon: 20 yahadus: 40) 5781: 7749404:40, 7759577:11, 7772555:11',
    'Parent Registration (chayolei: 100 chidon: 20 yahadus: 80) 5781: 7756517:4, 7769873:162, 7774465:4',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7749757:265, 7760734:265',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7749239:7, 7755535:255',
    'Parent Registration (chayolei: 165 chidon: 20 yahadus: 40) 5781: 7744971:48, 7747440:48, 7764322:48, 7770940:48',
    'Parent Registration (chayolei: 55 chidon: 10 shipping: 57) 5781: 7753765:7, 7765251:269',
    'Parent Registration (chayolei: 2 chidon: 10 yahadus: 40) 5781: 7749155:9, 7753919:54',
    'Parent Registration (chayolei: 220 chidon: 20 yahadus: 40) 5781: 7749406:40, 7751091:40, 7764597:11, 7771261:40, 7771262:40',
    'Parent Registration (chayolei: 55 chidon: 20 yahadus: 100) 5781: 7746593:615, 7750625:615',
    'Parent Registration (chayolei: 1 chidon: 10) 5781: 7753231:54',
    'Parent Registration (chayolei: 110 chidon: 20) 5781: 7752635:162, 7760187:162, 7772362:162',
    'Parent Registration (chayolei: 50 chidon: 34) 5781: 7749687:61, 7755961:162, 7758861:162, 7764280:4',
    'Parent Registration (chayolei: 55 chidon: 20 yahadus: 80) 5781: 7749410:40, 7751096:40',
    'Parent Registration (chayolei: 55 chidon: 30) 5781: 7749720:162, 7755982:162, 7760282:162',
    'Parent Registration (chayolei: 110 chidon: 30) 5781: 7749689:162, 7755965:162, 7764639:162, 7771721:4',
    'Parent Registration (chayolei: 55 chidon: 28 yahadus: 110) 5781: 7762483:61, 7762484:61',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7749219:7, 7755852:7',
    'Parent Registration (chayolei: 55 chidon: 28) 5781: 7748554:61, 7772335:61',
    'Parent Registration (chayolei: 110 chidon: 10) 5781: 7747300:84, 7761128:84, 7761165:84',
    'Parent Registration (chayolei: 55 chidon: 14) 5781: 7750723:61, 7774576:61',
    'Parent Registration (chayolei: 110 chidon: 20) 5781: 7747268:11, 7751992:11, 7764596:11',
    'Parent Registration (chayolei: 55 chidon: 10 yahadus: 40) 5781: 7759663:105, 7763803:105',
    'Parent Registration (chayolei: 55 chidon: 20 yahadus: 80) 5781: 7747321:4, 7760280:162',
    'Parent Registration (chayolei: 55 chidon: 10 yahadus: 40) 5781: 7756321:615, 7774676:615',
    'Parent Registration (chayolei: 210 chidon: 40 yahadus: 40) 5781: 7749707:162, 7755903:4, 7755953:162, 7755954:162, 7763956:4',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7750011:84, 7761150:84',
    'Parent Registration (chayolei: 1 chidon: 20) 5781: 7752336:81, 7760179:81, 7771332:81',
    'Parent Registration (chayolei: 150 chidon: 30) 5781: 7747322:4, 7752672:162, 7755926:4, 7763977:4',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7753854:255, 7753916:54',
    'Parent Registration (chayolei: 55 chidon: 20 yahadus: 40) 5781: 7749184:9, 7752521:9, 7764119:54',
    'Parent Registration (chayolei: 160 chidon: 20) 5781: 7752661:517, 7764666:162, 7772357:162, 7774469:4',
    'Parent Registration (chayolei: 110 chidon: 20 yahadus: 40) 5781: 7747788:49, 7751250:192, 7765378:192',
    'Parent Registration (chayolei: 110 chidon: 28) 5781: 7751062:61, 7760680:61, 7772318:61',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7748736:2, 7759470:2',
    'Parent Registration (chayolei: 105 chidon: 10 yahadus: 50) 5781: 7751079:2, 7759958:58, 7772965:58',
    'Parent Registration (chayolei: 100 chidon: 30) 5781: 7747314:255, 7747326:255, 7759931:7',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7744973:48, 7759600:48',
    'Parent Registration (chayolei: 110 chidon: 20 yahadus: 80) 5781: 7751981:40, 7755840:40, 7763725:40',
    'Parent Registration (chayolei: 165 chidon: 10 yahadus: 40) 5781: 7760010:615, 7762081:615, 7772381:615, 7774273:615',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7772166:48, 7772167:48',
    'Parent Registration (chayolei: 55 chidon: 20 yahadus: 40) 5781: 7764900:49, 7772389:192',
    'Parent Registration (chayolei: 105 chidon: 20) 5781: 7748570:58, 7756176:58, 7765213:2',
    'Parent Registration (chayolei: 110 chidon: 20 yahadus: 50) 5781: 7748573:58, 7751343:2, 7765181:2',
    'Parent Registration (chayolei: 110 chidon: 20) 5781: 7749825:11, 7760403:40, 7763727:40',
    'Parent Registration (chayolei: 165 chidon: 42) 5781: 7745307:61, 7752334:61, 7758912:61, 7774706:61',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7744968:48, 7756468:48',
    'Parent Registration (chayolei: 55 chidon: 30 yahadus: 40) 5781: 7751877:54, 7755583:255, 7759382:255',
    'Parent Registration (chayolei: 110 chidon: 38 yahadus: 150) 5781: 7750747:5, 7756801:61, 7756804:61',
    'Parent Registration (chayolei: 160 chidon: 30) 5781: 7746831:2, 7751337:2, 7759942:58, 7772192:2',
    'Parent Registration (chayolei: 165 chidon: 10 yahadus: 80) 5781: 7749844:192, 7751956:49, 7758103:192, 7771561:192',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7749844:192, 7751956:49',
    'Parent Registration (chayolei: 110 chidon: 30) 5781: 7744689:4, 7755962:162, 7764662:162',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7749626:105, 7756167:105',
    'Parent Registration (chayolei: 50 chidon: 20 yahadus: 80) 5781: 7749693:162, 7756084:4',
    'Parent Registration (chayolei: 155 chidon: 30 yahadus: 50) 5781: 7748584:58, 7756236:58, 7759968:58, 7772182:2',
    'Parent Registration (chayolei: 60 chidon: 20) 5781: 7752502:255, 7756837:54',
    'Parent Registration (chayolei: 55 chidon: 20 yahadus: 40 shipping: 57) 5781: 7751447:19, 7760810:19, 7773938:269',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7754338:54, 7755612:255',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7751191:192, 7751932:49, 7760240:192',
    'Parent Registration (chayolei: 15 chidon: 30) 5780: 7772003:180, 7772004:180, 7774760:180',
    'Parent Registration (chayolei: 55 chidon: 28) 5781: 7756978:61, 7756982:61',
    'Parent Registration (chayolei: 55 chidon: 14) 5781: 7756982:61, 7774643:61',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7748297:4, 7765876:162',
    'Parent Registration (chayolei: 110 chidon: 10) 5781: 7747945:192, 7759815:49, 7771546:49',
    'Parent Registration (chayolei: 55 chidon: 20) 5781: 7749233:7, 7755882:7',
    'Parent Registration (chayolei: 55 chidon: 20 yahadus: 40) 5781: 7751799:54, 7754665:9',
    'Parent Registration (chayolei: 100 chidon: 30) 5781: 7748720:2, 7756258:58, 7759954:58',
    'Parent Registration (chayolei: 55 chidon: 10 yahadus: 40) 5781: 7750939:255, 7751833:54',
    'Parent Registration (chayolei: 110 chidon: 20) 5781: 7749627:105, 7754256:105, 7770795:105',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7760722:265, 7770879:265',
    'Parent Registration (chayolei: 55 chidon: 10 yahadus: 40) 5781: 7751885:54, 7754209:54',
    'Parent Registration (chayolei: 110 chidon: 10 yahadus: 40) 5781: 7755904:4, 7774809:162, 7774827:162',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7754375:54, 7755676:255',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7754261:54, 7755595:255',
    'Parent Registration (chayolei: 105 chidon: 20 yahadus: 80) 5781: 7771718:4, 7774918:162, 7774919:162',
    'Parent Registration (chayolei: 165 chidon: 20) 5781: 7747550:3, 7751051:265, 7756302:265, 7765306:265',
    'Parent Registration (chayolei: 155 chidon: 30) 5781: 7748578:58, 7756245:58, 7758051:2, 7762022:58',
    'Parent Registration (chayolei: 105 chidon: 10) 5781: 7748719:2, 7759969:58, 7772197:2',
    'Parent Registration (chayolei: 55 chidon: 10 yahadus: 45) 5781: 7749398:40, 7770809:40',
    'Parent Registration (chayolei: 110 chidon: 20) 5781: 7751901:54, 7753225:54, 7759271:255',
    'Parent Registration (chayolei: 220 chidon: 30) 5781: 7770746:427, 7770747:427, 7770750:427, 7770751:427, 7774607:427',
    'Parent Registration (chayolei: 110 chidon: 38) 5781: 7756090:613, 7759979:61, 7759980:61',
    'Parent Registration (chayolei: 55 chidon: 14) 5781: 7764512:61, 7764570:49',
    'Parent Registration (chayolei: 55 chidon: 10) 5781: 7751993:11, 7763723:40'
];

$admins = [];
$children = [];
foreach ($info as $desc) {
//    echo $desc . "<br />";
    $pos = strpos($desc, '5781:');
    if ($pos === false) $pos = strpos($desc, '5780:');;
    $serials = substr($desc, ($pos + 6));
    $arrSerials = explode(',', $serials);
    foreach ($arrSerials as $serial) {
        $details = explode(':', $serial);
        $serial_num = $details[0];
        $school_id = $details[1];
        $stmt->execute([':serial' => $serial_num]);
        $row = $stmt->fetch();
        if ( !isset( $admins[$row['admin_id']] ) ) {
            $admins[$row['admin_id']] = [
                'name' => $row['first'] . ' ' . $row['last'],
                'email' => $row['admin_email'],
                'phone' => $row['admin_phone_mobile'],
                'phone2' => $row['admin_phone_mobile2']
            ];
        }
        $children[$row['admin_id']][] = [
            'name'  => $row['uFirst'] . ' ' . $row['uLast'],
            'school'=> $schools[$school_id]
        ];
    }
}
echo "<pre>";
//print_r( $admins );
//print_r( $children );
echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <h1>Parents with Registration Issues</h1>
        <table>
            <tr>
                <th>Parent</th>
                <th>Email</th>
                <th>Phone Numbers</th>
                <th>Students Registered</th>
            </tr>
            <?php
            foreach ( $admins as $admin_id => $admin ) {
                echo "<tr><td>" . $admin['name'] . "</td><td>" . $admin['email'] . "</td><td>" . $admin['phone'] . "<br />" .
                    $admin['phone2'] . "</td><td>";
                foreach ( $children[$admin_id] as $child ) echo $child['name'] . ' - ' . $child['school'] . "<br />";
                echo "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>