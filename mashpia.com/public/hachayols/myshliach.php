<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

// get all info about admins and children registered in myshliach
$admins = [];
$children = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.*, u.user_id, u.first as user_first, u.last as user_last
    FROM
        admins a 
            JOIN
        admin_auths aa USING (admin_id) 
            JOIN
        users u ON u.user_id = aa.id 
            JOIN 
        user_registration ur ON ur.user_id = u.user_id 
    WHERE
        u.user_registered > 0
            AND u.school_id = 61 AND ur.year = :year
");
$stmt->execute([
    'year'  => $year,
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $admins[$row['admin_id']] = $row;
    $children[$row['admin_id']][$row['user_id']] = $row['user_first'] . ' ' . $row['user_last'];
}

$chayolei_charges = [];
$shipping_charges = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        * 
    FROM
        registration_charges  
    WHERE
        year = :year 
            AND (type = 'THE' OR type LIKE 'THMS%')
");
$stmt->execute([
    'year'  => $year,
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    if ($row['type'] == 'THE') {
        $chayolei_charges[$row['user_id']][] = $row;
    } else {
        $shipping_charges[$row['admin_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf8"/>
  <title>MyShliach Hachayol Shipping Charges Report</title>
  <style>
    tr, th, td {
      font-size: 14px;
      padding: 5px;
      font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    }
    button {
      margin: 10px 0;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }
  </style>
</head>
<body>
<button onclick="downloadAsCSV()">Download as CSV</button>
<table id="report">
  <tr>
    <th>Family ID</th>
    <th>Family Name</th>
    <th>Address</th>
    <th>Number of Registered Children</th>
    <th>Children Registered</th>
    <th>Registration amount paid</th>
    <th>Shipping Fee Paid</th>
  </tr>
    <?php
    foreach ($children as $admin_id => $details) {
        $admin = $admins[$admin_id];
        $name = $admin['first'] . ' ' . $admin['last'];
        $address = $admin['admin_address1'] . " " . $admin['admin_address2'] . "<br />" . $admin['admin_city'] .
            ", " . $admin['admin_state'] . "<br />" . $admin['admin_postal'] . "<br />" . $admin['admin_country'];
        echo "<tr><td>" . $admin_id . "</td><td>" . $name . "</td><td>" . $address . "</td><td>" . count($details) . "</td><td>";
        foreach ($details as $child_name) {
            echo $child_name . "<br />";
        }
        echo "</td><td>";
        foreach ($details as $user_id => $child_name) {
            if (isset($chayolei_charges[$user_id])) {
                foreach ($chayolei_charges[$user_id] as $charge) {
                    echo $child_name . ": " . $charge['amount'];
                    if (intval($charge['discount']) > 0) echo ' (discount: ' . $charge['discount'] . ')';
                    echo "<br />";
                }
            }
        }
        echo "</td><td>";
        $paid = 0;
        if (isset($shipping_charges[$admin_id])) {
            foreach ($shipping_charges[$admin_id] as $charge) {
                if ($charge['type'] != 'THE') {
                    $paid += $charge['amount'];
                }
            }
        }
        if (!$paid) echo "NOT PAID";
        else echo $paid;
        echo "</td></tr>";
    }
    ?>
</table>
</body>
    <script>
        function downloadAsCSV() {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Family ID,Family Name,Address,Number of Registered Children,Children Registered,Registration amount paid,Shipping Fee Paid\n";
            
            const rows = document.querySelectorAll("#report tr");
            console.log("Total rows found:", rows.length);
            
            if (rows.length <= 1) {
                alert("No data to export");
                return;
            }
            
            let processedRows = 0;
            
            // Skip the first row (header) and process only data rows
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.querySelectorAll("td");
                
                console.log(`Row ${i}: Found ${cells.length} cells`);
                
                if (cells.length >= 7) {
                    // Use innerHTML to get the full content including HTML, then clean it
                    const familyId = cells[0].innerHTML.trim();
                    const familyName = cells[1].innerHTML.trim();
                    const address = cells[2].innerHTML.trim();
                    const numberOfChildren = cells[3].innerHTML.trim();
                    const childrenRegistered = cells[4].innerHTML.trim();
                    const registrationAmountPaid = cells[5].innerHTML.trim();
                    const shippingFeePaid = cells[6].innerHTML.trim();
                    
                    console.log(`Row ${i} raw data:`, {
                        familyId, familyName, address, numberOfChildren, 
                        childrenRegistered, registrationAmountPaid, shippingFeePaid
                    });
                    
                    // Clean up the data - remove HTML br tags and replace with semicolons, then clean whitespace
                    const cleanFamilyId = familyId.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
                    const cleanFamilyName = familyName.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
                    const cleanAddress = address.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
                    const cleanNumberOfChildren = numberOfChildren.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
                    const cleanChildrenRegistered = childrenRegistered.replace(/<br\s*\/?>/gi, '; ').replace(/\s+/g, ' ').trim();
                    const cleanRegistrationAmountPaid = registrationAmountPaid.replace(/<br\s*\/?>/gi, '; ').replace(/\s+/g, ' ').trim();
                    const cleanShippingFeePaid = shippingFeePaid.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
                    
                    console.log(`Row ${i} cleaned data:`, {
                        cleanFamilyId, cleanFamilyName, cleanAddress, cleanNumberOfChildren,
                        cleanChildrenRegistered, cleanRegistrationAmountPaid, cleanShippingFeePaid
                    });
                    
                    // Escape quotes and wrap in quotes for CSV
                    const escapedFamilyId = cleanFamilyId.replace(/"/g, '""');
                    const escapedFamilyName = cleanFamilyName.replace(/"/g, '""');
                    const escapedAddress = cleanAddress.replace(/"/g, '""');
                    const escapedNumberOfChildren = cleanNumberOfChildren.replace(/"/g, '""');
                    const escapedChildrenRegistered = cleanChildrenRegistered.replace(/"/g, '""');
                    const escapedRegistrationAmountPaid = cleanRegistrationAmountPaid.replace(/"/g, '""');
                    const escapedShippingFeePaid = cleanShippingFeePaid.replace(/"/g, '""');
                    
                    const csvRow = `"${escapedFamilyId}","${escapedFamilyName}","${escapedAddress}","${escapedNumberOfChildren}","${escapedChildrenRegistered}","${escapedRegistrationAmountPaid}","${escapedShippingFeePaid}"\n`;
                    csvContent += csvRow;
                    processedRows++;
                    
                    console.log(`Row ${i} CSV row:`, csvRow);
                } else {
                    console.log(`Row ${i}: Skipped - insufficient cells (${cells.length})`);
                }
            }
            
            console.log("Total rows processed:", processedRows);
            console.log("CSV content length:", csvContent.length);
            console.log("First 500 chars of CSV:", csvContent.substring(0, 500));
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "myshliach_hachayol_report.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            alert(`CSV download complete! Processed ${processedRows} rows.`);
        }
        </script>
</html>
