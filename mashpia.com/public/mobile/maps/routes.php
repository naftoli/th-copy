<?php
$admin_auth = ['user'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$gender = $_GET['g'] ?? 'M';
$bus = $_GET['bus'] ?? '';

$sql = "
    SELECT 
        u.first,
        u.last, 
        tc.*
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id) 
    WHERE
        year = :year AND ultimate_trip = 1
            AND u.gender = :gender
";
if ($bus && in_array($bus, ['thurs', 'ms'])) $sql .= " AND {$bus}_walking = 2";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([':year' => $year, ':gender' => $gender]);
$rows = $stmt->fetchAll();

$addresses = [];
foreach ($rows as $i => $row) {
    $addresses[$i] = [
        'address' => $row['host_street_num'] . ' ' . $row['host_street'] . ' ' . $row['host_street_apt'] . ', ' . $row['host_street_num_suffix'] . ' Brooklyn, NY',
    ];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Chidon Map</title>
    <!-- prettier-ignore -->
    <script>
        const GOOGLE_MAPS_API_KEY = 'AIzaSyBegHlUZbSVqYfm3Pp76PSHzcYfEKG9ieo';

        (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
            key: GOOGLE_MAPS_API_KEY,
            v: "weekly",
            // Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
            // Add other bootstrap parameters as needed, using camel case.
        });
    </script>
    <style>
        gmp-map {
            height: 100%;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
  </head>
  <body>
    <!-- The map, centered at Uluru, Australia. -->
    <gmp-map center="40.668975,-73.942824" zoom="20" map-id="chidon-map" heading="0">
        <div id="controls" slot="control-inline-start-block-start">
            <h3>Chidon Route</h3>
        </div>
    </gmp-map>

  </body>
  <script>   
    let mapPolylines = [];
    const mapElement = document.querySelector('gmp-map');
    let innerMap;
    // Initialize and add the map.
    async function initMap() {
        //  Request the needed libraries.
        await google.maps.importLibrary('maps');
        innerMap = await mapElement.innerMap;
        innerMap.setOptions({
            mapTypeControl: false,
        });
        // Call the function after the map is loaded.
        google.maps.event.addListenerOnce(innerMap, 'idle', () => {
            getDirections();
        });
    }
    function toLatLngLiteral(location) {
        return {
            lat: typeof location.lat === 'function' ? location.lat() : location.lat,
            lng: typeof location.lng === 'function' ? location.lng() : location.lng
        };
    }

    async function getDirections() {
        const [{ Route }, { PinElement }, { Geocoder }] = await Promise.all([
            google.maps.importLibrary('routes'),
            google.maps.importLibrary('marker'),
            google.maps.importLibrary('geocoding'),
        ]);
        const geocoder = new Geocoder();
        const addresses = <?= json_encode($addresses); ?>;

        const originResult = await geocoder.geocode({ address: '770 Eastern Parkway, Brooklyn, NY 11213' });
        if (!originResult.results || originResult.results.length === 0) {
            console.error('Could not geocode 770 Eastern Parkway');
            return;
        }
        const origin770 = toLatLngLiteral(originResult.results[0].geometry.location);

        const intermediateWaypoints = [];
        for (const address of addresses) {
            try {
                const res = await geocoder.geocode({ address: address.address });
                if (res.results && res.results.length > 0) {
                    intermediateWaypoints.push(toLatLngLiteral(res.results[0].geometry.location));
                }
            } catch (e) {
                console.warn('Geocode failed for:', address.address, e);
            }
        }

        if (intermediateWaypoints.length === 0) {
            innerMap.setCenter(origin770);
            innerMap.setZoom(15);
            innerMap.setHeading(0);
            return;
        }

        // const MAX_WAYPOINTS = 25;
        // const waypoints = intermediateWaypoints.length > MAX_WAYPOINTS
        //     ? intermediateWaypoints.slice(0, MAX_WAYPOINTS)
        //     : intermediateWaypoints;
        // if (intermediateWaypoints.length > MAX_WAYPOINTS) {
        //     console.warn('Routes API allows max 25 waypoints; using first 25.');
        // }

        const request = {
            origin: origin770,
            destination: origin770,
            intermediates: intermediateWaypoints.map(loc => ({ location: loc })),
            travelMode: 'DRIVING',
            optimizeWaypointOrder: true,
            fields: ['path', 'legs', 'viewport', 'optimizedIntermediateWaypointIndices'],
        };

        let result;
        try {
            result = await Route.computeRoutes(request);
        } catch (e) {
            console.error('Route failed:', e);
            innerMap.setCenter(origin770);
            innerMap.setZoom(15);
            innerMap.setHeading(0);
            return;
        }

        if (!result.routes || result.routes.length === 0) {
            innerMap.setCenter(origin770);
            innerMap.setZoom(15);
            innerMap.setHeading(0);
            return;
        }

        const route = result.routes[0];

        function markerOptions(defaultOptions, waypointMarkerDetails) {
            const { index, totalMarkers } = waypointMarkerDetails;
            const isStart = index === 0;
            const isEnd = index === totalMarkers - 1;
            let background = 'blue';
            let glyphText = (index + 1).toString();
            if (isStart) {
                background = 'green';
                glyphText = 'S';
            } else if (isEnd) {
                background = 'red';
                glyphText = 'E';
            }
            return {
                ...defaultOptions,
                map: innerMap,
                content: new PinElement({
                    glyphText,
                    glyphColor: 'white',
                    background,
                    borderColor: background,
                }).element,
            };
        }

        await route.createWaypointAdvancedMarkers(markerOptions);
        mapPolylines = route.createPolylines();
        mapPolylines.forEach(p => p.setMap(innerMap));

        if (route.viewport) {
            innerMap.fitBounds(route.viewport);
        } else if (route.path && route.path.length > 0) {
            const { LatLngBounds } = await google.maps.importLibrary('core');
            const bounds = new LatLngBounds();
            route.path.forEach(pt => bounds.extend(pt));
            innerMap.fitBounds(bounds);
        } else {
            innerMap.setCenter(origin770);
            innerMap.setZoom(15);
            innerMap.setHeading(0);
        }
        innerMap.setHeading(0);
    }
    initMap();
  </script>
</html>