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
        a.admin_phone_work,
        a.admin_phone_mobile,
        a.admin_phone_mobile2,
        a.admin_phone_home,
        a.admin_email,
        tc.*
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        admin_auths aa ON aa.id = u.user_id
            JOIN
        admins a USING (admin_id)
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
        'child_name' => $row['first'] . ' ' . $row['last'],
        'phone_numbers' => [],
        'email' => $row['admin_email'] ?? '',
    ];
    if ($row['admin_phone_mobile']) $addresses[$i]['phone_numbers'][] = $row['admin_phone_mobile'];
    if ($row['admin_phone_mobile2']) $addresses[$i]['phone_numbers'][] = $row['admin_phone_mobile2'];
    if ($row['admin_phone_work']) $addresses[$i]['phone_numbers'][] = $row['admin_phone_work'];
    if ($row['admin_phone_home']) $addresses[$i]['phone_numbers'][] = $row['admin_phone_home'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Chidon Map</title>
    <!-- prettier-ignore -->
    <script>
        const GOOGLE_MAPS_API_KEY = 'AIzaSyBegHlUZbSVqYfm3Pp76PSHzcYfEKG9ieo';

        // 770 Eastern Parkway, Brooklyn, NY 11213
        const MAP_CENTER = { lat: 40.668975, lng: -73.942824 };

        // Points to show on the map (add your locations here)
        // const MAP_POINTS = [
        //     { lat: 40.660111, lng: -73.965278, title: "770 Eastern Parkway, Brooklyn NY" },
        //     // Add more: { lat: 40.67, lng: -73.94, title: "Name", desc: "Address" },
        // ];

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
            <h3>Chidon Map</h3>
        </div>
    </gmp-map>

  </body>
  <script>
    async function initMap() {
        // 1. Capture the Geocoder class from the import
        const [{ Map, InfoWindow }, { AdvancedMarkerElement, PinElement }, { Geocoder }] = await Promise.all([
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
            center: MAP_CENTER,
            zoom: 15,
        });

        const marker = new AdvancedMarkerElement({
            position: mapElement.center,
            title: '770 Eastern Parkway Brooklyn, NY 11213',
            gmpClickable: true,
        });
        const pin = new PinElement({
            scale: 1.5,
            glyphColor: 'white',
        })
        marker.appendChild(pin);
        mapElement.append(marker);

        async function geocode(request, info) {
            // 3. Use the geocoder instance instead of google.maps.geocoding
            geocoder.geocode(request)
                .then((results) => {
                    const { results: geocodeResults } = results;
                    const marker = new AdvancedMarkerElement({
                        position: geocodeResults[0].geometry.location, 
                        title: info.child_name + '\n' + geocodeResults[0].formatted_address + '\nParent Phone Numbers:\n' + info.phone_numbers.join("\n") + (info.email ? '\nParent Email: ' + info.email : ''),
                        gmpClickable: true,
                    });
                    // Note: gmp-map handles markers automatically if 'map' is set, 
                    // but appending is fine for Advanced Markers.

                    const pin = new PinElement({ 
                        scale: 1.0,
                        glyphColor: 'white',
                    })
                    marker.appendChild(pin);
                    mapElement.append(marker);

                    // Add a click listener for each marker, and set up the info window.
                    const infoWindow = new InfoWindow({
                        content: info.child_name + "<br />" + geocodeResults[0].formatted_address + "<br />Parent Phone Numbers:<br />" + info.phone_numbers.join("<br />") + (info.email ? "<br />Parent Email: " + info.email : ''),
                        position: marker.position,
                    });
                    // Add a click listener for each marker, and set up the info window.
                    marker.addListener('click', ({ domEvent, latLng }) => {
                        infoWindow.open(marker.map, marker);
                    });
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