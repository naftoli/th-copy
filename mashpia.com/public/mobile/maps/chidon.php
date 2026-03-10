<?php
$admin_auth = ['user'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$gender = $_GET['g'] ?? 'M';

$sql = "
    SELECT 
        u.first, u.last, tc.*
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
    WHERE
        year = :year AND ultimate_trip = 1
            AND u.gender = :gender
    ORDER BY user_id
";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([':year' => $year, ':gender' => $gender]);
$rows = $stmt->fetchAll();

$addresses = [];
foreach ($rows as $row) {
    $addresses[] = [
        'address' => $row['host_street_num'] . ' ' . $row['host_street'] . ' ' . $row['host_street_apt'] . ', ' . $row['host_street_num_suffix'] . ' Brooklyn, NY',
        'child_name' => $row['first'] . ' ' . $row['last'],
        'phone_number' => $row['host_number'] ?? '',
        'email' => $row['admin_email'] ?? '',
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

        // Jewish Children's Museum, 792 Eastern Parkway, Brooklyn, NY 11213
        const MUSEUM_CENTER = { lat: 40.668889, lng: -73.941917 };

        // Points to show on the map (add your locations here)
        const MAP_POINTS = [
            { lat: 40.668889, lng: -73.941917, title: "Jewish Children's Museum", desc: "792 Eastern Parkway, Brooklyn NY" },
            // Add more: { lat: 40.67, lng: -73.94, title: "Name", desc: "Address" },
        ];

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
    <gmp-map center="40.668889,-73.941917" zoom="15" map-id="chidon-map" heading="0">
        <div id="controls" slot="control-inline-start-block-start">
            <h3>Chidon Map</h3>
        </div>
    </gmp-map>

  </body>
  <script>
    async function initMap() {
        // 1. Capture the Geocoder class from the import
        const [{ Map }, { AdvancedMarkerElement }, { Geocoder }] = await Promise.all([
            google.maps.importLibrary('maps'),
            google.maps.importLibrary('marker'),
            google.maps.importLibrary('geocoding'),
        ]);

        // 2. Create an instance of the Geocoder
        const geocoder = new Geocoder();

        const mapElement = document.querySelector('gmp-map');
        const innerMap = mapElement.innerMap;

        innerMap.setOptions({
            mapTypeControl: false,
        });

        // const marker = new AdvancedMarkerElement({
        //     map: innerMap,
        //     position: mapElement.center,
        //     title: 'Jewish Children\'s Museum',
        // });

        async function geocode(request, info) {
            // 3. Use the geocoder instance instead of google.maps.geocoding
            geocoder.geocode(request)
                .then((results) => {
                    const { results: geocodeResults } = results;
                    const marker = new AdvancedMarkerElement({
                        map: innerMap,
                        position: geocodeResults[0].geometry.location, 
                        title: info.child_name + '\n' + geocodeResults[0].formatted_address + '\nPhone Number: ' + info.phone_number + (info.email ? '\nEmail: ' + info.email : ''),
                    });
                    // Note: gmp-map handles markers automatically if 'map' is set, 
                    // but appending is fine for Advanced Markers.
                })
                .catch((e) => {
                    console.error('Geocode was not successful: ' + e);
                });
        }

        const addresses = <?= json_encode($addresses); ?>;
        for (const address of addresses) {
            geocode({ address: address.address }, address);
        }
    }
    initMap();
  </script>
</html>