<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
$australian = GlobalSettings::getAustralian();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

function checkForBreak()
{
    global $i, $rows;
    if (($i % 3) != 0) {
        echo "<div class='space'></div>";
    } else {
        $i = 0; //reset i so that it will show new row
        $rows++; //add row
        if (($rows % 11) == 0) {
            $rows = 1; //reset rows counter and add space to top of new page
            echo "<div class='page-break'></div><div class='topSpace'></div>";
        }
    }
    $i++;
}

function get_ranks()
{
    $ranks = [];
    $sql = "select * from ranks";
    $result = mysql_query($sql);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }
    while ($row = mysql_fetch_assoc($result)) {
        $ranks[$row['rank_ord']] = $row['rank_name'];
    }
    return $ranks;
}

$exceptions = array_unique( array_merge([585, 808, 612], $australian) );
$sql = "SELECT 
            s.school_name,
            c.class_id,
            c.class_grade,
            c.class_sub,
            u.user_id, 
            u.first,
            u.last, 
            u.user_serial,
            MAX(rm.rank_ord) AS rank_ord
        FROM
            users u
                JOIN
            schools s USING (school_id)
                JOIN
            rank_marks rm USING (user_id)
                JOIN
            classes c ON u.class_id = c.class_id 
                JOIN 
            user_registration ur ON u.user_id = ur.user_id 
                LEFT JOIN
            rank_medals_shipped rms ON u.user_id = rms.user_id 
        WHERE
            u.user_registered > 0 AND ur.year = $year 
                AND u.school_id NOT IN (" . implode(',', $exceptions) . ") 
                AND u.school_id IN (" . implode(',', array_keys($schools)) . ") 
                AND rms.user_id IS NULL
        GROUP BY u.user_id 
        HAVING rank_ord > 1  
        ORDER BY school_name , u.last , u.first";
$result = mysql_query($sql);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

$info = [];
$user_info = [];
while ($row = mysql_fetch_assoc($result)) {
    $school = $row['school_name'];
    $user_id = $row['user_id'];
    $user_info[$user_id] = $row;
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $info[$school][$grade][$user_id] = $row['rank_ord'];
}

//echo "<pre>"; print_r($info); echo "</pre>"; exit;
$for_shipping = [];
$actual_ranks = []; // array to hold highest rank per child
$ranks = get_ranks();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
    <TITLE>Rank Books Catchup Report</TITLE>
    <LINK href="../admin_styles.css" rel="stylesheet" type="text/css">
    <SCRIPT type="text/javascript" src="../icalendar.js"></SCRIPT>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
      .instructions {
        width: 50%;
      }

      @media screen {
        .no-print {
          display: block;
        }
      }

      @media print {
        .no-print {
          display: none;
        }
      }

      button {
        padding: 10px;
        font-size: 14px;
      }

      tr, th, td {
        font-family: "Arial", sans-serif;
        padding: 10px;
        font-size: 14px;
        border-bottom: 1px #f0f0f0 solid;
      }

      .page-break {
        clear: both;
        page-break-after: always;
      }
    </style>
</HEAD>

<BODY>
<?php include('../admin_header.php'); ?>

<h1 class="no-print">Rank Books Catchup Report</h1>
<div>
    <div class="no-print">
      <button onclick="window.print()">Print</button>
      <?php if ($admin_user['auth'] == 'super') { ?>
          <button id="medalsBtnAll">Set All as Shipped</button>
      <?php } ?>
    </div>
    <?php
    $totalBooks = [];
    $grandTotal = [];
    // first show summaries
    foreach ($info as $school => $grades) {
        foreach ($grades as $grade => $users) {
            foreach ($users as $user_id => $rank_ord) {
                if ($rank_ord > 11) {
                    $book = 3;
                } else if ($rank_ord > 8) {
                    $book = 2;
                } else {
                    $book = 1;
                }

                // totals
                if (isset($totalBooks[$book])) {
                    $totalBooks[$book]++;
                } else {
                    $totalBooks[$book] = 1;
                }
                if (isset($grandTotal[$book])) {
                    $grandTotal[$book]++;
                } else {
                    $grandTotal[$book] = 1;
                }
            }
        }
        echo "<h2>Total Books for " . $school . "</h2>";
        echo "<table><tr><th>Book</th><th>Total</th></tr>";
        foreach ($totalBooks as $book => $total) {
            echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
        }
        echo "</table><br /><div class='page-break'></div>";
    }
    echo "<h2>Grand Totals</h2>";
    echo "<table><tr><th>Book</th><th>Total</th></tr>";
    foreach ($grandTotal as $book => $total) {
        echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
    }
    echo "</table><br /><div class='page-break'></div>";

    // then show details
    foreach ($info as $school => $grades) {
        echo "<h2>" . $school . "</h2>";
        echo "<table><tr><th>User ID</th><th>Serial Number</th><th>Last Name</th><th>First Name</th><th>Grade</th>
            <th>Rank</th><th>Book #</th></tr>";
        foreach ($grades as $grade => $users) {
            foreach ($users as $user_id => $rank_ord) {
                if ($rank_ord > 11) {
                    $book = 3;
                } else if ($rank_ord > 8) {
                    $book = 2;
                } else {
                    $book = 1;
                }
                echo "<tr><td>" . $user_id . "</td><td>" . $user_info[$user_id]['user_serial'] . "</td><td>" .
                    $user_info[$user_id]['last'] . "</td><td>" . $user_info[$user_id]['first'] . "</td><td>" .
                    $grade . "</td><td>" . $ranks[$rank_ord] . "</td><td>" . $book . "</td></tr>";

                $for_shipping[$user_id] = $book; // add to array for shipping
            }
        }
        echo "</table><br/><div class='page-break'></div>";
    }
    ?>
</div>
<script>
  $(document).ready(() => {
    $('#medalsBtnAll').click(() => {
      setAsShipped()
    })
  })

  const setAsShipped = async () => {
    // use fetch
    const res = await fetch('set_as_shipped.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({total: <?= $totalBooks ?>, info: <?= json_encode($for_shipping); ?>})
    })
    const data = await res.json()
    if (data.success) {
      alert(data.books_count + ' books set as shipped.')
    } else {
      alert(data.error)
    }
  }
</script>
</BODY>
</HTML>
