<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        aa.admin_id, u.user_registered, u.user_id, c.class_grade, h.hachayol_id
    FROM
        users u
            JOIN
        user_registration ur USING (user_id)
            JOIN
        admin_auths aa ON aa.id = u.user_id
            JOIN
        classes c USING (class_id)
            LEFT JOIN
        hachayols_to_give h USING (user_id, year)
    WHERE
        ur.year = :year
    ORDER BY aa.admin_id, class_grade
");
$stmt->execute(['year' => $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$info = [];
foreach ($rows as $row) {
    $info[$row['admin_id']][] = $row;
}

// loop through array
// check which admin id does not have any children that have a hachayol_id 
// if so, find a child to give it to. only check from registered children
// within registered childre, find those that are in grade 5 or below. give to child in highest grade.
// if no child is in grades 5 or below, give to child in highest grade.
// also check for any children that have a hachayol_id but are not registered. swap with a registered child.
// use same logic as above regarding class grade.
// if no children are registered, do nothing.

$updates = [];
$issues_found = [];

foreach ($info as $admin_id => $children) {
    // Separate children into categories
    $registered_with_hachayol = [];
    $registered_without_hachayol = [];
    $unregistered_with_hachayol = [];
    $has_any_hachayol = false;
    
    foreach ($children as $child) {
        $is_registered = $child['user_registered'] > 0;
        $has_hachayol = !empty($child['hachayol_id']);
        
        if ($has_hachayol) {
            $has_any_hachayol = true;
        }
        
        if ($is_registered && $has_hachayol) {
            $registered_with_hachayol[] = $child;
        } elseif ($is_registered && !$has_hachayol) {
            $registered_without_hachayol[] = $child;
        } elseif (!$is_registered && $has_hachayol) {
            $unregistered_with_hachayol[] = $child;
        }
    }
    
    // Case 1: Admin has no children with hachayol_id at all
    if (!$has_any_hachayol && count($registered_without_hachayol) > 0) {
        // Find best candidate: grade 5 or below (highest grade), or just highest grade
        $candidate = findBestCandidate($registered_without_hachayol);
        if ($candidate) {
            $issues_found[] = "Admin $admin_id has no hachayol assigned. Will assign to user {$candidate['user_id']} (Grade {$candidate['class_grade']})";
            $updates[] = [
                'admin_id' => $admin_id,
                'user_id' => $candidate['user_id'],
                'action' => 'assign_new'
            ];
        }
    }
    
    // Case 2: Admin has unregistered children with hachayol_id
    if (count($unregistered_with_hachayol) > 0 && count($registered_without_hachayol) > 0) {
        foreach ($unregistered_with_hachayol as $unregistered) {
            // Find best registered candidate to swap with
            $candidate = findBestCandidate($registered_without_hachayol);
            if ($candidate) {
                $issues_found[] = "Admin $admin_id has unregistered user {$unregistered['user_id']} with hachayol {$unregistered['hachayol_id']}. Will swap to registered user {$candidate['user_id']} (Grade {$candidate['class_grade']})";
                $updates[] = [
                    'admin_id' => $admin_id,
                    'from_user_id' => $unregistered['user_id'],
                    'to_user_id' => $candidate['user_id'],
                    'hachayol_id' => $unregistered['hachayol_id'],
                    'action' => 'swap'
                ];
                
                // Remove this candidate from available pool
                $registered_without_hachayol = array_filter($registered_without_hachayol, function($c) use ($candidate) {
                    return $c['user_id'] != $candidate['user_id'];
                });
            }
        }
    }
}

/**
 * Find the best candidate to receive a hachayol
 * Priority: Grade 5 or below (highest grade first), otherwise highest grade
 */
function findBestCandidate($children) {
    if (empty($children)) {
        return null;
    }
    
    // Sort by grade descending
    usort($children, function($a, $b) {
        return intval($b['class_grade']) - intval($a['class_grade']);
    });
    
    // First try to find grade 5 or below
    foreach ($children as $child) {
        if (intval($child['class_grade']) <= 5) {
            return $child;
        }
    }
    
    // If no grade 5 or below, return highest grade
    return $children[0];
}

// Display issues found
echo "<h2>Issues Found:</h2>";
if (empty($issues_found)) {
    echo "<p>No issues found!</p>";
} else {
    echo "<ul>";
    foreach ($issues_found as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
}

// Display updates to be made
echo "<h2>Updates to be made:</h2>";
echo "<pre>";
print_r($updates);
echo "</pre>";

// Uncomment below to actually execute the updates
if (!empty($updates) && isset($_GET['fix']) && $_GET['fix'] == 1) {
    $MASHPIA_DB->beginTransaction();
    try {
        foreach ($updates as $update) {
            if ($update['action'] == 'assign_new') {
                // Insert new hachayol assignment
                $stmt = $MASHPIA_DB->prepare("
                    INSERT INTO hachayols_to_give (user_id, year, admin_id)
                    VALUES (:user_id, :year, :admin_id)
                ");
                $stmt->execute([
                    'admin_id' => $update['admin_id'],
                    'user_id' => $update['user_id'],
                    'year' => $year
                ]);
            } elseif ($update['action'] == 'swap') {
                // Update existing hachayol assignment
                $stmt = $MASHPIA_DB->prepare("
                    UPDATE hachayols_to_give 
                    SET user_id = :to_user_id, 
                    admin_id = :admin_id 
                    WHERE user_id = :from_user_id 
                    AND year = :year 
                    AND hachayol_id = :hachayol_id
                ");
                $stmt->execute([
                    'admin_id' => $update['admin_id'],
                    'to_user_id' => $update['to_user_id'],
                    'from_user_id' => $update['from_user_id'],
                    'year' => $year,
                    'hachayol_id' => $update['hachayol_id']
                ]);
            }
        }
        $MASHPIA_DB->commit();
        echo "<p style='color: green;'><strong>Updates executed successfully!</strong></p>";
    } catch (Exception $e) {
        $MASHPIA_DB->rollBack();
        echo "<p style='color: red;'><strong>Error: " . $e->getMessage() . "</strong></p>";
    }
}
