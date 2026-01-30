<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Print Duch</title>
    <link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo:ital,wght@0,100..900;1,100..900&family=Heebo:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Exo', sans-serif;
        }
        .userDuch {
            width: 7in;
            margin: 20px auto;
            page-break-after: always;
        }
        .container {
            column-count: 3;
            column-gap: 20px;
            height: auto !important;
            /* min-height: auto !important; */
            /* page-break-after: avoid !important; */
        }
        .track {
            margin-bottom: 15px;
        }
        .campaign-container, .task-container, .streak-container, .medals-container, .promotions-container, .streaks-container, .streak {
            display: flex;
            flex-direction: row;
            gap: 10px;
            break-inside: avoid; /* Don't split items */
            page-break-inside: avoid; /* Older browser support */
        }
        .container .medals-container, .container .promotions-container, .container .streaks-container {
            margin-bottom: 20px;
        }
        .task-container, .streak-container {
            margin-bottom: 5px;
        }
        .task, .medal, .promotion, .campaign-medals {
            display: flex;
            flex-direction: column;
            width: 2in;
            gap: 2px;
        }
        .campaign-name {
            font-size: 20px;
            line-height: 1;
        }
        .campaign-medals {
            font-size: 12px;
        }
        .task-short-name {
            font-size: 14px;
        }
        .task-name {
            font-size: 9px;
        }
        .campaign-icon, .task-stats {
            width: 50px;
            text-align: center;
            margin-bottom: -8px;
        }
        .task-stats {
            font-size: 9px;
            line-height: 1.2;
            direction: ltr;
        }
        .medal {
            width: 0.75in;
        }
        .promotion {
            width: 1in;
        }
        .medal-name, .promotion-name {
            font-size: 12px;
            margin-top: -10px;
            text-align: center;
        }
        .streak .campaign-icon {
            width: 75px;
        }
        .streak-text {
            text-align: center;
            font-size: 14px;
            line-height: 1;
        }
        .streak-fill progress {
            height: 30px;
            margin-left: 5px;
        }
        .streak progress {
            width: 2in;
            margin-left: 0;
        }
        .besuros-tovos {
            margin: 20px auto;
            line-height: 2.5;
            text-align: center;
        }
        footer {
            bottom: 0;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            font-family: 'Heebo', sans-serif;
            width: 80%;
            margin: auto;
            margin-top: 20px;
        }
        .pageFooter {
            padding-top: 5px;
            padding-bottom: 2px;
        }
        h3 {
            font-weight: bold;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
        @media screen {
            .no-print {
                display: block !important;
            }
        }
        button {
            padding: 5px 10px;
        }
    </style>
</head>

<body>
    <div id="spinner"></div>
    <div id="main"></div>
    <script src="/scripts/functions.js"></script>
    <script src="/jquery.js"></script>
    <script src="/scripts/js.cookie.js"></script>
    <script src="/mobile/js/spin.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // options for the loading spinner....
        var opts = {
            lines: 8, // The number of lines to draw
            length: 26, // The length of each line
            width: 12, // The line thickness
            radius: 26, // The radius of the inner circle
            scale: 0.75, // Scales overall size of the spinner
            corners: 1, // Corner roundness (0..1)
            color: '#888', // #rgb or #rrggbb or array of colors
            opacity: 0.25, // Opacity of the lines
            rotate: 0, // The rotation offset
            direction: 1, // 1: clockwise-1: counterclockwise
            speed: 1.1, // Rounds per second
            trail: 60, // Afterglow percentage
            fps: 20, // Frames per second when using setTimeout() as a fallback for CSS
            zIndex: 2e9, // The z-index (defaults to 2000000000)
            className: 'spinner', // The CSS class to assign to the spinner
            top: '50%', // Top position relative to parent
            left: '50%', // Left position relative to parent
            shadow: false, // Whether to render a shadow
            hwaccel: true, // Whether to use hardware acceleration
            position: 'absolute' // Element positioning
        };
        var target = document.getElementById('spinner');
        new Spinner(opts).spin(target);
        
        window.onload = function() {
            const email = <?= isset($_POST['email']) && $_POST['email'] ? 1 : 0 ?>;
            const fromBC = <?= isset($_POST['from_bc']) && $_POST['from_bc'] ? 1 : 0 ?>;
            const postData = <?= json_encode($_POST) ?>;
            // Pass profile parameter from URL if present
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('profile')) {
                postData.profile = 1;
            }
            let url = 'printDuch.php';
            if (Cookies.get('naftoli')) {
                url = 'printDuchAll.php';
            }
            fetch(url, {
                method: 'POST',
                body: JSON.stringify(postData),
            }).then(response => response.text()).then(html => {
                $("#spinner").empty();
                if (html === 'error') {
                    alert('Error: School ID is required');
                    return;
                }

                // break up html into pages
                const pages = html.split('|');
                $('#main').html(pages[0]);
                for (let i = 1; i < pages.length; i++) {
                    const page = pages[i];
                    $('#main').append(page);
                }

                if (email) {
                    emailToOhel();
                } else {
                    // Page-count is only an estimate (html2canvas doesn't perfectly match print layout,
                    // especially with CSS columns). We also need to wait for fonts/images so layout is final.
                    // (async function() {
                    //     const main = document.getElementById('main');
                    //     await waitForRenderReady(main);
                    //     // If you want per-soldier page estimates, use each .userDuch block:
                    //     document.querySelectorAll('.userDuch').forEach(el => checkPageCount(el));
                    //     // checkPageCount(main);
                    // })();
                    $("#print-button").show();
                    if (fromBC) {
                        $("#email-button").show();
                    }
                    // setTimeout(function() {
                    //     window.print();
                    // }, 1000);
                }
            }).catch(error => {
                $("#spinner").empty();
                alert('Error: ' + error);
            });
        };

        function emailToOhel() {
            setTimeout(async function() {
                const elem = document.getElementById('main');
                
                const filename = new Date().toISOString().replace(/[-:]/g, '') + '.pdf';
                const opt = {
                    margin:       0.5,
                    filename:      filename,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { useCORS: true, allowTaint: false, imageTimeout: 0 },
                    jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
                };
                const pdfBlob = await html2pdf().set(opt).from(elem).toPdf().output('blob');
                const formData = new FormData();
                formData.append('file', pdfBlob, filename);
                fetch('sendToOhel.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(result => {
                    $("#spinner").empty();
                    if (result.success) {
                        alert('Email sent successfully');
                    } else {
                        alert('Error: ' + (result.error || JSON.stringify(result)));
                    }
                })
                .catch(result => {
                    $("#spinner").empty();
                    alert('Error: ' + (result.error || JSON.stringify(result)));
                });
            }, 1500);
        }

        async function waitForRenderReady(rootEl) {
            // Wait for webfonts (if supported)
            try {
                if (document.fonts && document.fonts.ready) {
                    await document.fonts.ready;
                }
            } catch (e) {}

            // Wait for images inside root to load/decode so heights don't change after measuring
            try {
                const imgs = (rootEl || document).querySelectorAll ? (rootEl || document).querySelectorAll('img') : [];
                const waits = [];
                imgs.forEach(function(img) {
                    if (!img) return;
                    if (img.complete && img.naturalWidth) return;
                    if (img.decode) {
                        waits.push(img.decode().catch(function(){}));
                    } else {
                        waits.push(new Promise(function(resolve){ img.onload = resolve; img.onerror = resolve; }));
                    }
                });
                await Promise.all(waits);
            } catch (e) {}

            // Let the browser flush layout at least once
            await new Promise(function(r){ requestAnimationFrame(function(){ requestAnimationFrame(r); }); });
        }

        function checkPageCount(element) {
            // Configure PDF settings to match standard paper
            const opt = {
                margin:       0.5, // inches
                filename:     'duch.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                // NOTE: html2canvas won't perfectly match print pagination/columns.
                html2canvas:  { useCORS: true, allowTaint: false, imageTimeout: 0 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            // Generate the PDF internally but don't save it yet
            html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
                // The pdf object is an instance of jsPDF
                const totalPages = pdf.internal.getNumberOfPages();
                alert('This will be approximately ' + totalPages + ' pages.');
            }).catch(function(err) {
                console.error('checkPageCount failed', err);
                alert('Could not estimate pages. See console for details.');
            });
        }
    </script>
</body>
</html>