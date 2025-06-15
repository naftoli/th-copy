<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// Get data
$year = GlobalSettings::getRegistrationYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

// Query to get medal data since May 5, 2024
$sql = "
    SELECT 
        school_name, subject_name, medal_name, COUNT(*) AS total
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
        mm.date_awarded >= 2460448
    GROUP BY sc.school_id , subject_id , medal_ord
";

$result = mysql_query($sql);
$medal_data = [];
while ($row = mysql_fetch_assoc($result)) {
    $medal_data[$row['school_name']][] = [
        'medal_name' => $row['medal_name'],
        'medal_type' => $row['medal_type'],
        'count' => $row['medal_count']
    ];
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
        <button class="btn btn-primary mb-3 d-print-none" style="float: right; margin-right: 50px;" onClick="window.print()">
            Print Report
        </button>
    </div>
    <div id='main'></div>

    <script type="text/babel">
        const medalData = <?php echo json_encode($medal_data); ?>;

        function Table() {
            return (
                <div className="container-fluid">
                    {Object.entries(medalData).map(([schoolName, medals], index) => (
                        <div key={index} className="school-section">
                            <h3 className="school-header">
                                {schoolName}
                            </h3>
                            <div className="table-container">
                                <table className="table table-striped table-hover table-bordered">
                                    <thead className="table-light">
                                        <tr>
                                            <th scope="col">Medal Type</th>
                                            <th scope="col">Medal Name</th>
                                            <th scope="col">Medals Earned</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {medals.map((medal, medalIndex) => (
                                            <tr key={medalIndex}>
                                                <td>{medal.medal_type}</td>
                                                <td>{medal.medal_name}</td>
                                                <td>{medal.count}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ))}
                </div>
            );
        }

        const container = document.getElementById('main');
        const root = ReactDOM.createRoot(container);
        root.render(<Table />);
    </script>
</body>
</html>
