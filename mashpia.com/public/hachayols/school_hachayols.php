<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// Business Logic Functions
function getRegisteredStudents() {
    global $MASHPIA_DB, $year, $schools;

    if ($year < 5786) {
        $stmt = $MASHPIA_DB->prepare("
            SELECT u.*, c.*, aa.admin_id 
            FROM users u 
            JOIN admin_auths aa ON aa.id = u.user_id 
            JOIN classes c USING (class_id) 
            JOIN user_registration ur ON ur.user_id = u.user_id 
            WHERE u.school_id = :id 
            AND u.user_registered > 0 
            AND ur.year = :year 
            ORDER BY class_grade, class_sub, hachayol DESC, last, first
        ");
    } else {
        $stmt = $MASHPIA_DB->prepare("
            SELECT u.*, c.*, aa.admin_id, u.hachayol as hachayol_status, 
                IF(htg.user_id IS NOT NULL, 1, 0) as hachayol
            FROM users u 
            JOIN admin_auths aa ON aa.id = u.user_id 
            JOIN classes c USING (class_id) 
            JOIN user_registration ur ON ur.user_id = u.user_id 
            LEFT JOIN hachayols_to_give htg ON htg.user_id = u.user_id 
            WHERE u.school_id = :id 
            AND u.user_registered > 0 
            AND ur.year = :year 
            ORDER BY class_grade, class_sub, hachayol_status DESC, last, first
        ");
    }

    $users = [];
    foreach ($schools as $id => $name) {
        $stmt->execute(['id' => $id, 'year' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $users[$id][$row['class_grade']][$row['class_sub']][] = $row;
        }
    }
    return $users;
}

function getHachayolInfo($user) {
    global $MASHPIA_DB, $year;

    if ($year < 5786) {
        $stmt = $MASHPIA_DB->prepare("
            SELECT u.*, s.school_name 
            FROM users u 
            JOIN schools s USING (school_id) 
            JOIN admin_auths aa ON aa.id = u.user_id 
            JOIN user_registration ur ON ur.user_id = u.user_id 
            WHERE u.hachayol = 1 
            AND u.user_registered > 0 
            AND ur.year = :year
            AND aa.admin_id = :admin_id
        ");
    } else {
        $stmt = $MASHPIA_DB->prepare("
            SELECT u.*, s.school_name 
            FROM users u 
            JOIN schools s USING (school_id) 
            JOIN admin_auths aa ON aa.id = u.user_id 
            JOIN user_registration ur ON ur.user_id = u.user_id 
            JOIN hachayols_to_give htg ON htg.user_id = u.user_id AND htg.year = ur.year 
            WHERE u.user_registered > 0 
            AND ur.year = :year
            AND aa.admin_id = :admin_id 
        ");
    }
    $hachayols = [];
    $stmt->execute([
        'admin_id' => $user['admin_id'],
        'year' => $year
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $hachayols[$row['user_id']] = $row['first'] . ' ' . $row['last'] . ' (' . $row['school_name'] . ')';
    }
    return $hachayols;
}

function getGradeData($students) {
    $total = 0;
    $rows = [];
    foreach ($students as $user) {
        if (intval($user['hachayol'])) {
            $total++;
        }
        $rows[] = [
            'grade' => $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : ''),
            'hebrew_name' => $user['first_he'] . ' ' . $user['last_he'],
            'name' => $user['first'] . ' ' . $user['last'],
            'family_id' => $user['admin_id'],
            'hachayol' => intval($user['hachayol']) ? 'yes' : 'no',
            'children' => getHachayolInfo($user)
        ];
    }
    return ['total' => $total, 'rows' => $rows];
}

// Get data
$year = GlobalSettings::getRegistrationYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$users = getRegisteredStudents();

// Prepare data for display
$report_data = [];
foreach ($users as $school_id => $grades) {
    foreach ($grades as $grade => $subs) {
        foreach ($subs as $sub => $students) {
            $report_data[] = [
                'school_name' => $schools[$school_id],
                'grade' => $grade,
                'sub' => $sub,
                'data' => getGradeData($students)
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hachayol Report</title>
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
            /* overflow-x: auto; */
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .hachayol-yes {
            color: #27ae60;
            font-weight: 600;
        }

        .hachayol-no {
            color: #e74c3c;
        }

        .children-list {
            max-height: 100px;
            overflow-y: auto;
        }

        @media print {
            .school-section {
                page-break-after: auto;
            }
            
            .table {
                page-break-inside: auto;
            }
            
            .table thead {
                display: table-header-group;
            }
            
            .table tfoot {
                display: table-footer-group;
            }
            
            .table tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="margin-top: 2rem;">
        <button class="btn btn-primary mb-3 d-print-none" style="float: right; margin-right: 50px;" onClick="window.print()">
            🖨️ Print Report
        </button>
    </div>
    <div id='main'></div>

    <script type="text/babel">
        const reportData = <?php echo json_encode($report_data); ?>;

        function Table() {
            return (
                <div className="container-fluid">
                    {reportData.map((section, index) => (
                        <div key={index} className="school-section">
                            <h3 className="school-header">
                                {section.school_name} ({section.grade}
                                {section.sub ? `-${section.sub}` : ''})
                            </h3>
                            <div className="table-container">
                                <table className="table table-striped table-hover table-bordered">
                                    <thead className="table-light">
                                        <tr>
                                            <th scope="col">Grade</th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Family ID</th>
                                            <th scope="col">Hachayol</th>
                                            <th scope="col">Child(ren) Receiving Hachayol</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {section.data.rows.map((row, rowIndex) => (
                                            <tr key={rowIndex}>
                                                <td>{row.grade}</td>
                                                <td>{row.name}</td>
                                                <td>{row.family_id}</td>
                                                <td className={row.hachayol === 'yes' ? 'hachayol-yes' : 'hachayol-no'}>
                                                    {row.hachayol}
                                                </td>
                                                <td>
                                                    <div className="children-list">
                                                        {Object.values(row.children).map((child, i) => (
                                                            <div key={i}>{child}</div>
                                                        ))}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="table-secondary">
                                            <td colSpan="4" className="text-end fw-bold">Total Hachayols:</td>
                                            <td colSpan="2" className="fw-bold">{section.data.total}</td>
                                        </tr>
                                    </tfoot>
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
