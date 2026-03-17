import { useState, useEffect, useCallback } from 'react';
import { getAdmin } from './api/api.js';
import LoginPage from './pages/LoginPage.jsx';
import DashboardPage from './pages/DashboardPage.jsx';

const LOGIN_STORAGE_KEY = 'attendance_login';

export default function App() {
  const [loginToken, setLoginToken] = useState(null);
  const [user, setUser] = useState(null);
  const [info, setInfo] = useState(null);
  const [authChecked, setAuthChecked] = useState(false);

  const logout = useCallback(() => {
    localStorage.removeItem(LOGIN_STORAGE_KEY);
    setLoginToken(null);
    setUser(null);
    setInfo(null);
  }, []);

  const handleLoginSuccess = useCallback(async (token) => {
    localStorage.setItem(LOGIN_STORAGE_KEY, token);
    setLoginToken(token);
    const result = await getAdmin(token);
    if (result.success) {
      setUser(result.user);
      setInfo(result.info);
    } else {
      logout();
    }
  }, [logout]);

  // Check for existing login on mount
  useEffect(() => {
    const stored = localStorage.getItem(LOGIN_STORAGE_KEY);
    if (!stored) {
      setAuthChecked(true);
      return;
    }

    getAdmin(stored)
      .then((result) => {
        if (result.success) {
          setLoginToken(stored);
          setUser(result.user);
          setInfo(result.info);
        } else {
          localStorage.removeItem(LOGIN_STORAGE_KEY);
        }
      })
      .catch(() => {
        localStorage.removeItem(LOGIN_STORAGE_KEY);
      })
      .finally(() => setAuthChecked(true));
  }, []);

  if (!authChecked) {
    return (
      <div className="loading-spinner" style={{ minHeight: '100vh' }}>
        <div className="spinner" />
        Loading...
      </div>
    );
  }

  if (!loginToken || !user) {
    return <LoginPage onLoginSuccess={handleLoginSuccess} />;
  }

  return (
    <DashboardPage
      loginToken={loginToken}
      user={user}
      info={info}
      onLogout={logout}
    />
  );
}
