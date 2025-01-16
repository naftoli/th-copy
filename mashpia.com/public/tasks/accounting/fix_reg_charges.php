<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$user_by_serial = [];
$school_by_serial = [];
$school_by_user_id = [];
$stmtUserInfo = $MASHPIA_DB->query("
    SELECT user_id, user_serial, school_id from users
");
$rowsUsers = $stmtUserInfo->fetchAll(PDO::FETCH_ASSOC);
foreach ($rowsUsers as $row) {
    $user_by_serial[$row['user_serial']] = $row['user_id'];
    $school_by_user_id[$row['user_id']] = $row['school_id'];
    $school_by_serial[$row['user_serial']] = $row['school_id'];
}

$stmt = $MASHPIA_DB->query("
    SELECT 
        *
    FROM
        transactions
    WHERE
        trans_id > 51840 
    ORDER BY trans_id DESC 
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$fields = ['trans_id', 'school_id', 'trans_date', 'description', 'amount', 'admin_id', 'users_registered'];
$qrys = [];

$stmtInsert = $MASHPIA_DB->prepare("
    INSERT INTO registration_charges 
    SET trans_id = :trans_id, 
        user_id = :user_id, 
        school_id = :school_id, 
        admin_id = :admin_id, 
        type = :type, 
        amount = :amount, 
        date = :date, 
        year = :year, 
        discount = :discount
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Transactions</title>
</head>
<style>
  body, tr, th, td {
    font-family: Arial;
    font-size: 14px;
  }

  tr, th, td {
    padding: 6px;
  }
</style>
<body>
<h1>Transactions</h1>
<table style="width:95%; margin: auto;">
  <tr>
      <?php
      foreach ($fields as $field) {
          echo "<th>$field</th>";
      }
      ?>
  </tr>
    <?php
    $fixes = [
        52070 => [
            'action'  => 'remove',
            'code'    => 'RRSUSA',
            'amount'  => 20
        ],
        52633 => [
            'action'  => 'remove',
            'code'    => 'HACH',
            'amount'  => 20,
        ],
        56182 => [
            'action'  => 'add',
            'code'    => 'THMSUSA',
            'amount'  => 35,
        ],
        55285 => [
            'action'  => 'add',
            'code'    => 'RRHVN',
            'amount'  => 75,
        ],
        54002 => [
            'action'  => 'add',
            'code'    => 'THAKUSA',
            'amount'  => 67,
        ],
        52491 => [
            'action'  => 'add',
            'code'    => 'THAKUSA',
            'amount'  => 67,
        ]
    ];
    $all_info = [];
    $errors_found = 0;
    foreach ($rows as $row) {
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<td>" . $row[$field] . "</td>";
        }
        echo "</tr>";

        // create qry for reg_charges table
        $trans_id = $row['trans_id'];
        $school_id = $row['school_id'];
        $admin_id = $row['admin_id'];
        $date = $row['trans_date'];
        $amount = intval($row['amount']);
        // loop through details if the first letter of first element has a 'C' or 'F' or starts with 'Soldier'
        if (preg_match('/^(C|F|Soldier)/', $row['description'])) {
            if (preg_match('/^Soldier/', $row['description'])) {
                $users = explode(',', $row['users_registered']);
                $user_amount = $row['amount'] / count($users);
                $code = 'THE-' . $user_amount;
                foreach ($users as $user) {
                    $all_info[$trans_id][] = [
                        'type' => 'U',
                        'school_id' => $school_id,
                        'admin_id' => 0,
                        'date' => $date,
                        'user_id' => $user,
                        'codes' => [$code]
                    ];
                }
            } else {
                $total = 0;
                $info = explode(',', $row['description']);
                foreach ($info as $items) {
                    $details = explode(':', $items);
                    $first_letter = substr($details[0], 0, 1);
                    $id = substr($details[0], 1);
                    if (!is_numeric($id)) continue;
                    $codes = [];
                    foreach ($details as $idx => $detail) {
                        if ($idx > 0) {
                            if (strpos($detail, '-') !== false) {
                                $codes[] = $detail;
                                // figure out total
                                $code_info = explode('-', $detail);
                                if ($code_info[0] != 'V') {
                                    if (strpos($detail, '--') !== false) {
                                        $total -= intval($code_info[2]); // need to subtract
                                    } else {
                                        $total += intval($code_info[1]);
                                    }
                                }
                            }
                        }
                    }
                    // if codes is empty figure out what it should be
                    if (empty($codes) && isset($fixes[$trans_id])) {
                        $fix_info = $fixes[$trans_id];
                        if ($fix_info['action'] == 'add') {
                            $codes[] = $fix_info['code'] . '-' . $fix_info['amount'];
                            $total += intval($fix_info['amount']);
                        }
                    }
                    if ($first_letter == 'C') {
                        if (!isset($user_by_serial[$id])) continue;
                        $all_info[$trans_id][] = [
                            'type' => $first_letter,
                            'school_id' => $school_by_serial[$id],
                            'admin_id' => 0,
                            'date' => $date,
                            'user_id' => $user_by_serial[$id],
                            'codes' => $codes
                        ];
                    } else if ($first_letter == 'F') {
                        $all_info[$trans_id][] = [
                            'type' => $first_letter,
                            'school_id' => 0,
                            'admin_id' => $admin_id,
                            'date' => $date,
                            'user_id' => 0,
                            'codes' => $codes
                        ];
                    }
                }
                // remove codes for the ones that need to be removed
                foreach ($fixes as $id => $fix_info) {
                    if ($id == $trans_id && $fix_info['action'] == 'remove') {
                        if ($id == 52633) {
                            unset($all_info[$trans_id][1]);
                            unset($all_info[$trans_id][2]);
                            $total -= intval($fix_info['amount']);
                            $total -= intval($fix_info['amount']);
                        } else if ($id == 52070) {
                            $found = false;
                            foreach ($all_info[$id] as $idx => $details) {
                                $codes = $details['codes'];
                                foreach ($codes as $code) {
                                    $code_info = explode('-', $code);
                                    if ($code_info[0] == $fix_info['code'] && $code_info[1] == $fix_info['amount']) {
                                        $found = true;
                                        break;
                                    }
                                }
                            }
                            if ($found) {
                                unset($all_info[$id][$idx]);
                                $total -= intval($fix_info['amount']);
                            }
                        }
                    }
                }
                if ($amount != $total) {
                    $item_count = count($all_info[$trans_id]);
                    $codes_count = count($all_info[$trans_id][--$item_count]['codes']);

                    $amount_to_modify = intval(explode('-', $all_info[$trans_id][$item_count]['codes'][--$codes_count])[1]);
                    if ($amount < $total) {
                        $total -= $amount_to_modify;
                        // remove last item from array
                        unset($all_info[$trans_id][$item_count]);
                    }

                    // if we still have an issue show issue
                    if ($amount != $total) {
                        $total += $amount_to_modify;
                        $errors_found++;
                        echo "<tr><th>Error:</th><th>Modified Amount: $amount_to_modify</th><th>Total: $total</th><td colspan='4'><pre>" .
                            print_r($all_info[$trans_id], true) . "</pre></td></tr>";
                    }
                }
            }
        }
    }
    echo "</table>";
    echo "Errors found: $errors_found";

    echo "<pre>";
//    print_r($all_info);
    echo "</pre>";

    foreach ($all_info as $trans_id => &$items) {
        // first find out if there's any vouchers for discounts for chayolei reg
        $voucher = false;
        foreach ($items as &$item) {
            foreach ($item['codes'] as $j => $code) {
                // if code starts with 'V-'
                if (preg_match('/^V-/', $code)) {
                    $voucher = explode('-', $code)[1];
                    // add to prev item
                    $item['discount'] = intval($voucher);
                    // remove voucher from codes
                    unset($item['codes'][$j]);
                    break 2;
                }
            }
        }
    }
    echo "<pre>"; print_r($all_info); echo "</pre>";

    $success = true;
    $MASHPIA_DB->beginTransaction();

    foreach ($all_info as $trans_id => $items) {
        foreach ($items as $idx => $item) {
            $type = $item['type'];
            $school_id = $item['school_id'];
            $admin_id = $item['admin_id'];
            $user_id = $item['user_id'];
            $date = $item['date'];
            if (new DateTime($date) >= new DateTime('2024-08-07 02:19:54')) {
                $year = 5785;
            } else {
                $year = 5784;
            }
            $codes = $item['codes'];
            foreach ($codes as $code) {
                $code_info = explode('-', $code);
                if (strpos($code, '--') !== false) {
                    $code_info = explode('--', $code);
                    $code_info[1] = -abs($code_info[1]);
                }
                $discount = isset($item['discount']) ? $item['discount'] : 0;
                $res = $stmtInsert->execute([
                    'trans_id' => $trans_id,
                    'user_id' => $user_id,
                    'school_id' => $school_id,
                    'admin_id' => $admin_id,
                    'type' => $code_info[0],
                    'amount' => $code_info[1],
                    'date' => $date,
                    'year' => $year,
                    'discount' => $discount
                ]);
                if (!$res) {
                    $success = false;
                    break 3;
                }
            }
        }
    }

    if ($success) {
      $MASHPIA_DB->commit();
      echo "success";
    } else {
      $MASHPIA_DB->rollBack();
      echo "error";
    }
    ?>
</body>
</html>