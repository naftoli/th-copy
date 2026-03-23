<?php
$admin_auth = array('school');
require('header.php');

require_once 'class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

require_once 'class.rankReport.php';
$rr = new RankReport();

if (isset($_POST['date_selection'])) {
    $dates = explode(':', $_POST['date_selection']);
    $start = $dates[0];
    $end = $dates[1];
    $rr->overrideDates($start, $end);
}

$rr->setRankNames();
$rankNames = $rr->getRankNames();
$reportDates = $rr->getReportDates();
$heDatesRanks = $rr->getHeReportDates();
$shipped = $rr->getRankBooksShipped();
$super = $admin_user['auth'] == 'super';

require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], false);
$schools = $as->getSchools();

$for_shipping = [];
?>
<!DOCTYPE html>
<HTML>
<HEAD>
    <TITLE>Book Report</TITLE>
    <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
      @media screen {
        .no-print {
          display: block;
        }

        .print-only {
          display: none;
        }
      }

      @media print {
        .no-print {
          display: none;
        }

        .print-only {
          display: block;
        }
      }

      tr, th, td {
        font-family: "Arial", sans-serif;
        padding: 10px;
        font-size: 14px;
        border-bottom: 1px #f0f0f0 solid;
      }

      .page-break {
        page-break-after: always;
      }

      #main {
        font-size: 14px;
      }

      select, button, input[type="button"], input[type="submit"] {
        padding: 5px 10px;
        font-size: 16px;
        cursor: pointer;
      }
    </style>
</HEAD>

<BODY>
<?php include('admin_header.php'); ?>
<div class='no-print'>
    <h1>Book Report</h1>

    <div>
        Current Report is calculated from <?= $heDatesRanks['start_he'] ?> up to <?= $heDatesRanks['end_he'] ?>.<br/>
        <form action="" method="post">
            <p>
                <?php
                echo $rr->getHtmlSelect(3);
                ?>
                <input type="submit" name="submit" value="Modify Report"/>
            </p>
        </form>
    </div>

    <div align='center'>
        <input type='button' name='print' value='Print' onclick="window.print()"/>
    </div>

    <?php if ($super) : ?>
    <div>
        <button id='booksBtnAll'>Set All Books as Shipped</button>
    </div>
    <?php endif; ?>
</div>
<div id='main'>
    <?php
    $bookTotals = [];
    foreach ($schools as $school_id => $school_name) {
        if (in_array($school_id, [180, 585, 588, 612, 709])) continue;
        $rr->setSchoolId($school_id);
        $books = $rr->getBooksToSend();
        $userInfo = $rr->getUserInfo();
        $heNames = $rr->getUserHeNames();

        foreach ($books as $school => $more) {
//            if ($school != $school_name) continue;
            echo "<h2>" . $school_name . "</h2>";
            echo "Books earned in " . $school . " from " . $heDatesRanks['start_he'] . " until " . $heDatesRanks['end_he'] . ". <br /><br />";
            $totals = [];

            foreach ($more as $book => $other) {
                $totals[$book] = 0;
                echo "<h2>Book #" . $book . "</h2>";
                echo "<table><tr><th></th><th>Grade</th><th>Serial Number</th><th>Name</th></tr>";
                foreach ($other as $teacher => $more) {
                    foreach ($more as $grade => $users) {
                        foreach ($users as $user_id) {
                            $info = $userInfo[$user_id];
                            $heName = $heNames[$user_id];
                            $addToTotal = true;

                            echo "<tr><td>";
                            echo "<input type='checkbox' class='book' id='book_" . $book . "_" . $user_id . "' ";
                            if (isset($shipped[$user_id]) && in_array($book, $shipped[$user_id])) {
                                $addToTotal = false;
                                echo 'checked ';
                            }
                            if (!$super) echo 'disabled ';
                            echo "/>";
                            echo "</td><td>". $grade . "</td><td>" . $info['user_serial'] . "</td><td>";
                            echo $heName . ' - ' . $info['first'] . ' ' . $info['last'] . "</td></tr>";

                            if ($addToTotal) {
                                $totals[$book]++;
                                $for_shipping[$user_id] = $book;

                                // grand totals
                                if (isset($bookTotals[$book])) {
                                    $bookTotals[$book]++;
                                } else {
                                    $bookTotals[$book] = 1;
                                }
                            }
                        }
                    }
                }
                echo "</table>";
            }

            // show totals
            if ($super) {
                ?>
                <h2><?= $school ?> Totals</h2>
                <table>
                    <tr>
                        <th>Book</th>
                        <th>Total</th>
                    </tr>
                    <?
                    $gtotal = 0;
                    foreach ($totals as $book => $total) {
                        $gtotal += $total;
                        echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
                    }
                    echo "<tr><th>Grand Total:</th><th>" . $gtotal . "</th></tr>";
                    ?>
                </table>
                <?
            }
            echo "<br /><br />";
            echo "<div class='page-break'></div>";
        }
    }
    ?>
    <h2><?= $super ? 'Grand ' : '' ?>Totals</h2>
    <table>
        <tr>
            <th>Book</th>
            <th>Total</th>
        </tr>
        <?php
        $grandTotal = 0;
        foreach ($bookTotals as $book => $total) {
            $grandTotal += $total;
            echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
        }
        echo "<tr><th>Grand Total:</th><th><span id='grandTotal'>" . $grandTotal . "</span></th></tr>";
        ?>
    </table>
</div>
</BODY>
<script>
  $(function () {
    $(document).ready(() => {
      $('#booksBtnAll').click(() => {
        setAsShipped()
      })
    })

    const setAsShipped = async () => {
      // use fetch
      const res = await fetch('/rank_books/set_as_shipped.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({total: <?= $grandTotal ?>, info: <?= json_encode($for_shipping); ?>})
      })
      const data = await res.json()
      if (data.success) {
        alert(data.books_count + ' books set as shipped.')
      } else {
        alert(data.error)
      }
    }

    const books = document.querySelectorAll('.book')
    books.forEach(book => {
      book.addEventListener('change', async (e) => {
        const [book, user] = e.target.id.split('_').slice(1)
        const checked = e.target.checked
        const res = await fetch('/rank_books/update_shipped.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({user, book, checked})
        })
        const data = await res.json()
        if (!data.success) {
          alert('Failed to update shipped status.')
          document.getElementById(e.target.id).checked = !checked
        }
      })
    })
  })
</script>
</HTML>
