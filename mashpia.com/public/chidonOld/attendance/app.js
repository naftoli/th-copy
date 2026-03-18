const API_BASE = '/chidonOld/attendance/api';
const LOGIN_KEY = 'attendance_login';

const state = {
  token: null,
  user: null,
  info: null,
  selectedGroups: [],
  times: [],
  selectedTimeId: null,
  selectedTimeType: null,
  marks: {},
  marksType: ''
};

// ─── API ────────────────────────────────────────────────────────────────────

async function apiPost(endpoint, data) {
  const form = new FormData();
  for (const [k, v] of Object.entries(data)) {
    if (Array.isArray(v)) v.forEach(i => form.append(k + '[]', i));
    else form.append(k, v);
  }
  const res = await fetch(`${API_BASE}/${endpoint}`, { method: 'POST', body: form });
  const text = await res.text();
  return JSON.parse(text);
}

// ─── Auth ────────────────────────────────────────────────────────────────────

async function checkLogin() {
  const token = localStorage.getItem(LOGIN_KEY);
  if (!token) { showLogin(); return; }
  try {
    const result = await apiPost('getAdmin.php', { login: token });
    if (result.success) {
      state.token = token;
      state.user = result.user;
      state.info = result.info;
      showApp();
    } else {
      localStorage.removeItem(LOGIN_KEY);
      showLogin();
    }
  } catch {
    localStorage.removeItem(LOGIN_KEY);
    showLogin();
  }
}

function logout() {
  localStorage.removeItem(LOGIN_KEY);
  state.token = null;
  state.user = null;
  state.info = null;
  showLogin();
}

// ─── Pages ───────────────────────────────────────────────────────────────────

function showLogin() {
  document.getElementById('initial-loader').style.display = 'none';
  document.getElementById('login-page').style.display = '';
  document.getElementById('app-page').style.display = 'none';
  document.getElementById('login-username').value = '';
  document.getElementById('login-password').value = '';
  setLoginError('');
  setTimeout(() => document.getElementById('login-username').focus(), 50);
}

function showApp() {
  document.getElementById('initial-loader').style.display = 'none';
  document.getElementById('login-page').style.display = 'none';
  document.getElementById('app-page').style.display = '';
  const roles = state.info.roles?.length ? ` (${state.info.roles.join(' / ')})` : '';
  document.getElementById('user-name').textContent = state.user.first_name + ' ' + state.user.last_name + roles;
  showPhaseGroups();
}

function setLoginError(msg) {
  const el = document.getElementById('login-error');
  el.textContent = msg;
  el.style.display = msg ? '' : 'none';
}

// ─── Login form ──────────────────────────────────────────────────────────────

document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const username = document.getElementById('login-username').value.trim();
  const password = document.getElementById('login-password').value;
  if (!username || !password) return;

  const btn = document.getElementById('login-btn');
  btn.disabled = true;
  btn.textContent = 'Signing in…';
  setLoginError('');

  try {
    const result = await apiPost('login.php', { username, password });
    if (result.success) {
      localStorage.setItem(LOGIN_KEY, result.login);
      const adminResult = await apiPost('getAdmin.php', { login: result.login });
      if (adminResult.success) {
        state.token = result.login;
        state.user = adminResult.user;
        state.info = adminResult.info;
        showApp();
      } else {
        setLoginError('Failed to load user info.');
      }
    } else {
      setLoginError(result.error || 'Invalid username or password.');
    }
  } catch {
    setLoginError('Connection error. Please try again.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Sign In';
  }
});

document.getElementById('logout-btn').addEventListener('click', logout);

// ─── Groups phase ─────────────────────────────────────────────────────────────

