<?php
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // in km

    $d_lat = deg2rad($lat2 - $lat1);
    $d_lon = deg2rad($lon2 - $lon1);

    $a = sin($d_lat / 2) * sin($d_lat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($d_lon / 2) * sin($d_lon / 2);
    $c = 2 * asin(sqrt($a));
    $distance = $earth_radius * $c;

    return $distance;
}

function tsp($locations, $radius) {
    $visited = array();
    $current = 0;
    $total_distance = 0;

    $visited[] = $current;

    while (count($visited) < count($locations)) {
        $min_distance = INF;
        $min_index = -1;

        for ($i = 0; $i < count($locations); $i++) {
            if (!in_array($i, $visited)) {
                $distance = calculateDistance($locations[$current][0], $locations[$current][1], $locations[$i][0], $locations[$i][1]);

                if ($distance < $min_distance && $distance <= $radius) {
                    $min_distance = $distance;
                    $min_index = $i;
                }
            }
        }

        $visited[] = $min_index;
        $current = $min_index;
        $total_distance += $min_distance;
    }

    $total_distance += calculateDistance($locations[$visited[count($visited)-1]][0], $locations[$visited[count($visited)-1]][1], $locations[0][0], $locations[0][1]);

    return array($visited, $total_distance);
}

$locations = array(
    array(40.7128, -74.0060), // New York
    array(37.7749, -122.4194), // San Francisco
    array(51.5074, -0.1278), // London
    array(35.6895, 139.6917) // Tokyo
);

$result = tsp($locations, 50); // 50 mile radius

$waypoints = array();
foreach ($result[0] as $index) {
    $waypoints[] = $locations[$index][0] . ',' . $locations[$index][1];
}

$waypoints_str = implode('|', $waypoints);

$url = 'https://www.google.com/maps/dir/' . $waypoints_str;

echo 'Visited locations: ';
foreach ($result[0] as $index) {
    echo $index . ' ';
}
echo '<br>Total distance: ' . $result[1] . ' km<br>';
echo '<a href="' . $url . '">View on Google Maps</a>';