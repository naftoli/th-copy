<?php
function parseScreenUrl($url = null) {
    $url = $url ?: $_SERVER['REQUEST_URI'];
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    
    // Pattern: /screens/display.php/{school_id}/{screen_slug}
    if (count($segments) >= 3 && $segments[0] === 'screens' && $segments[1] === 'display.php') {
        return [
            'school_id' => $segments[2],
            'screen_slug' => $segments[3],
            'valid' => true
        ];
    }
    
    return ['valid' => false];
}

// Usage
$route = parseScreenUrl();
if ($route['valid']) {
    $school_id = $route['school_id'];
    $screen_slug = $route['screen_slug'];
    // Load screen...
    echo "School ID: $school_id<br>";
    echo "Screen Slug: $screen_slug<br>";
} else {
    echo "Invalid screen URL";
}
?>