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
$start = null;
$end = null;
if (isset($_POST['from_hidden']) && isset($_POST['to_hidden'])) {
    $start = $_POST['from_hidden'];
    $end = $_POST['to_hidden'];
}
// } else {
//     // default to last 30 days
//     $start = date('Y-m-d', strtotime('-30 days'));
//     $end = date('Y-m-d');
// }

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
    <link href="/heDatePicker/dist/css/he-datepicker.css" rel="stylesheet">
    <script src="/heDatePicker/dist/js/he-datepicker.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" 
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" 
        crossorigin="anonymous"></script>
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

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .date-type-toggle {
            margin-bottom: 1rem;
        }

        .col-md-4 {
            position: relative;
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
            <div class="date-type-toggle">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="dateTypeToggle">
                    <label class="form-check-label" for="dateTypeToggle">Use Hebrew Dates</label>
                </div>
            </div>
            <form method="POST" class="row g-3 align-items-end" id="dates_form">
                <div class="col-md-4">
                    <label for="from" class="form-label">From Date</label>
                    <input type="date" class="form-control english-date" id="from" name="from" value="<?php echo $start; ?>" />
                    <input type="text" class="form-control hebrew-date" id="from_he" name="from_he" style="display: none;" autocomplete="off" />
                </div>
                <div class="col-md-4">
                    <label for="to" class="form-label">To Date</label>
                    <input type="date" class="form-control english-date" id="to" name="to" value="<?php echo $end; ?>" />
                    <input type="text" class="form-control hebrew-date" id="to_he" name="to_he" style="display: none;" autocomplete="off" />
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <input type="hidden" id="from_hidden" name="from_hidden">
                    <input type="hidden" id="to_hidden" name="to_hidden">
                    <?php if ($start && $end): ?>
                    <button type="button" class="btn btn-primary ms-2" onClick="downloadCSV()">Download CSV</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div id='main'></div>

    <script>
        // Initialize date pickers and toggle functionality
        $(document).ready(function() {
            console.log('Document ready');
            
            // Handle form submission
            $('#dates_form').on('submit', function(e) {
                e.preventDefault();
                const isHebrew = $('#dateTypeToggle').is(':checked');
                console.log('Form submitted, isHebrew:', isHebrew);
                
                let from, to;
                if (isHebrew) {
                    from = $('#from_he').attr('data-gregorian-date');
                    to = $('#to_he').attr('data-gregorian-date');
                    console.log('Hebrew dates:', from, to);
                } else {
                    from = $('#from').val();
                    to = $('#to').val();
                    console.log('English dates:', from, to);
                }
                    
                if (!from || !to) {
                    alert('Please select both dates');
                    return false;
                }

                const fromDate = new Date(from);
                const toDate = new Date(to);
                if (toDate < fromDate) {
                    alert('End date must be after or same as start date');
                    return false;
                }
                
                $('#from_hidden').val(from);
                $('#to_hidden').val(to);
                
                console.log('Submitting with dates:', from, to);
                this.submit();
            });

            // Handle date type toggle
            $('#dateTypeToggle').on('change', function() {
                console.log('Toggle changed:', this.checked);
                if (this.checked) {
                    $('.english-date').hide().prop('required', false).prop('disabled', true);
                    $('.hebrew-date').show().prop('required', true).prop('disabled', false);
                } else {
                    $('.english-date').show().prop('required', true).prop('disabled', false);
                    $('.hebrew-date').hide().prop('required', false).prop('disabled', true);
                }
            });

            // Initialize Hebrew date pickers
            const fromDatepicker = new JewishDatepicker('#from_he', {
                hideHeader: true,
                color: '#0d6efd'
            });
            const toDatepicker = new JewishDatepicker('#to_he', {
                hideHeader: true,
                color: '#0d6efd'
            });
        });
    </script>

    <script type="text/babel">
        const medalData = <?php echo json_encode($medal_data); ?>;
        const grandTotals = <?php echo json_encode($grand_totals); ?>;
        const dateRange = {
            start: "<?php echo $start ? date('F j, Y', strtotime($start)) : ''; ?>",
            end: "<?php echo $end ? date('F j, Y', strtotime($end)) : ''; ?>"
        };

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
                            Medal Report <small className="text-muted">({dateRange.start} to {dateRange.end})</small>
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
