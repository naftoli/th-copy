<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

// only super users can see all schools
if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

$start_date = 2460448; // May 17, 2024
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// Query to get medal data since May 5, 2024
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_name, subject_name, medal_name, COUNT(*) AS total,
        s.subject_id, m.medal_ord
    FROM
        medal_marks mm
            JOIN
        subjects s USING (subject_id)
            JOIN
        medals m USING (medal_ord)
            JOIN
        users u USING (user_id)
            JOIN
        schools sc ON sc.school_id = u.school_id
    WHERE
        mm.date_awarded >= :start_date
    GROUP BY sc.school_id , subject_id , medal_ord
");
$stmt->execute(['start_date' => $start_date]);
$medal_data = [];
$grand_totals = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $medal_data[$row['school_name']][] = [
        'medal_name' => $row['medal_name'],
        'subject_name' => $row['subject_name'],
        'total' => $row['total'],
        'subject_id' => $row['subject_id'],
        'medal_ord' => $row['medal_ord']
    ];
    if (!isset($grand_totals[$row['subject_name']][$row['medal_name']])) {
        $grand_totals[$row['subject_name']][$row['medal_name']] = [
            'total' => $row['total'],
            'subject_id' => $row['subject_id'],
            'medal_ord' => $row['medal_ord']
        ];
    } else {
        $grand_totals[$row['subject_name']][$row['medal_name']]['total'] += $row['total'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Medal Report - Since May 5, 2024</title>
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <style>
        #main {
            margin: 2rem;
            padding: 1rem;
        }

        .school-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        .school-header {
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #3498db;
        }

        .table-container {
            overflow-x: auto;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        @media print {
            .school-section {
                background: none;
                border-radius: 0;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .school-section:not(:first-child) {
                page-break-before: always;
            }
            
            .school-section {
                page-break-after: always;
            }
            
            .table tr {
                page-break-inside: avoid;
            }

            .table {
                border-collapse: collapse;
                width: 100% !important;
            }

            .table thead {
                display: table-header-group;
            }

            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                width: 100% !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            #main {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="margin-top: 2rem;">
        <button class="btn btn-primary mb-3 d-print-none" style="float: right; margin-right: 50px;" onClick="downloadCSV()">
            Download CSV
        </button>
    </div>
    <div id='main'></div>

    <script type="text/babel">
        const medalData = <?php echo json_encode($medal_data); ?>;
        const grandTotals = <?php echo json_encode($grand_totals); ?>;

        function downloadCSV() {
            // Get all unique subject/medal combinations for column headers
            const columns = new Set();
            const columnData = new Map();
            
            Object.values(medalData).forEach(schoolMedals => {
                schoolMedals.forEach(medal => {
                    const key = `${medal.subject_name} - ${medal.medal_name}`;
                    columns.add(key);
                    columnData.set(key, {
                        subject_id: medal.subject_id,
                        medal_ord: medal.medal_ord
                    });
                });
            });
            
            // Sort columns based on subject_id and medal_ord
            const columnArray = Array.from(columns).sort((a, b) => {
                const dataA = columnData.get(a);
                const dataB = columnData.get(b);
                if (dataA.subject_id !== dataB.subject_id) {
                    return dataA.subject_id - dataB.subject_id;
                }
                return dataA.medal_ord - dataB.medal_ord;
            });

            // Create CSV content
            let csvContent = "School," + columnArray.join(",") + ",School Total\n";

            // Add school rows
            Object.entries(medalData).forEach(([schoolName, medals]) => {
                const schoolMedalMap = {};
                let schoolTotal = 0;
                medals.forEach(medal => {
                    const key = `${medal.subject_name} - ${medal.medal_name}`;
                    schoolMedalMap[key] = parseInt(medal.total, 10);
                    schoolTotal += parseInt(medal.total, 10);
                });

                const row = [schoolName];
                columnArray.forEach(column => {
                    row.push(schoolMedalMap[column] || 0);
                });
                row.push(schoolTotal);
                csvContent += row.join(",") + "\n";
            });

            // Add grand total row
            const grandTotalRow = ["Grand Total"];
            columnArray.forEach(column => {
                const [subject, medal] = column.split(" - ");
                grandTotalRow.push(grandTotals[subject]?.[medal]?.total || 0);
            });
            const grandTotal = Object.values(grandTotals).reduce((sum, medals) => 
                sum + Object.values(medals).reduce((a, b) => a + parseInt(b.total, 10), 0), 0
            );
            grandTotalRow.push(grandTotal);
            csvContent += grandTotalRow.join(",");

            // Create and trigger download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "medal_report.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function Table() {
            // Get all unique subject/medal combinations for column headers
            const columns = new Set();
            const columnData = new Map();
            
            Object.values(medalData).forEach(schoolMedals => {
                schoolMedals.forEach(medal => {
                    const key = `${medal.subject_name} - ${medal.medal_name}`;
                    columns.add(key);
                    columnData.set(key, {
                        subject_id: medal.subject_id,
                        medal_ord: medal.medal_ord
                    });
                });
            });
            
            // Sort columns based on subject_id and medal_ord
            const columnArray = Array.from(columns).sort((a, b) => {
                const dataA = columnData.get(a);
                const dataB = columnData.get(b);
                if (dataA.subject_id !== dataB.subject_id) {
                    return dataA.subject_id - dataB.subject_id;
                }
                return dataA.medal_ord - dataB.medal_ord;
            });

            return (
                <div className="container-fluid">
                    <div className="school-section">
                        <h3 className="school-header">Medal Report</h3>
                        <div className="table-container">
                            <table className="table table-striped table-hover table-bordered">
                                <thead className="table-light">
                                    <tr>
                                        <th scope="col">School</th>
                                        {columnArray.map((column, index) => (
                                            <th key={index} scope="col">{column}</th>
                                        ))}
                                        <th scope="col">School Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {Object.entries(medalData).map(([schoolName, medals], index) => {
                                        // Create a map of subject-medal combinations to totals for this school
                                        const schoolMedalMap = {};
                                        let schoolTotal = 0;
                                        medals.forEach(medal => {
                                            const key = `${medal.subject_name} - ${medal.medal_name}`;
                                            schoolMedalMap[key] = parseInt(medal.total, 10);
                                            schoolTotal += parseInt(medal.total, 10);
                                        });

                                        return (
                                            <tr key={index}>
                                                <td>{schoolName}</td>
                                                {columnArray.map((column, colIndex) => (
                                                    <td key={colIndex}>{schoolMedalMap[column] || 0}</td>
                                                ))}
                                                <td className="fw-bold">{schoolTotal}</td>
                                            </tr>
                                        );
                                    })}
                                    <tr className="table-secondary">
                                        <td className="fw-bold">Grand Total</td>
                                        {columnArray.map((column, index) => {
                                            const [subject, medal] = column.split(' - ');
                                            return (
                                                <td key={index} className="fw-bold">
                                                    {parseInt(grandTotals[subject]?.[medal]?.total || 0, 10)}
                                                </td>
                                            );
                                        })}
                                        <td className="fw-bold">
                                            {Object.values(grandTotals).reduce((sum, medals) => 
                                                sum + Object.values(medals).reduce((a, b) => a + parseInt(b.total, 10), 0), 0
                                            )}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            );
        }

        const container = document.getElementById('main');
        const root = ReactDOM.createRoot(container);
        root.render(<Table />);
    </script>
</body>
</html>