function showPhaseGroups() {
  const dash = document.getElementById('dashboard');
  const assignments = state.info?.assignments || {};
  const counselors = assignments.counselors || {};
  const walkingCounselors = assignments.walking_counselors || {};

  let html = '';

  for (const [typeName, groups] of Object.entries(assignments)) {
    if (typeName === 'counselors' || typeName === 'walking_counselors') continue;
    const isBunk = typeName === 'bunk';
    const counselorMap = isBunk ? counselors : walkingCounselors;
    const label = isBunk ? 'Bunks' : 'Walking Groups';
    const groupValues = Object.values(groups).filter(Boolean);

    html += `
      <div class="section-card">
        <h3>${label} <button class="toggle-all-btn" data-type="${typeName}">Toggle All</button></h3>
        <div class="groups-grid" id="groups-grid-${typeName}">
          ${groupValues.map(g => {
            const cn = counselorMap[g]?.join(', ') || '';
            return `<label class="group-checkbox-label" data-group="${g}">
              <input type="checkbox" value="${g}" class="group-cb" />
              <span>${g}</span>
              ${cn ? `<span class="group-counselor">(${cn})</span>` : ''}
            </label>`;
          }).join('')}
        </div>
      </div>`;
  }

  html += `
    <div class="section-card" style="text-align:center">
      <button class="go-button" id="go-btn" disabled>Go to Marking (0 groups selected)</button>
    </div>`;

  dash.innerHTML = html;

  dash.querySelectorAll('.group-cb').forEach(cb => {
    cb.addEventListener('change', () => {
      cb.closest('.group-checkbox-label').classList.toggle('checked', cb.checked);
      updateGoButton(dash);
    });
  });

  dash.querySelectorAll('.toggle-all-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const cbs = dash.querySelectorAll(`#groups-grid-${btn.dataset.type} .group-cb`);
      const allChecked = Array.from(cbs).every(c => c.checked);
      cbs.forEach(c => {
        c.checked = !allChecked;
        c.closest('.group-checkbox-label').classList.toggle('checked', !allChecked);
      });
      updateGoButton(dash);
    });
  });

  document.getElementById('go-btn').addEventListener('click', async () => {
    const groups = Array.from(dash.querySelectorAll('.group-cb:checked')).map(c => c.value);
    await goToMarking(groups);
  });
}

function updateGoButton(dash) {
  const count = dash.querySelectorAll('.group-cb:checked').length;
  const btn = document.getElementById('go-btn');
  btn.disabled = count === 0;
  btn.textContent = `Go to Marking (${count} group${count !== 1 ? 's' : ''} selected)`;
}

// ─── Marking phase ────────────────────────────────────────────────────────────

async function goToMarking(groups) {
  state.selectedGroups = groups;
  const dash = document.getElementById('dashboard');

  dash.innerHTML = `
    <div class="section-card back-bar">
      <button class="toggle-all-btn" id="back-btn">← Back to Groups</button>
      <span style="color:#636e72;font-size:0.9rem">Groups: ${groups.join(', ')}</span>
    </div>
    <div class="loading-spinner"><div class="spinner"></div> Loading times…</div>`;

  document.getElementById('back-btn').addEventListener('click', showPhaseGroups);

  let times;
  try {
    const result = await apiPost('getMarkingOptions.php', { login: state.token, groups });
    if (!result.success) { alert(result.error || 'Failed to load times.'); showPhaseGroups(); return; }
    times = result.times || [];
  } catch {
    alert('Connection error loading times.');
    showPhaseGroups();
    return;
  }

  state.times = times;

  const backBar = `
    <div class="section-card back-bar">
      <button class="toggle-all-btn" id="back-btn">← Back to Groups</button>
      <span style="color:#636e72;font-size:0.9rem">Groups: ${groups.join(', ')}</span>
    </div>`;

  let timesHtml;
  if (times.length === 0) {
    timesHtml = `<div class="section-card"><div class="empty-state">No attendance times available for these groups.</div></div>`;
  } else {
    const options = times.map(t =>
      `<option value="${t.key}" data-type="${t.type}">${t.time} : ${t.type} — ${t.description}</option>`
    ).join('');
    timesHtml = `
      <div class="section-card">
        <h3>Attendance Time</h3>
        <select class="time-select" id="time-select">${options}</select>
      </div>`;
  }

  dash.innerHTML = backBar + timesHtml + `<div id="children-area"></div>`;

  document.getElementById('back-btn').addEventListener('click', showPhaseGroups);

  if (times.length > 0) {
    const sel = document.getElementById('time-select');
    sel.addEventListener('change', () => {
      const opt = sel.options[sel.selectedIndex];
      loadChildren(sel.value, opt.dataset.type);
    });
    loadChildren(times[0].key, times[0].type);
  }
}

async function loadChildren(timeId, type) {
  state.selectedTimeId = timeId;
  state.selectedTimeType = type;
  const area = document.getElementById('children-area');
  area.innerHTML = `<div class="loading-spinner"><div class="spinner"></div> Loading students…</div>`;

  try {
    const result = await apiPost('getMarkingDetails.php', {
      login: state.token,
      time_id: timeId,
      type,
      groups: state.selectedGroups
    });
    if (!result.success) {
      area.innerHTML = `<div class="section-card"><div class="error-message">${result.error || 'Failed to load students.'}</div></div>`;
      return;
    }
    state.marks = result.marks || {};
    state.marksType = result.type || type;
    renderChildren(area);
  } catch {
    area.innerHTML = `<div class="section-card"><div class="error-message">Connection error loading students.</div></div>`;
  }
}

