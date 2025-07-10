<?php
// Get parameters from URL rewriting
$school_id = isset($_GET['school_id']) ? $_GET['school_id'] : null;
$screen_slug = isset($_GET['screen_slug']) ? $_GET['screen_slug'] : null;

// Fallback to parsing URL if parameters not available
if (!$school_id || !$screen_slug) {
    function parseScreenUrl($url = null) {
        $url = $url ?: $_SERVER['REQUEST_URI'];
        $path = parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        // Pattern: /screens/{school_id}/{screen_slug}
        if (count($segments) >= 3 && $segments[0] === 'screens') {
            return [
                'school_id' => $segments[1],
                'screen_slug' => $segments[2],
                'valid' => true
            ];
        }
        
        return ['valid' => false];
    }

    $route = parseScreenUrl();
    if ($route['valid']) {
        $school_id = $route['school_id'];
        $screen_slug = $route['screen_slug'];
    }
}

// Display screen information
if ($school_id && $screen_slug) {
    echo "School ID: $school_id<br>";
    echo "Screen Slug: $screen_slug<br>";
    echo "<p>Digital Screen Display</p>";
} else {
    echo "Invalid screen URL";
}
?>