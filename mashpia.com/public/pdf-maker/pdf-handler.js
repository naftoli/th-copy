/**
 * pdf-handler.js
 * Include this on any page that needs to trigger PDF generation.
 * Requires no dependencies.
 *
 * Usage:
 *   PdfHandler.generate(pages, email, name, {
 *     onQueued:   function(jobId) { ... },
 *     onProgress: function(status) { ... },
 *     onComplete: function(status) { ... },
 *     onError:    function(errorMsg) { ... }
 *   });
 */

var PdfHandler = (function () {

    var POLL_INTERVAL = 3000; // ms between status checks
    var MAX_POLLS     = 100;  // ~5 minutes before giving up

    /**
     * Queue a PDF generation job.
     *
     * @param {string[]} pages      - Array of HTML strings, one per PDF page
     * @param {string}   email      - Recipient email address
     * @param {string}   name       - Recipient display name
     * @param {object}   callbacks  - { onQueued, onProgress, onComplete, onError }
     * @param {object}   options    - Optional PDF options e.g. { format: 'A4' }
     */
    function generate(pages, email, name, callbacks, options) {
        callbacks = callbacks || {};
        options   = options || {};

        fetch('/pdf-maker/queue-job.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                pages:   pages,
                email:   email,
                name:    name,
                options: options
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.error) {
                if (callbacks.onError) callbacks.onError(data.error);
                return;
            }

            if (callbacks.onQueued) callbacks.onQueued(data.jobId);

            poll(data.jobId, callbacks, 0);
        })
        .catch(function(err) {
            if (callbacks.onError) callbacks.onError('Request failed: ' + err.message);
        });
    }

    function poll(jobId, callbacks, count) {
        if (count >= MAX_POLLS) {
            if (callbacks.onError) callbacks.onError('Timed out waiting for PDF. Please try again.');
            return;
        }

        setTimeout(function() {
            fetch('/pdf-maker/status.php?jobId=' + encodeURIComponent(jobId))
                .then(function(res) { return res.json(); })
                .then(function(status) {

                    if (callbacks.onProgress) callbacks.onProgress(status);

                    if (status.status === 'complete') {
                        if (callbacks.onComplete) callbacks.onComplete(status);
                        return; // stop polling
                    }

                    if (status.status === 'failed') {
                        if (callbacks.onError) callbacks.onError(status.error || 'PDF generation failed');
                        return; // stop polling
                    }

                    // Still queued or processing — keep polling
                    poll(jobId, callbacks, count + 1);
                })
                .catch(function(err) {
                    // Transient network error — keep polling, don't give up
                    console.warn('Status poll error:', err.message);
                    poll(jobId, callbacks, count + 1);
                });
        }, POLL_INTERVAL);
    }

    return { generate: generate };

})();


// ── Example usage ─────────────────────────────────────────────────────────────
//
// Include this file in your HTML:
//   <script src="/pdf-maker/pdf-handler.js"></script>
//
// Then call it like this (e.g. on a button click):
//
// document.getElementById('generate-btn').addEventListener('click', function() {
//
//   var pages = [
//     '<html><body><h1>Invoice #1</h1><p>Line items...</p></body></html>',
//     '<html><body><h1>Invoice #2</h1><p>Line items...</p></body></html>'
//   ];
//
//   PdfHandler.generate(pages, 'client@example.com', 'Client Name', {
//
//     onQueued: function(jobId) {
//       document.getElementById('pdf-status').textContent = 'Your PDF is being generated...';
//     },
//
//     onProgress: function(status) {
//       document.getElementById('pdf-status').textContent = status.progress;
//     },
//
//     onComplete: function(status) {
//       document.getElementById('pdf-status').textContent = '';
//       alert(status.message); // swap for your own notification UI
//     },
//
//     onError: function(err) {
//       document.getElementById('pdf-status').textContent = '';
//       alert('Error: ' + err); // swap for your own notification UI
//     }
//
//   });
// });