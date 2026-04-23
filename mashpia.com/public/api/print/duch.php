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
        .pdf-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }
        .pdf-modal.is-open {
            display: flex;
        }
        .pdf-modal-card {
            width: min(900px, 92vw);
            background: #111;
            color: #d7ffd7;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
        }
        .pdf-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: #1a1a1a;
            color: #fff;
            font-family: 'Exo', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }
        .pdf-modal-close {
            border: 0;
            background: #2d2d2d;
            color: #fff;
            border-radius: 6px;
            padding: 4px 10px;
            cursor: pointer;
            display: none;
        }
        .pdf-modal-close.is-visible {
            display: inline-block;
        }
        .pdf-modal-log {
            max-height: 55vh;
            overflow: auto;
            padding: 14px;
            line-height: 1.4;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .pdf-line-error {
            color: #ff9696;
        }
        .pdf-line-success {
            color: #9df7a1;
        }
    </style>
</head>

<body>
    <div id="spinner"></div>
    <div id="grade-list" class="no-print" style="display: none;"></div>
    <div id="main"></div>
    <div id="pdf-modal" class="pdf-modal no-print" aria-hidden="true">
        <div class="pdf-modal-card" role="dialog" aria-modal="true" aria-labelledby="pdf-modal-title">
            <div class="pdf-modal-header">
                <span id="pdf-modal-title">Sending Duch to Ohel</span>
                <button type="button" id="pdf-modal-close" class="pdf-modal-close">Close</button>
            </div>
            <div id="pdf-modal-log" class="pdf-modal-log"></div>
        </div>
    </div>
    <script src="/scripts/functions.js"></script>
    <script src="/jquery.js"></script>
    <script src="/mobile/js/spin.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script> -->
    <script src="/pdf-maker/pdf-handler.js"></script>
    <script>
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

        const pdfModal = document.getElementById('pdf-modal');
        const pdfModalLog = document.getElementById('pdf-modal-log');
        const pdfModalClose = document.getElementById('pdf-modal-close');
        let lastProgressSignature = '';

        function ts() {
            return new Date().toLocaleTimeString();
        }

        function openPdfModal() {
            lastProgressSignature = '';
            pdfModalLog.textContent = '';
            pdfModal.classList.add('is-open');
            pdfModal.setAttribute('aria-hidden', 'false');
            pdfModalClose.classList.remove('is-visible');
        }

        function appendPdfLine(message, type) {
            const line = document.createElement('div');
            line.textContent = '[' + ts() + '] ' + message;
            if (type === 'error') line.classList.add('pdf-line-error');
            if (type === 'success') line.classList.add('pdf-line-success');
            pdfModalLog.appendChild(line);
            pdfModalLog.scrollTop = pdfModalLog.scrollHeight;
        }

        function finishPdfModal(message, ok) {
            appendPdfLine(message, ok ? 'success' : 'error');
            pdfModalClose.classList.add('is-visible');
        }

        pdfModalClose.addEventListener('click', function() {
            pdfModal.classList.remove('is-open');
            pdfModal.setAttribute('aria-hidden', 'true');
        });
        
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

            // Print Duch All: optionally split into multiple tab pages (see printDuchAll check_tabs)
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
        };

        function buildPdfPageHtml(studentNode) {
            const clone = studentNode.cloneNode(true);
            clone.style.setProperty('page-break-after', 'auto', 'important');
            clone.style.setProperty('break-after', 'auto', 'important');
            clone.querySelectorAll('img').forEach(function(img) { img.remove(); });

            const headHtml = document.head ? document.head.innerHTML : '';
            const origin = window.location.origin || 'https://mashpia.com';
            return '<!DOCTYPE html><html><head><base href="' + origin + '/">' + headHtml + '</head><body>' + clone.outerHTML + '</body></html>';
        }

        function emailToOhel() {
            setTimeout(async function() {
                try {
                    openPdfModal();
                    appendPdfLine('Preparing duch pages for PDF generation...');

                    const btns = document.getElementById('buttons');
                    if (btns) btns.remove();

                    const studentNodes = Array.from(document.querySelectorAll('.userDuch'));
                    if (!studentNodes.length) {
                        throw new Error('No student pages found to export.');
                    }

                    const pages = studentNodes.map(buildPdfPageHtml);
                    const emailAddress = 'naftoli@tzivoshashem.org';
                    const displayName = 'Ohel';
                    appendPdfLine('Queued ' + pages.length + ' page(s) for rendering.');

                    PdfHandler.generate(pages, emailAddress, displayName, {
                        onQueued: function(jobId) {
                            appendPdfLine('Job queued: ' + jobId);
                        },
                        onProgress: function(status) {
                            const signature = [status.status || '', status.progress || '', status.updatedAt || ''].join('|');
                            if (signature === lastProgressSignature) return;
                            lastProgressSignature = signature;
                            appendPdfLine((status.status || 'processing') + ': ' + (status.progress || 'Working...'));
                        },
                        onComplete: function(status) {
                            finishPdfModal(status.message || 'PDF finished and emailed successfully.', true);
                        },
                        onError: function(err) {
                            finishPdfModal('Error creating duch PDF: ' + err, false);
                        }
                    }, {
                        format: 'Letter',
                        margin: { top: '10mm', bottom: '10mm', left: '10mm', right: '10mm' }
                    });
                } catch (err) {
                    finishPdfModal('Error creating duch PDF: ' + (err && err.message ? err.message : err), false);
                }
            }, 1500);
        }
    </script>
</body>
</html>