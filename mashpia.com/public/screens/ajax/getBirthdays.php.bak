<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

header('Content-Type: application/json');

// Get the number of days to look back
$days = isset($_GET['start']) ? intval($_GET['start']) : 7;

// Validate days parameter
if ($days < 1 || $days > 90) {
    $days = 7;
}

// Calculate the start date
$start_date = date('Y-m-d', strtotime("-{$days} days"));

// Mock birthdays data for demonstration
// In a real implementation, this would query your users/students database
$birthdays = [
    [
        'id' => 1,
        'name' => 'Sarah Johnson',
        'age' => 12,
        'date' => date('Y-m-d', strtotime('+1 day')),
        'grade' => '6th Grade'
    ],
    [
        'id' => 2,
        'name' => 'Michael Chen',
        'age' => 14,
        'date' => date('Y-m-d', strtotime('+2 days')),
        'grade' => '8th Grade'
    ],
    [
        'id' => 3,
        'name' => 'Emma Rodriguez',
        'age' => 10,
        'date' => date('Y-m-d', strtotime('+4 days')),
        'grade' => '4th Grade'
    ],
    [
        'id' => 4,
        'name' => 'David Thompson',
        'age' => 16,
        'date' => date('Y-m-d', strtotime('+6 days')),
        'grade' => '10th Grade'
    ],
    [
        'id' => 5,
        'name' => 'Lisa Park',
        'age' => 11,
        'date' => date('Y-m-d', strtotime('+7 days')),
        'grade' => '5th Grade'
    ]
];

// Filter birthdays based on the date range
$filtered_birthdays = array_filter($birthdays, function($birthday) use ($start_date) {
    return $birthday['date'] >= $start_date;
});

// Return the filtered birthdays
echo json_encode(array_values($filtered_birthdays));
?> 