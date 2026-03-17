const API_BASE = '/chidonOld/attendance/api';

async function post(endpoint, data) {
  const formData = new FormData();
  for (const [key, value] of Object.entries(data)) {
    if (Array.isArray(value)) {
      value.forEach((v) => formData.append(`${key}[]`, v));
    } else {
      formData.append(key, value);
    }
  }

  const response = await fetch(`${API_BASE}/${endpoint}`, {
    method: 'POST',
    body: formData,
  });

  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch {
    throw new Error(`Invalid JSON response from ${endpoint}`);
  }
}

export async function login(username, password) {
  return post('login.php', { username, password });
}

export async function getAdmin(loginToken) {
  return post('getAdmin.php', { login: loginToken });
}

export async function getMarkingOptions(loginToken, groups) {
  return post('getMarkingOptions.php', { login: loginToken, groups });
}

export async function getMarkingDetails(loginToken, timeId, type, groups) {
  return post('getMarkingDetails.php', {
    login: loginToken,
    time_id: timeId,
    type,
    groups,
  });
}

export async function markChild(loginToken, timeId, chidonId, checked) {
  return post('markChild.php', {
    login: loginToken,
    time_id: timeId,
    chidon_id: chidonId,
    checked: checked ? 'true' : 'false',
  });
}
