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

// Mock promotions data for demonstration
// In a real implementation, this would query your promotions database
$promotions = [
    [
        'id' => 1,
        'title' => 'Summer Camp Registration',
        'description' => 'Early bird registration for summer camp is now open! Save 20% when you register before June 1st.',
        'date' => date('Y-m-d', strtotime('+3 days')),
        'type' => 'camp'
    ],
    [
        'id' => 2,
        'title' => 'Parent-Teacher Conference',
        'description' => 'Annual parent-teacher conference scheduled for next week. Please book your appointment.',
        'date' => date('Y-m-d', strtotime('+5 days')),
        'type' => 'meeting'
    ],
    [
        'id' => 3,
        'title' => 'School Fundraiser',
        'description' => 'Support our school with the annual fundraiser. All proceeds go towards new educational materials.',
        'date' => date('Y-m-d', strtotime('+7 days')),
        'type' => 'fundraiser'
    ],
    [
        'id' => 4,
        'title' => 'Holiday Break Reminder',
        'description' => 'School will be closed for the upcoming holiday break. Classes resume on January 8th.',
        'date' => date('Y-m-d', strtotime('+10 days')),
        'type' => 'holiday'
    ]
];

// Filter promotions based on the date range
$filtered_promotions = array_filter($promotions, function($promotion) use ($start_date) {
    return $promotion['date'] >= $start_date;
});

// Return the filtered promotions
echo json_encode(array_values($filtered_promotions));
?> 