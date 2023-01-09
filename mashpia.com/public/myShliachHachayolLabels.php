0<?php
$admin_auth = array('school'); 
require('header.php');

require 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

function checkChidon($id) {
    global $year;
    $sql = "select * from th_chidon where year = $year and user_id in (
            select id from admin_auths where admin_id = $id and auth = 'user')";
    $result = mysql_query($sql);
    return mysql_num_rows($result);
}

function createCsv(array $info) {
    $addresses = [];
    $addresses[] = ['Name', 'Address', 'Number of Hachayols'];
    foreach ($info as $details) {
        foreach ($details as $parent) {
            $name = $parent['last'];
            $address = $parent['admin_address1'] . " " . $parent['admin_city'] . ', ' . $parent['admin_state'] .
                " " . $parent['admin_postal'] . " " . (empty($parent['admin_country']) ? 'USA' : $parent['admin_country']);
            $num = $parent['num_hachayols'];
            $addresses[] = [$name, $address, $num];
        }
    }

    $file_url = "myshliach.csv";
    $fp = fopen($file_url, 'w');
    foreach ($addresses as $address) {
        fputcsv($fp, $address);
    }
    fclose($fp);

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"$file_url\"");
    readfile($file_url);
}

require 'class.myShliachHachayol.php';
$m = new MyShliachHachayol( true );

//sort for shipping
$m->sortByAddress();
$parents = $m->getSortedAdmins();
$children = $m->getChildren();
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<!--
		<style type="text/css">
            .label {
                width: 2.3in;
                height: .7in;
                font-size: 10px;
                padding: 5px;
                float: left;
                margin-left: .3in;
                margin-top: .3in;
            }
            .space {
                width: .25in;
                height: .7in;
                float: left;
                padding: 3px;
            }
            .page-break {
                clear: both;
                page-break-after: always;
            }
            .medal {
                width: 1in;
                float: left;
            }
            .name {
                width: 2in;
            }
            .topSpace {
                height: 0.2in;
                width: 7in;
            }
            .instructions {
                width: 50%;
            }
            @media screen { 
                #report_div {
                    display: none;
                }
                .no-print {
                    display: block;
                }
            }
            @media print {
                #report_div {
                    display: block;
                } 
                .no-print {
                    display: none;
                }
            }
        </style>
        -->
		<style type="text/css">
            .label {
                width: 2.15in;
                height: 1in;
                font-size: 12px;
                padding: 5px;
                float: left;
            }
            .space {
                width: .35in;
                height: 1in;
                float: left;
                padding: 5px 20px;
            }
            .page-break {
                clear: both;
                page-break-after: always;
            }
            .medal {
                width: 1in;
                float: left;
            }
            .name {
                width: 2.15in;
                font-size: 14px;
            }
            .topSpace {
                height: 0.2in;
                width: 7in;
            }
            .instructions {
                width: 50%;
            }
            @media screen { 
                #report_div {
                    display: none;
                }
                .no-print {
                    display: block;
                }
            }
            @media print {
                #report_div {
                    display: block;
                } 
                .no-print {
                    display: none;
                }
            }
        </style>
        <script type="text/javascript">
            function check() {
                if ( confirm( "Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again." ) ) 
                    window.print();
            }
        </script>
	</head>

	<body>
	<? include('admin_header.php'); ?>
	<div class="no-print">
        <h1>Hachayol Report</h1>        
            <div class='instructions'>
                <b>Printing Instructions</b><br />
                Set Scale to 90%<br />
                Set your printer margins to the following:<br />
                0.5 Top<br />
                0.3 Left<br />
                0.0 Right and Bottom<br /><br />   
                <div align='center'>
                    <input type='button' name='print' value='Print' onclick="check()" />    
                </div>
            </div>
        </div>
        
        <div id="report_div" name="report_div">
            <div class='topSpace'></div>
            <?
            $cols = 1; //counter for columns
            $rows = 1; //counter for rows
            
			foreach ($parents as $ord => $info) {
				foreach ($info as $admin_id => $parent) {
					$name = $parent['last'];
					$address = $parent['admin_address1'] . "<br />" . $parent['admin_city'] . ', ' . $parent['admin_state'] . 
						" " . $parent['admin_postal'] . "<br />" . (empty($parent['admin_country']) ? 'USA' : $parent['admin_country']);

					echo "<div class='label'>";
					echo "<span class='name'>";
					echo "<b>";
//          if (checkChidon($admin_id)) echo "*";
					echo $name . " Family </b><br />" . $address . "</span></div>";
					checkForBreak();
					/*
					echo "<div class='label'>";
					foreach ($children[$admin_id] as $child) {
						echo "<span class='medal'>" . $child . "</span>";
					}
					echo "</div>";
					checkForBreak();
					 * 
					 */
				 }
			}

			function checkForBreak() {
				global $cols, $rows;
				if (($cols % 3) != 0) {
                	echo "<div class='space'></div>";
                } else {
                    $cols = 0; //reset cols so that it will show new row
                    $rows++; //add row
                    if ( ($rows % 11) == 0 ) {
                        $rows = 1; //reset rows counter and add space to top of new page
                        echo "<div class='page-break'></div><div class='topSpace'></div>"; 
                    }
                }
                $cols++;
			}
			?>
        </div>
	</body>
    <script>
        function generateCsv( headers, rows, filename ) {
            // generate the csv content
            const universalBOM = "\uFEFF";
            let csvContent = `${ headers.join(',') }\n`;
            // Add each row to the CSV content and encode it for unicode in excel
            rows.forEach( row => { csvContent += `${row.join(',')}\n` } );
            csvContent = encodeURIComponent( universalBOM + csvContent );
            // create and click the download link
            let link = document.createElement('a');
            link.href = `data:text/csv;charset=utf-8,${csvContent}`;
            // link.target = '_blank';
            link.download = `${filename}.csv`;
            link.click();
        }

        let headers = []
        headers.push(['Name', 'Address', 'Number of Registered Children'])
        const info = <?= json_encode($parents) ?>;
        const children = <?= json_encode($children) ?>;
        let rows = []
        for (let p in info) {
            for (let id in info[p]) {
                const parent = info[p][id]
                const name = parent['last']
                const address = parent['admin_address1'] + ' ' + parent['admin_city'] + ' ' + parent['admin_state'] +
                    ' ' + parent['admin_postal'] + ' ' + (parent['admin_country'].length ? parent['admin_country'] : 'USA');
                const num_children = children[parent['admin_id']].length
                rows.push([name, address, num_children])
            }
        }
        generateCsv(headers, rows, 'myshliach')
    </script>
</html>