const API_BASE = '/chidonOld/attendance/api';

async function apiPost(endpoint, data) {
  const form = new FormData();
  for (const [k, v] of Object.entries(data)) {
    if (Array.isArray(v)) v.forEach(item => form.append(k + '[]', item));
    else form.append(k, v);
  }
  const res = await fetch(`${API_BASE}/${endpoint}`, { method: 'POST', body: form });
  const text = await res.text();
  return JSON.parse(text);
}

export const login = (username, password) =>
  apiPost('login.php', { username, password });

export const getAdmin = (token) =>
  apiPost('getAdmin.php', { login: token });

export const getMarkingOptions = (token, groups) =>
  apiPost('getMarkingOptions.php', { login: token, groups });

export const getMarkingDetails = (token, timeId, type, groups) =>
  apiPost('getMarkingDetails.php', { login: token, time_id: timeId, type, groups });

export const markChild = (token, timeId, chidonId, checked) =>
  apiPost('markChild.php', {
    login: token,
    time_id: timeId,
    chidon_id: chidonId,
    checked: checked ? 'true' : 'false',
  });
