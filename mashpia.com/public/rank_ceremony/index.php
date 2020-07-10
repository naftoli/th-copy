<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';  
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

function createFile($info, $name) {
    $fp = fopen($name, "w");
    foreach ($info as $fields) {
        fputcsv($fp, $fields, "\t");
    }
    fclose($fp);
}

function createZip($files, $images, $filename) {
    $zip = new ZipArchive();
    if ($zip->open($filename, ZipArchive::CREATE) !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file) {
        $zip->addFile($file);
        unlink($file);
    }
    foreach ($images as $img) {
        $zip->addFile($img);
        // unlink($img);
    }
    $zip->close();
}

$files = [];
$images = [];
$r = new RankReport();
foreach ($schools as $id => $school) {
    $r->setSchoolId($id);
    $r->setRanks('byRank');
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $logos = $r->getSchoolLogos();
    
    $i = 0;
    $info[$i++] = ['comp','comp_name','chayol_name','school_name','school_logo'];
    foreach ($ranks as $school => $other) {
        foreach ($other as $rank => $more) {
            $j = 1;
            foreach ($more as $teacher => $other) {
                foreach ($other as $grade => $more) {
                    foreach ($more as $user_id) {
                        // create pic of child to add to zipArchive
                        $new_img = imagecreatefromstring(file_get_contents('http://mashpia.com' . $pics[$user_id]));
                        $new_image = imagepng($new_img, $user_id . '.png');
                        if ($new_image) $images[] = $user_id . '.png';

                        $info[$i]['comp'] = $rank;
                        $info[$i]['comp_name'] = $rank . '_' . $j++; 
                        $info[$i]['chayol_name'] = $users[$user_id];
                        $info[$i]['chayol_picture'] = $new_image ? $user_id . '.png' : '';
                        $info[$i]['school_name'] = $school;
                        $info[$i]['school_logo'] = $logos[$school]['logo_id'];
                        $i++;
                    }
                }
            }
        }
    }
    if (count($ranks)) {
        $file_name = "TSV_Report_" . $id . ".csv";
        createFile($info, $file_name);
        $files[] = $file_name;
        break;
    }
} 
$filename = "tsv.zip";
createZip($files, $images, $filename);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filename));
flush(); // Flush system output buffer
readfile($filename);
unlink($filename);
exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family: Arial, Helvetica, sans-serif;
                padding: 5px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <?php
        $info = [];
        foreach ($schools as $school_id => $school_name) {
            if (count($ranks[$school_id]) > 0) 
                echo "<table><tr><th>comp</th><th>comp_name</th><th>chayol_name</th><th>chayol_picture</th><th>school_name</th><th>school_logo</th></tr>";
            foreach ($ranks[$school_id] as $school => $other) {
                $k = 0; // index into info array
                foreach ($other as $rank => $more) {
                    $i = 1; // number the users within rank
                    $j = 1; // same as above
                    foreach ($more as $teacher => $other) {
                        foreach ($other as $grade => $more) {
                            foreach ($more as $user_id) {
                                echo "<tr><td>" . $rank . "</td><td>" . ($rank . '_' . $i++) . "</td><td>" . $users[$school_id][$user_id] . "</td><td>" . 
                                    $pics[$school_id][$user_id] . "</td><td>" . $school . "</td><td>" . $logos[$school_id][$school]['logo_id'] . "</td></tr>";
                                $info[$school_id][$k]['comp'] = $rank;
                                $info[$school_id][$k]['comp_name'] = $rank . '_' . $j++; 
                                $info[$school_id][$k]['chayol_name'] = $users[$school_id][$user_id];
                                $info[$school_id][$k]['chayol_picture'] = $pics[$school_id][$user_id];
                                $info[$school_id][$k]['school_name'] = $school;
                                $info[$school_id][$k]['school_logo'] = $logos[$school_id][$school]['logo_id'];
                                $k++;
                            }
                        }
                    }
                }
            }
            echo "</table><br />";
        }
        ?>
    </body>
    <script>
        function dataToTSV( headers, rows, filename ) {
            const universalBOM = "\uFEFF";
            let csvContent = `${ headers.join("\t") }\n`;
            // Add each row to the TSV content and encode it for unicode in excel
            rows.forEach( row => { csvContent += `${row.join("\t")}\n` } );
            csvContent = encodeURIComponent( universalBOM + csvContent );
            // create and click the download link
            let link = document.createElement('a');
            link.href = `data:text/csv;charset=utf-8,${csvContent}`;
            // link.target = '_blank';
            link.download = `${filename}.csv`;
            link.click();
        }

        window.onload = function() {
            // set the headers
            const headers = ['comp','comp_name','chayol_name','chayol_picture','school_name','school_logo']

            // get the data
            const info = <?= json_encode($info); ?>;

            // create seperate file for each school
            for (school in info) {
                let rows = []
                for (i of info[school]) {
                    let row = []
                    for (h of headers) {
                        row.push(i[h])
                    }
                    rows.push(row)
                }
                const filename = "TSV_Report_" + school;
                dataToTSV( headers, rows, filename );
            }
        }
    </script>
</html>