// ─── Child list ───────────────────────────────────────────────────────────────

function renderChildren(container) {
  const marks = state.marks;
  const type = state.marksType;
  const groupNumbers = Object.keys(marks).sort((a, b) => Number(a) - Number(b));

  if (groupNumbers.length === 0) {
    container.innerHTML = `<div class="section-card"><div class="empty-state">No students found for the selected groups and time.</div></div>`;
    return;
  }

  let html = '';
  for (const gNum of groupNumbers) {
    const children = marks[gNum] || [];
    const markedCount = children.filter(c => c.marked).length;
    html += `
      <div class="section-card group-section">
        <div class="group-section-header">
          <div class="group-section-title">
            ${type} ${gNum}
            <span style="font-size:0.8rem;font-weight:400;color:#636e72;margin-left:8px">(${markedCount}/${children.length} marked)</span>
          </div>
          <button class="check-all-btn" data-group="${gNum}">Check / Uncheck All</button>
        </div>
        <div class="children-grid">
          ${children.map(child => renderChildItem(child, type)).join('')}
        </div>
      </div>`;
  }

  container.innerHTML = html;

  container.querySelectorAll('.child-item').forEach(el => {
    el.addEventListener('click', () => toggleChild(el));
    el.addEventListener('keydown', e => {
      if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); toggleChild(el); }
    });
  });

  container.querySelectorAll('.check-all-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const groupEl = btn.closest('.group-section');
      const items = groupEl.querySelectorAll('.child-item');
      const allMarked = Array.from(items).every(el => el.classList.contains('marked'));
      items.forEach(el => markChildEl(el, !allMarked, true));
    });
  });
}

function renderChildItem(child, type) {
  const marked = !!child.marked;
  let extra = '';

  if (type === 'walk') {
    extra += `<div class="child-host-info">`;
    if (child.host) extra += `<div>Host Family: ${child.host}</div>`;
    const addr = [child.host_street_num, child.host_street_num_suffix, child.host_street, child.host_street_apt]
      .filter(Boolean).join(' ');
    if (addr) extra += `<div class="child-host-address">${addr}</div>`;
    if (child.between_streets1 || child.between_streets2)
      extra += `<div>Between ${child.between_streets1} and ${child.between_streets2}</div>`;
    if (child.host_number) extra += `<div>Host Phone: ${child.host_number}</div>`;
    extra += `</div>`;
  }

  if (type === 'door') {
    extra += `<div class="child-host-info">`;
    if (child.host) extra += `<div>Host Family: ${child.host}${child.host_number ? ` — ${child.host_number}` : ''}</div>`;
    if (child.chap_name) extra += `<div>Walking Chaperone: ${child.chap_name}${child.chap_phone ? ` — ${child.chap_phone}` : ''}</div>`;
    extra += `</div>`;
  }

  return `
    <div class="child-item${marked ? ' marked' : ''}"
         data-id="${child.th_chidon_id}"
         role="checkbox" aria-checked="${marked}" tabindex="0">
      <div class="child-check-icon">${marked ? '<i class="fa fa-check"></i>' : ''}</div>
      <div class="child-info">
        <div class="child-name">${child.first} ${child.last}</div>
        <div class="child-detail">School: ${child.school_name}${
          type === 'walk' && child.dropoff_number != null
            ? '<br>Dropoff #: ' + child.dropoff_number : ''
        }</div>
        ${extra}
      </div>
    </div>`;
}

function toggleChild(el) {
  markChildEl(el, !el.classList.contains('marked'), true);
}

function markChildEl(el, marked, sendToServer) {
  el.classList.toggle('marked', marked);
  el.setAttribute('aria-checked', marked);
  el.querySelector('.child-check-icon').innerHTML = marked ? '<i class="fa fa-check"></i>' : '';

  // Update marked count in group header
  const groupEl = el.closest('.group-section');
  if (groupEl) {
    const items = groupEl.querySelectorAll('.child-item');
    const count = Array.from(items).filter(i => i.classList.contains('marked')).length;
    const span = groupEl.querySelector('.group-section-title span');
    if (span) span.textContent = `(${count}/${items.length} marked)`;
  }

  if (sendToServer) {
    apiPost('markChild.php', {
      login: state.token,
      time_id: state.selectedTimeId,
      chidon_id: el.dataset.id,
      checked: marked ? 'true' : 'false'
    }).then(result => {
      if (!result.success) markChildEl(el, !marked, false);
    }).catch(() => {
      markChildEl(el, !marked, false);
    });
  }
}

// ─── Init ─────────────────────────────────────────────────────────────────────

checkLogin();
