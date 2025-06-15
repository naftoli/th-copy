<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

// only super users can see all schools
if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// Get selected dates from form
// default to last 30 days
$start = isset($_POST['from']) ? $_POST['from'] : null;
$end = isset($_POST['to']) ? $_POST['to'] : null;

$medal_data = [];
$grand_totals = [];

// Only query if dates are provided
if ($start && $end) {
    // convert to julian days
    $startArr = explode('-', $start);
    $endArr = explode('-', $end);
    $start_date = gregoriantojd($startArr[1], $startArr[2], $startArr[0]);
    $end_date = gregoriantojd($endArr[1], $endArr[2], $endArr[0]);

    // Query to get medal data based on date range
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
            mm.date_awarded BETWEEN :start_date AND :end_date
        GROUP BY sc.school_id , subject_id , medal_ord
    ");
    $stmt->execute([
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
    
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
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Medal Report</title>
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

        .date-form {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
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
        <div class="date-form">
            <form method="POST" class="row g-3 align-items-end" id="dates_form">
                <div class="col-md-4">
                    <label for="from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="from" name="from" value="<?php echo $start; ?>" required />
                </div>
                <div class="col-md-4">
                    <label for="to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="to" name="to" value="<?php echo $end; ?>" required />
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <?php if ($start && $end): ?>
                    <button type="button" class="btn btn-primary ms-2" onClick="downloadCSV()">Download CSV</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div id='main'></div>

    <script type="text/babel">
        const medalData = <?php echo json_encode($medal_data); ?>;
        const grandTotals = <?php echo json_encode($grand_totals); ?>;
        const dateRange = {
            start: "<?php echo $start; ?>",
            end: "<?php echo $end; ?>"
        };

        // Add form validation
        document.getElementById('dates_form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('from').value);
            const endDate = new Date(document.getElementById('to').value);
            
            if (endDate < startDate) {
                e.preventDefault();
                alert('End date must be after or same as start date');
                return false;
            }
        });

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

            // Helper function to escape CSV fields
            const escapeCSV = (field, alwaysQuote = false) => {
                if (field === null || field === undefined) return '""';
                const stringField = String(field);
                if (alwaysQuote || stringField.includes(',') || stringField.includes('"') || stringField.includes('\n')) {
                    return '"' + stringField.replace(/"/g, '""') + '"';
                }
                return stringField;
            };

            // Create CSV content
            let csvContent = [
                "School",
                ...columnArray,
                "School Total"
            ].map((field, index) => escapeCSV(field, index === 0)).join(",") + "\n";

            // Add school rows
            Object.entries(medalData).forEach(([schoolName, medals]) => {
                const schoolMedalMap = {};
                let schoolTotal = 0;
                medals.forEach(medal => {
                    const key = `${medal.subject_name} - ${medal.medal_name}`;
                    schoolMedalMap[key] = parseInt(medal.total, 10);
                    schoolTotal += parseInt(medal.total, 10);
                });

                const row = [
                    schoolName,
                    ...columnArray.map(column => schoolMedalMap[column] || 0),
                    schoolTotal
                ].map((field, index) => escapeCSV(field, index === 0));
                csvContent += row.join(",") + "\n";
            });

            // Add grand total row
            const grandTotalRow = [
                "Grand Total",
                ...columnArray.map(column => {
                    const [subject, medal] = column.split(" - ");
                    return grandTotals[subject]?.[medal]?.total || 0;
                }),
                Object.values(grandTotals).reduce((sum, medals) => 
                    sum + Object.values(medals).reduce((a, b) => a + parseInt(b.total, 10), 0), 0
                )
            ].map((field, index) => escapeCSV(field, index === 0));
            csvContent += grandTotalRow.join(",");

            // Create and trigger download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            const filename = `medal_report_${dateRange.start}_to_${dateRange.end}.csv`;
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function Table() {
            if (!dateRange.start || !dateRange.end) {
                return (
                    <div className="container-fluid">
                        <div className="school-section">
                            <h3 className="school-header">Please select a date range to generate the report</h3>
                        </div>
                    </div>
                );
            }

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
                        <h3 className="school-header">
                            Medal Report ({dateRange.start} to {dateRange.end})
                        </h3>
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
                                            const [subject, medal] = column.split(" - ");
                                            return (
                                                <td key={index} className="fw-bold">
                                                    {grandTotals[subject]?.[medal]?.total || 0}
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
