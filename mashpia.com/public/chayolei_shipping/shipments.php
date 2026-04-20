<?php

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if (($admin_user['auth'] ?? '') !== 'super') {
    header('Location: /chayolei_shipping/');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$regYear = GlobalSettings::getRegistrationYear();
$curYear = GlobalSettings::getCurrentYear();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Hachayol shipments · Chayolei shipping</title>
  <style>
    :root {
      --bg: #f4f6f8;
      --card: #fff;
      --text: #1a1d21;
      --muted: #5c6570;
      --border: #e2e6ea;
      --accent: #2563eb;
      --accent-hover: #1d4ed8;
      --danger: #dc2626;
      --radius: 10px;
      --shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
      background: var(--bg);
      color: var(--text);
      line-height: 1.5;
      padding: 2rem clamp(1rem, 4vw, 2.5rem) 3rem;
    }
    a { color: var(--accent); text-decoration: none; }
    a:hover { text-decoration: underline; }
    .wrap { max-width: 920px; margin: 0 auto; }
    h1 {
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0 0 0.5rem;
      letter-spacing: -0.02em;
    }
    .sub { color: var(--muted); font-size: 0.95rem; margin-bottom: 1.5rem; }
    .card {
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
      padding: 1.25rem 1.5rem 1.5rem;
      margin-bottom: 1rem;
    }
    .toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.75rem 1rem;
      margin-bottom: 1rem;
    }
    label { font-size: 0.875rem; font-weight: 500; color: var(--muted); }
    select {
      padding: 0.45rem 0.65rem;
      border: 1px solid var(--border);
      border-radius: 6px;
      font-size: 0.95rem;
      background: #fff;
    }
    button {
      font: inherit;
      cursor: pointer;
      border: none;
      border-radius: 6px;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      font-weight: 500;
    }
    .btn-primary { background: var(--accent); color: #fff; }
    .btn-primary:hover { background: var(--accent-hover); }
    .btn-secondary { background: #fff; color: var(--text); border: 1px solid var(--border); }
    .btn-secondary:hover { background: #f8fafc; }
    .btn-danger { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }
    .btn-danger:hover { background: #fee2e2; }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }
    th, td {
      text-align: left;
      padding: 0.65rem 0.75rem;
      border-bottom: 1px solid var(--border);
    }
    th {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--muted);
      font-weight: 600;
    }
    tbody tr:hover { background: #fafbfc; }
    input[type="number"] {
      width: 100%;
      max-width: 110px;
      padding: 0.4rem 0.5rem;
      border: 1px solid var(--border);
      border-radius: 6px;
      font-size: 0.9rem;
    }
    .row-actions { white-space: nowrap; }
    .row-actions button { padding: 0.35rem 0.65rem; font-size: 0.8rem; }
    #status {
      margin-top: 1rem;
      font-size: 0.9rem;
      min-height: 1.25rem;
    }
    #status.ok { color: #15803d; }
    #status.err { color: var(--danger); }
    .empty-hint { color: var(--muted); font-size: 0.9rem; padding: 0.5rem 0; }
  </style>
</head>
<body>
  <div class="wrap">
    <p><a href="/chayolei_shipping/">&larr; Back to shipping reports</a></p>
    <h1>Hachayol physical shipments</h1>
    <p class="sub">
      Map each shipment batch number to the range of Hachayol issue numbers it contains (e.g. shipment 3 → issues 4–60).
      The shipping report uses this for labels and line items.
    </p>

    <div class="card">
      <div class="toolbar">
        <div>
          <label for="year">Jewish year</label><br />
          <select id="year"></select>
        </div>
        <div style="margin-top:1.35rem;">
          <button type="button" class="btn-secondary" id="btnAdd">Add row</button>
          <button type="button" class="btn-primary" id="btnSave">Save</button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Shipment #</th>
            <th>First issue #</th>
            <th>Last issue #</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="tbody">
        </tbody>
      </table>
      <p class="empty-hint" id="emptyHint" style="display:none;">No rows yet — add a shipment or save an empty list to use legacy labels (shipments 1–10 without ranges).</p>
      <div id="status"></div>
    </div>
  </div>

  <script>
(function () {
  var regYear = <?= (int) $regYear ?>;
  var curYear = <?= (int) $curYear ?>;
  var yearSel = document.getElementById('year');
  for (var y = regYear; y >= 5782; y--) {
    var o = document.createElement('option');
    o.value = y;
    o.textContent = y;
    if (y === curYear) o.selected = true;
    yearSel.appendChild(o);
  }

  var tbody = document.getElementById('tbody');
  var statusEl = document.getElementById('status');
  var emptyHint = document.getElementById('emptyHint');

  function setStatus(msg, ok) {
    statusEl.textContent = msg || '';
    statusEl.className = ok === true ? 'ok' : (ok === false ? 'err' : '');
  }

  function addRow(data) {
    data = data || {};
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><input type="number" class="inp-sn" min="1" max="99" step="1" value="' +
      (data.shipment_num != null ? data.shipment_num : '') +
      '" /></td>' +
      '<td><input type="number" class="inp-is" min="1" step="1" value="' +
      (data.issue_start != null ? data.issue_start : '') +
      '" /></td>' +
      '<td><input type="number" class="inp-ie" min="1" step="1" value="' +
      (data.issue_end != null ? data.issue_end : '') +
      '" /></td>' +
      '<td class="row-actions"><button type="button" class="btn-danger btn-remove">Remove</button></td>';
    tr.querySelector('.btn-remove').addEventListener('click', function () {
      tr.remove();
      updateEmpty();
    });
    tbody.appendChild(tr);
    updateEmpty();
  }

  function updateEmpty() {
    emptyHint.style.display = tbody.children.length ? 'none' : 'block';
  }

  function gather() {
    var rows = [];
    var errs = [];
    tbody.querySelectorAll('tr').forEach(function (tr, i) {
      var sn = parseInt(tr.querySelector('.inp-sn').value, 10);
      var is = parseInt(tr.querySelector('.inp-is').value, 10);
      var ie = parseInt(tr.querySelector('.inp-ie').value, 10);
      if (!sn && !is && !ie) return;
      if (!sn || sn < 1 || sn > 99) errs.push('Row ' + (i + 1) + ': invalid shipment #');
      if (!is || !ie || is < 1 || ie < 1 || is > ie) errs.push('Row ' + (i + 1) + ': invalid issue range');
      rows.push({ shipment_num: sn, issue_start: is, issue_end: ie });
    });
    if (errs.length) throw new Error(errs[0]);
    var nums = rows.map(function (r) { return r.shipment_num; });
    var uniq = {};
    rows.forEach(function (r) {
      if (uniq[r.shipment_num]) throw new Error('Duplicate shipment # ' + r.shipment_num);
      uniq[r.shipment_num] = true;
    });
    return rows;
  }

  function load() {
    setStatus('Loading…', null);
    var y = yearSel.value;
    fetch('ajax/shipmentsApi.php?year=' + encodeURIComponent(y))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) throw new Error(data.error || 'Load failed');
        tbody.innerHTML = '';
        (data.shipments || []).forEach(function (r) {
          addRow(r);
        });
        setStatus('', null);
        updateEmpty();
      })
      .catch(function (e) {
        setStatus(e.message || 'Error', false);
      });
  }

  document.getElementById('btnAdd').addEventListener('click', function () {
    addRow({});
  });

  document.getElementById('btnSave').addEventListener('click', function () {
    try {
      var rows = gather();
      setStatus('Saving…', null);
      fetch('ajax/shipmentsApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ year: parseInt(yearSel.value, 10), shipments: rows })
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) throw new Error(data.error || 'Save failed');
          setStatus('Saved.', true);
          load();
        })
        .catch(function (e) {
          setStatus(e.message || 'Error', false);
        });
    } catch (e) {
      setStatus(e.message || 'Invalid rows', false);
    }
  });

  yearSel.addEventListener('change', load);

  load();
})();
  </script>
</body>
</html>
