<?php
$admin_auth = array('school');
require('header.php');

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page.');
}

function checkForBreak()
{
    global $cols, $rows;
    if (($cols % 3) != 0) {
        echo "<div class='space'></div>";
    } else {
        $cols = 0; //reset cols so that it will show new row
        $rows++; //add row
        if (($rows % 11) == 0) {
            $rows = 1; //reset rows counter and add space to top of new page
            echo "<div class='page-break'></div><div class='topSpace'></div>";
        }
    }
    $cols++;
}

function getRankNames()
{
    $ranks = [];
    $sql = "SELECT * FROM ranks";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $ranks[$row['rank_ord']] = $row['rank_name'];
    }
    return $ranks;
}

require 'class.myShliachShipLabels.php';
if (isset($_GET['school'])) {
    $school_id = $_GET['school'];
    $myshliach = new MyShliachShipLabels($school_id);
} else {
    $myshliach = new MyShliachShipLabels();
}

// deal with dates
if (isset($_POST['date_selection'])) {
    $dates = explode(':', $_POST['date_selection']);
    $myshliach->overrideDates($dates[0], $dates[1]);
}

$myshliach->setInfo(); // set all the info for the report
$reportDates = $myshliach->getReportDates();
$heDatesRanks = $myshliach->getHeReportDates();
$rankNames = getRankNames();
$parents = $myshliach->getParents();
$medals = $myshliach->getMedals();
$ranks = $myshliach->getRanks();
$admins = $myshliach->getAdmins();
$userInfo = $myshliach->getUserInfo();
$heDates = $myshliach->getHeReportDates();
$books_shipped = $myshliach->getRankBooksShipped();
$medals_shipped = $myshliach->getRankMedalsShipped();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"/>
  <link href="admin_styles.css" rel="stylesheet" type="text/css">
  <style>
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
      font-size: 9px;
    }

    .name {
      width: 2.15in;
      font-size: 14px;
    }

    .topSpace {
      height: 0.5in;
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

    select, button, input[type="button"], input[type="submit"] {
      padding: 5px 10px;
      font-size: 16px;
      cursor: pointer;
    }
  </style>
  <script type="text/javascript">
    function check() {
      if (confirm("Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again."))
        window.print();
    }
  </script>
</head>

<body>
<? include('admin_header.php'); ?>
<div class="no-print">
  <h1>Medals Labels Report</h1>
  <div>
    Current Report is calculated from <?= $heDatesRanks['start_he'] ?> up to <?= $heDatesRanks['end_he'] ?>.<br/>
    <form action="" method="post">
      <p>
          <?= $myshliach->getHtmlSelect(4); ?>
        <input type="submit" name="submit" value="Modify Report"/>
      </p>
    </form>
    <button id="csvDownload" disabled>Download CSV</button>
    <br /><br />
  </div>
  <div class='instructions'>
    <b>Printing Instructions</b><br/>
    Please set your printer margins to the following:<br/>
    0.5 Top<br/>
    0.3 Left<br/>
    0.0 Right and Bottom<br/><br/>
    <div align='center'>
      <input type='button' name='print' value='Print' onclick="check()"/>
    </div>
  </div>
</div>

<div id="report_div" name="report_div">
  <div class='topSpace'></div>
    <?php
    $cols = 1; // counter for columns
    $rows = 1; // counter for rows
    $csv = []; // variable for csv data

    foreach ($parents as $admin => $children) {
        $parent = $admins[$admin];
        $name = $parent['first'] . ' ' . $parent['last'];
        $address = $parent['admin_address1'] . "<br />" . (empty($admin['admin_address2']) ? '' :
                $admin['admin_address2'] . "<br />") . $parent['admin_city'] . ', ' . $parent['admin_state'] .
            " " . $parent['admin_postal'] . "<br />" . $parent['admin_country'];

        echo "<div class='label'>";
        echo "<span class='name'>";
        echo "<b>" . $name . " (" . $admin . ")</b><br />" . $address . "</span></div>";
        checkForBreak();

        $totalCampaignMedals = 0;
        $totalRankBooks = 0;
        $totalRankMedalsSmall = 0;
        $totalRankMedalsBig = 0;

        foreach ($children as $child) {
            if (isset($medals[$child]) || isset($ranks[$child])) {

                if (isset($medals[$child])) {
                    $numMedals = 1;
                    echo "<div class='label'>";
                    echo "<span class='name'>";
                    echo $userInfo[$child] . "</span><br />";
                    foreach ($medals[$child] as $medal) {
                        if ($numMedals > 8) {
                            echo "</div>";
                            checkForBreak();
                            echo "<div class='label'>";
                            echo "<span class='name'>";
                            echo $userInfo[$child] . "</span><br />";
                            $numMedals = 1;
                        }
                        if ($medal['subject_name'] == 'שבת מברכים תהילים') $medal['subject_name'] = 'תהילים';
                        echo "<span class='medal'>" . $medal['subject_name'] . "-" . $medal['medal_name'] . "</span>";
                        $numMedals++;
                    }
                    echo "</div>";
                    checkForBreak();
                    $totalCampaignMedals += count($medals[$child]);
                }

                if (isset($ranks[$child])) {
                    // add medal ranks and books that were not yet shipped
                    // get highest rank
                    $rank = $ranks[$child][count($ranks[$child]) - 1];
                    $rank_ord = $rank['rank_ord'];

                    // get book number
                    if ($rank_ord > 11) {
                        $book = 3;
                    } else if ($rank_ord > 8) {
                        $book = 2;
                    } else {
                        $book = 1;
                    }

                    // check if book was already sent
                    if (isset($books_shipped[$child]) && in_array($book, $books_shipped[$child])) {
                        continue;
                    }

                    echo "<div class='label'>";
                    echo "<span class='name'>";
                    echo "Name : " . $userInfo[$child] . "<br />";
                    echo "Rank : " . $rankNames[$rank_ord] . "<br />Rank Book #: " . $book;
                    echo "</span></div>";
                    checkForBreak();
                    $totalRankBooks++;

                    // add rank medals that were not yet shipped
                    // rank medals start from 2
                    $ship = false;
                    for ($idx = 2; $idx <= $rank_ord; $idx++) {
                        if (!isset($medals_shipped[$child]) || (isset($medals_shipped[$child]) && !in_array($idx, $medals_shipped[$child]))) {
                            $ship = true;
                            break;
                        }
                    }

                    if ($ship) {
                        echo "<div class='label'>";
                        echo "<span class='name'>";
                        echo "Name : " . $userInfo[$child] . "<br />";
                        echo "Rank Medals:<br />";
                        $j = 1; // flag for when to make new label
                        for ($idx = 2; $idx <= $rank_ord; $idx++) {
                            if ($j > 10) {
                                echo "</span></div>";
                                checkForBreak();
                                echo "<div class='label'>";
                                echo "<span class='name'>";
                                echo "Name : " . $userInfo[$child] . "<br />";
                                echo "Rank Medals:<br />";
                                $j = 1;
                            }
                            // check if rank was shipped
                            if (isset($medals_shipped[$child]) && in_array($idx, $medals_shipped[$child])) {
                                continue;
                            }
                            echo "<span class='medal'>" . $rankNames[$idx] . "</span>";
                            $j++;
                            if ($idx < 9) {
                                $totalRankMedalsSmall++;
                            } else {
                                $totalRankMedalsBig++;
                            }
                        }
                        echo "</span></div>";
                        checkForBreak();
                    }
                }
            }
        }
        $csv[] = [
            'family_id' => $admin,
            'total_campaign_medals' => $totalCampaignMedals,
            'total_rank_books' => $totalRankBooks,
            'total_rank_medals_small' => $totalRankMedalsSmall,
            'total_rank_medals_large' => $totalRankMedalsBig,
        ];
    }
    ?>
</div>
</body>
<script>
    function downloadCSV(csvData) {
        const csvContent = "data:text/csvcharset=utf-8,";
        const headers = ["family_id", "total_campaign_medals", "total_rank_books", "total_rank_medals_small", "total_rank_medals_big"];
        const rows = csvData.map(row => [row.family_id, row.total_campaign_medals, row.total_rank_books, row.total_rank_medals_small, row.total_rank_medals_large].join(","));
        const csv = csvContent + headers.join(",") + "\n" + rows.join("\n");
        const encodedUri = encodeURI(csv);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "medals_ranks_report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // after page is loaded
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('csvDownload').disabled = false;
    })

    // when button is clicked
    document.getElementById('csvDownload').addEventListener('click', function() {
        downloadCSV(<?= json_encode($csv) ?>)
    })
</script>
</html>