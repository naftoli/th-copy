import React from 'react';

export default function Header({ user, info, onLogout }) {
  const roles = info && info.roles && info.roles.length
    ? ` (${info.roles.join(' / ')})`
    : '';
  const fullName = user
    ? `${user.first_name} ${user.last_name}${roles}`
    : '';

  return (
    <header className="app-header">
      <h1>Chidon Attendance</h1>
      <span className="user-name">{fullName}</span>
      <button className="btn-logout" onClick={onLogout}>Logout</button>
    </header>
  );
}
