<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '3072M');

require_once( '../header/header.php' );

if ( !isset( $_POST['school_id'] ) ) {
    header('Location: /new/missions/print' ); die();
}

// When user selected specific platoons (class_ids in form), render directly via printMissionsAll
$has_class_ids = ! empty( $_POST['class_ids'] ) && ( is_array( $_POST['class_ids'] ) ? count( $_POST['class_ids'] ) > 0 : strlen( trim( $_POST['class_ids'] ) ) > 0 );
if ( $has_class_ids ) {
    require_once __DIR__ . '/printMissionsAll.php';
    exit;
}

// Whole school: show launcher that checks 300+ and opens grade tabs or loads full content
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Print Missions</title>
    <link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo:ital,wght@0,100..900;1,100..900&family=Heebo:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Exo', sans-serif; }
        #spinner {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .no-print { display: block !important; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div id="spinner"></div>
    <div id="grade-list" class="no-print" style="display: none;"></div>
    <div id="main"></div>
    <script src="/scripts/functions.js"></script>
    <script src="/jquery.js"></script>
    <script src="/mobile/js/spin.js"></script>
    <script>
        // options for the loading spinner (same as duch.php)
        var opts = {
            lines: 8,
            length: 26,
            width: 12,
            radius: 26,
            scale: 0.75,
            corners: 1,
            color: '#888',
            opacity: 0.25,
            rotate: 0,
            direction: 1,
            speed: 1.1,
            trail: 60,
            fps: 20,
            zIndex: 2e9,
            className: 'spinner',
            top: '50%',
            left: '50%',
            shadow: false,
            hwaccel: true,
            position: 'absolute'
        };
        var target = document.getElementById('spinner');
        new Spinner(opts).spin(target);

        window.onload = function() {
            const postData = <?= json_encode( $_POST ) ?>;
            let url = 'printMissionsAll.php';

            function showContent(html) {
                $("#spinner").empty();
                if (html === 'error') {
                    alert('Error: School ID is required');
                    return;
                }
                $('#main').html(html);
            }

            function buildFormInputs(data, classIds) {
                var html = '';
                var payload = Object.assign({}, data, { class_ids: classIds || [] });
                delete payload.check_tabs;
                for (var key in payload) {
                    if (!payload.hasOwnProperty(key)) continue;
                    var val = payload[key];
                    if (Array.isArray(val)) {
                        for (var i = 0; i < val.length; i++) {
                            html += '<input type="hidden" name="' + key + '[]" value="' + (val[i] != null ? String(val[i]).replace(/"/g, '&quot;') : '') + '">';
                        }
                    } else {
                        html += '<input type="hidden" name="' + key + '" value="' + (val != null ? String(val).replace(/"/g, '&quot;') : '') + '">';
                    }
                }
                return html;
            }

            function openGradeInNewPage(gradeData, delayMs) {
                setTimeout(function() {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'missions.php';
                    form.target = '_blank';
                    form.style.display = 'none';
                    form.innerHTML = buildFormInputs(postData, gradeData.class_ids || []);
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }, delayMs);
            }

            var checkData = Object.assign({}, postData, { check_tabs: 1 });
            fetch(url, { method: 'POST', body: JSON.stringify(checkData) })
                .then(function(r) { return r.text(); })
                .then(function(text) {
                    var json = null;
                    try { json = JSON.parse(text); } catch (e) {}
                    if (json && json.useTabs && json.grades && json.grades.length > 0) {
                        $("#spinner").empty();
                        var listEl = document.getElementById('grade-list');
                        listEl.style.display = 'block';
                        listEl.innerHTML = '<p>Opening ' + json.grades.length + ' grade pages…</p>';
                        json.grades.forEach(function(g, i) {
                            openGradeInNewPage(g, i * 1000);
                        });
                        var lastOpenMs = (json.grades.length - 1) * 1000;
                        setTimeout(function() {
                            listEl.innerHTML = '<p>Opened ' + json.grades.length + ' grade pages. Closing…</p>';
                            window.close();
                        }, lastOpenMs + 1000);
                        return;
                    }
                    var fullData = Object.assign({}, postData);
                    delete fullData.check_tabs;
                    fetch(url, { method: 'POST', body: JSON.stringify(fullData) })
                        .then(function(r) { return r.text(); })
                        .then(showContent)
                        .catch(function(err) {
                            $("#spinner").empty();
                            alert('Error: ' + err);
                        });
                })
                .catch(function(err) {
                    $("#spinner").empty();
                    alert('Error: ' + err);
                });
        };
    </script>
</body>
</html>
