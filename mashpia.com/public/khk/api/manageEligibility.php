<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';
require_once '../../class.adminSchools.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], false, true);
$schools = $as->getSchools();
$super = $admin_user['auth'] == 'super';

if (!isset($_POST['action']) || !isset($_POST['year'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameters: action or year.'
    ]);
    exit;
}

$action = $_POST['action'];
$year = intval($_POST['year']);

// For add action, we need either user_id or search term
if ($action === 'add' && !isset($_POST['user_id']) && !isset($_POST['search'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing user_id or search parameter for add action.'
    ]);
    exit;
}

// For remove action, we need user_id
if ($action === 'remove' && !isset($_POST['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing user_id parameter for remove action.'
    ]);
    exit;
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
$search = isset($_POST['search']) ? trim($_POST['search']) : null;

if (!in_array($action, ['add', 'remove'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action. Must be "add" or "remove".'
    ]);
    exit;
}

try {
    // First, find the user by ID or search term
    if ($user_id) {
        // Direct user ID lookup
        $user_sql = "SELECT u.user_id, u.user_serial, u.first, u.last, u.school_id
                     FROM users u 
                     WHERE u.user_id = :user_id";
        $params = [':user_id' => $user_id];
    } else {
        // Search by user ID or serial number
        $user_sql = "SELECT u.user_id, u.user_serial, u.first, u.last, u.school_id
                     FROM users u 
                     WHERE (u.user_id = :search OR u.user_serial = :search)";
        $params = [':search' => $search];
    }
    
    if (!$super) {
        $user_sql .= " AND u.school_id IN (" . implode(',', array_keys($schools)) . ")";
    }
    
    $user_stmt = $MASHPIA_DB->prepare($user_sql);
    $user_stmt->execute($params);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update user_id for subsequent operations
    if ($user && !$user_id) {
        $user_id = $user['user_id'];
    }
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'error' => 'User not found or you do not have permission to manage this user.'
        ]);
        exit;
    }
    
    if ($action === 'add') {        
        // Add user to eligibility list
        $insert_sql = "INSERT IGNORE INTO khk_enrollment_eligibility (user_id, year) 
                       VALUES (:user_id, :year)";
        $insert_stmt = $MASHPIA_DB->prepare($insert_sql);
        $result = $insert_stmt->execute([
            ':user_id' => $user_id,
            ':year' => $year,
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'User successfully added to eligibility list.',
                'user' => $user,
                'year' => $year
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to add user to eligibility list.',
                'db_error' => $MASHPIA_DB->errorInfo()
            ]);
        }
        
    } elseif ($action === 'remove') {
        // Check if user is in the eligibility list
        $check_sql = "SELECT * FROM khk_enrollment_eligibility 
                      WHERE user_id = :user_id AND year = :year";
        $check_stmt = $MASHPIA_DB->prepare($check_sql);
        $check_stmt->execute([':user_id' => $user_id, ':year' => $year]);
        
        if (!$check_stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode([
                'success' => false,
                'error' => 'User is not in the eligibility list for this year.'
            ]);
            exit;
        }
        
        // Remove user from eligibility list
        $delete_sql = "DELETE FROM khk_enrollment_eligibility 
                       WHERE user_id = :user_id AND year = :year";
        $delete_stmt = $MASHPIA_DB->prepare($delete_sql);
        $result = $delete_stmt->execute([
            ':user_id' => $user_id,
            ':year' => $year
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'User successfully removed from eligibility list.',
                'user' => $user,
                'year' => $year
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to remove user from eligibility list.',
                'db_error' => $MASHPIA_DB->errorInfo()
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
