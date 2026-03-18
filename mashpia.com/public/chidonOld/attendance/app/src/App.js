import React, { useState, useEffect, useCallback } from 'react';
import * as api from './api';
import Login from './components/Login';
import Header from './components/Header';
import GroupSelection from './components/GroupSelection';
import MarkingPhase from './components/MarkingPhase';

const LOGIN_KEY = 'attendance_login';

export default function App() {
  // 'loading' | 'login' | 'groups' | 'marking'
  const [phase, setPhase] = useState('loading');
  const [token, setToken] = useState(null);
  const [user, setUser] = useState(null);
  const [info, setInfo] = useState(null);
  const [selectedGroups, setSelectedGroups] = useState([]);

  useEffect(() => {
    const storedToken = localStorage.getItem(LOGIN_KEY);
    if (!storedToken) {
      setPhase('login');
      return;
    }
    api.getAdmin(storedToken)
      .then(result => {
        if (result.success) {
          setToken(storedToken);
          setUser(result.user);
          setInfo(result.info);
          setPhase('groups');
        } else {
          localStorage.removeItem(LOGIN_KEY);
          setPhase('login');
        }
      })
      .catch(() => {
        localStorage.removeItem(LOGIN_KEY);
        setPhase('login');
      });
  }, []);

  const handleLogin = useCallback(async (username, password) => {
    const result = await api.login(username, password);
    if (!result.success) {
      throw new Error(result.error || 'Invalid username or password.');
    }
    const adminResult = await api.getAdmin(result.login);
    if (!adminResult.success) {
      throw new Error('Failed to load user info.');
    }
    localStorage.setItem(LOGIN_KEY, result.login);
    setToken(result.login);
    setUser(adminResult.user);
    setInfo(adminResult.info);
    setPhase('groups');
  }, []);

  const handleLogout = useCallback(() => {
    localStorage.removeItem(LOGIN_KEY);
    setToken(null);
    setUser(null);
    setInfo(null);
    setSelectedGroups([]);
    setPhase('login');
  }, []);

  const handleGoToMarking = useCallback((groups) => {
    setSelectedGroups(groups);
    setPhase('marking');
  }, []);

  const handleBackToGroups = useCallback(() => {
    setPhase('groups');
  }, []);

  if (phase === 'loading') {
    return (
      <div className="initial-loader">
        <div className="spinner" />
      </div>
    );
  }

  if (phase === 'login') {
    return <Login onLogin={handleLogin} />;
  }

  return (
    <div className="app">
      <Header user={user} info={info} onLogout={handleLogout} />
      <main className="dashboard">
        {phase === 'groups' && (
          <GroupSelection info={info} onGoToMarking={handleGoToMarking} />
        )}
        {phase === 'marking' && (
          <MarkingPhase
            token={token}
            groups={selectedGroups}
            onBack={handleBackToGroups}
          />
        )}
      </main>
    </div>
  );
}
