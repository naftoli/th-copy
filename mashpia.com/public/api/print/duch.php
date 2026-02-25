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

    <script src="https://docraptor.com/docraptor-1.0.0.js"></script>
</head>

<body>
    <div id="spinner"></div>
    <div id="grade-list" class="no-print" style="display: none;"></div>
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
            let url = 'printDuchAll.php';

            function showContent(html) {
                if (html === 'error') {
                    $("#spinner").empty();
                    alert('Error: School ID is required');
                    return;
                }
                $('#main').html(html);
                $("#spinner").empty();
                if (email) {
                    emailToOhel();
                } else {
                    $("#print-button").show();
                    if (fromBC) {
                        $("#email-button").show();
                    }
                    // Wait for fonts, images, and layout before auto-printing
                    waitForRenderReady(document.getElementById('main')).then(function() {
                        // const children = document.querySelectorAll('.userDuch');
                        // children.forEach(function(child) {
                        //     const totalPages = checkPageCount(child);
                        //     if (totalPages % 2 !== 0) {
                        //         // add a blank page
                        //         child.insertAdjacentHTML('beforeend',
                        //             '<div style="page-break-after: always;"></div>'
                        //         );
                        //     }
                        // });
                        // window.print();
                        // this key works in test mode!
                        DocRaptor.createAndDownloadDoc("YOUR_API_KEY_HERE", {
                            name: "html-and-javascript",
                            test: true, // test documents are free but watermarked
                            document_type: "pdf",
                            document_content: document.getElementById('main').innerHTML,
                            // document_url: "https://docraptor.com/examples/invoice.html",
                            javascript: true
                            // prince_options: {
                            //   media: "print", // @media 'screen' or 'print' CSS
                            //   baseurl: "https://yoursite.com", // the base URL for any relative URLs
                            // }
                        });
                    });
                }
            }

            function buildFormInputs(data, overrides) {
                var html = '';
                var payload = Object.assign({}, data, overrides || {});
                delete payload.check_tabs;
                for (var key in payload) {
                    if (!payload.hasOwnProperty(key)) continue;
                    var val = payload[key];
                    if (key === 'user_ids' && Array.isArray(val)) {
                        html += '<input type="hidden" name="user_ids" value="' + (val.join(',') || '').replace(/"/g, '&quot;') + '">';
                    } else if (Array.isArray(val)) {
                        for (var i = 0; i < val.length; i++) {
                            html += '<input type="hidden" name="' + key + '[]" value="' + (val[i] != null ? String(val[i]).replace(/"/g, '&quot;') : '') + '">';
                        }
                    } else {
                        html += '<input type="hidden" name="' + key + '" value="' + (val != null ? String(val).replace(/"/g, '&quot;') : '') + '">';
                    }
                }
                return html;
            }

            function openTabInNewPage(tabData, delayMs) {
                setTimeout(function() {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'duch.php';
                    form.target = '_blank';
                    form.style.display = 'none';
                    var overrides = { class_ids: tabData.class_ids || [] };
                    form.innerHTML = buildFormInputs(postData, overrides);
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }, delayMs);
            }

            // Tab- or grade-specific page: we were opened with class_ids (from auto-open). Load content for that selection only.
            var hasClassIds = postData.class_ids && (Array.isArray(postData.class_ids) ? postData.class_ids.length : (postData.class_ids + '').split(',').filter(Boolean).length);
            if (hasClassIds) {
                fetch(url, { method: 'POST', body: JSON.stringify(postData) })
                    .then(function(r) { return r.text(); })
                    .then(showContent)
                    .catch(function(err) {
                        $("#spinner").empty();
                        alert('Error: ' + err);
                    });
                return;
            }

            // Print Duch All: check if we need to auto-open tab pages (more than 10 classes)
                var checkData = Object.assign({}, postData, { check_tabs: 1 });
                fetch(url, { method: 'POST', body: JSON.stringify(checkData) })
                    .then(function(r) { return r.text(); })
                    .then(function(text) {
                        var json = null;
                        try { json = JSON.parse(text); } catch (e) {}
                        if (json && json.useTabs && json.tabs && json.tabs.length > 0) {
                            $("#spinner").empty();
                            var listEl = document.getElementById('grade-list');
                            listEl.style.display = 'block';
                            listEl.innerHTML = '<p>Opening ' + json.tabs.length + ' tab pages…</p>';
                            json.tabs.forEach(function(t, i) {
                                openTabInNewPage(t, i * 1000);
                            });
                            var lastOpenMs = (json.tabs.length - 1) * 1000;
                            setTimeout(function() {
                                listEl.innerHTML = '<p>Opened ' + json.tabs.length + ' tab pages. Closing…</p>';
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

            // fetch(url, { method: 'POST', body: JSON.stringify(postData) })
            //     .then(function(r) { return r.text(); })
            //     .then(showContent)
            //     .catch(function(err) {
            //         $("#spinner").empty();
            //         alert('Error: ' + err);
            //     });
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

        async function checkPageCount(element) {
            // Configure PDF settings to match standard paper
            const opt = {
                    margin:       0.5,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { useCORS: true, allowTaint: false, imageTimeout: 0 },
                    jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
                };

            // Generate the PDF internally but don't save it yet
            const pdf = await html2pdf().set(opt).from(element).toPdf().get('pdf');
            // The pdf object is an instance of jsPDF
            const totalPages = pdf.internal.getNumberOfPages();
            console.log('Total pages: ' + totalPages);
            return totalPages;
        }
    </script>
</body>
</html>