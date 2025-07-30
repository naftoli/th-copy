<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

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
            SELECT u.*, c.*, aa.admin_id 
            FROM users u 
            JOIN admin_auths aa ON aa.id = u.user_id 
            JOIN classes c USING (class_id) 
            JOIN user_registration ur ON ur.user_id = u.user_id 
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
        $children = getHachayolInfo($user);
        if (in_array($user['user_id'], $children)) {
            $total++;
        }
        $rows[] = [
            'grade' => $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : ''),
            'hebrew_name' => $user['first_he'] . ' ' . $user['last_he'],
            'name' => $user['first'] . ' ' . $user['last'],
            'family_id' => $user['admin_id'],
            'children' => $children,
            'hachayol' => in_array($user['user_id'], $children) ? 'yes' : 'no',
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
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Hachayol Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- React -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 20px auto;
            padding: 30px;
            max-width: 1800px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-weight: 300;
            font-size: 2.5rem;
        }
        
        .print-button {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .school-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }
        
        .school-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px 10px;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            font-size: 0.9rem;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .table-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268) !important;
            color: white !important;
        }
        
        .hachayol-yes {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .hachayol-no {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .children-list {
            max-height: 100px;
            overflow-y: auto;
            background: var(--light-bg);
            border-radius: 6px;
            padding: 8px;
            border: 1px solid var(--border-color);
        }
        
        .children-list div {
            padding: 4px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.85rem;
        }
        
        .children-list div:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .school-header {
                font-size: 1.1rem;
                padding: 15px;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
        }
        
        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .main-container {
                background: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
            }
            
            .page-header {
                background: none !important;
                color: black !important;
                border-bottom: 2px solid black !important;
                border-radius: 0 !important;
            }
            
            .school-section {
                background: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                page-break-after: always;
            }
            
            .school-section:not(:first-child) {
                page-break-before: always;
            }
            
            .school-header {
                background: none !important;
                color: black !important;
                border-bottom: 2px solid black !important;
                border-radius: 0 !important;
            }
            
            .table-container {
                background: none !important;
                box-shadow: none !important;
            }
            
            .table {
                border-collapse: collapse;
                width: 100% !important;
            }
            
            .table thead {
                display: table-header-group;
            }
            
            .table thead th {
                background: #f8f9fa !important;
                color: black !important;
                border: 1px solid black !important;
            }
            
            .table tbody td {
                border: 1px solid black !important;
            }
            
            .table tr {
                page-break-inside: avoid;
            }
            
            .print-button {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1><i class="bi bi-file-earmark-text"></i> Hachayol Report</h1>
        </div>
        
        <div class="text-end mb-4 d-print-none">
            <button class="print-button" onClick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
        
        <div id='main'></div>
    </div>

    <script type="text/babel">
        const reportData = <?php echo json_encode($report_data); ?>;

        function Table() {
            return (
                <div className="container-fluid">
                    {reportData.map((section, index) => (
                        <div key={index} className="school-section">
                            <h3 className="school-header">
                                <i className="bi bi-building"></i> {section.school_name} ({section.grade}
                                {section.sub ? `-${section.sub}` : ''}) - <strong>{section.data.total}</strong>
                            </h3>
                            <div className="table-container">
                                <div className="table-responsive">
                                    <table className="table table-striped table-hover">
                                        <thead>
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
                                                    <td className="text-center"><strong>{row.grade}</strong></td>
                                                    <td><strong>{row.name}</strong></td>
                                                    <td className="text-center">{row.family_id}</td>
                                                    <td className="text-center">
                                                        <span className={row.hachayol === 'yes' ? 'hachayol-yes' : 'hachayol-no'}>
                                                            {row.hachayol === 'yes' ? 
                                                                <><i className="bi bi-check-circle"></i> Yes</> : 
                                                                <><i className="bi bi-x-circle"></i> No</>
                                                            }
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div className="children-list">
                                                            {Object.values(row.children).length > 0 ? 
                                                                Object.values(row.children).map((child, i) => (
                                                                    <div key={i}><i className="bi bi-person"></i> {child}</div>
                                                                )) : 
                                                                <div className="text-muted"><i className="bi bi-dash"></i> No children</div>
                                                            }
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr className="table-secondary">
                                                <td colSpan="4" className="text-end fw-bold">Total Hachayols:</td>
                                                <td className="text-center fw-bold">{section.data.total}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
