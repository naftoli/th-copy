import React, { useState, useEffect } from 'react';
import { useSelector } from 'react-redux';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
// screens
import { Page404 } from 'pages/errors';
import Login, { Logout } from 'pages/login';
// pages
import Account from 'pages/account';
import Rewards from 'pages/rewards';
import Missions from 'pages/missions';
import HomePage from 'pages/home/HomePage';
import BaseManagment from 'pages/base-managment';
import RegistrationPage from 'pages/base-managment/base/RegisterBasePage';
// components
import { Dashboard } from 'components/navigation';
import { LoadingScreen } from 'components/ui';
// import ConfirmationModal from 'components/modals/ConfirmationModal'; // TODO: Implement custom blocker for v6
// functions
import { loginStoreChanged } from 'functions/login';
import { validateLogin } from 'store/login/operations';
import ReportCards from 'pages/reportCards/ReportCards';

export default function App() {
  console.log('App.jsx: Rendering App component');
  const { current_login: login, current_user, title } = useSelector(state => state.login || {});
  // Helper to safely check login status
  const logged_in = login && Object.keys(login).length > 0 && !!current_user;

  const [refreshing, setRefreshing] = useState(true);
  const [modalState, setModalState] = useState({
    message: '',
    isOpen: false,
  });

  // Validate login interval
  useEffect(() => {
    const interval = setInterval(validateLogin, 5000);
    return () => clearInterval(interval);
  }, []);

  // Handle login changes
  useEffect(() => {
    const hadLogin = login && Object.keys(login).length > 0;
    // Set refreshing to true initially if we have a login, or false if we don't
    setRefreshing(hadLogin);

    // If we have a login, wait 500ms then stop refreshing to show content
    if (hadLogin) {
      const timeout = setTimeout(() => setRefreshing(false), 500);
      return () => clearTimeout(timeout);
    }
  }, [login]);

  const dashboardProps = { title, login, current_user };

  // Define routes based on auth state
  let routes = [
    { path: "/", element: <HomePage /> },
    { path: "/myaccount", element: <Account /> },
    { path: "/logout", element: <Logout /> },
    { path: "*", element: <RegistrationPage /> } // Fallback for public ?? Original was just { component: Reg } as last item
  ];

  if (login && login.active) {
    routes = [
      { path: "/", element: <HomePage /> },
      { path: "/login/*", element: <Login /> }, // Maybe redirect if logged in?
      { path: "/rewards/*", element: <Rewards /> }, // Star for nested routes
      { path: "/bm/*", element: <BaseManagment /> },
      { path: "/missions/*", element: <Missions /> },
      { path: "/myaccount", element: <Account /> },
      { path: "/logout", element: <Logout /> },
      { path: "/register", element: <RegistrationPage /> },
      { path: "/chidonTests/reportCards", element: <ReportCards /> },
      { path: "*", element: <Page404 /> }
    ];
  }

  // NOTE: getUserConfirmation replacements are complex in v6 and Require unstable_useBlocker or similar.
  // Skipping custom confirmation modal for now.

  return (
    <Router basename={import.meta.env.BASE_URL}>
      {!logged_in && <Login />}

      {logged_in && (
        <Dashboard {...dashboardProps}>
          {refreshing ? (
            <LoadingScreen />
          ) : (
            <Routes>
              {routes.map((route, index) => (
                <Route
                  key={index}
                  path={route.path}
                  element={route.element}
                />
              ))}
            </Routes>
          )}

          <ToastContainer
            position="bottom-right"
            autoClose={5000}
            closeOnClick={false}
            draggablePercent={40}
          />

          {/* 
          <ConfirmationModal
            isOpen={modalState.isOpen}
            message={modalState.message}
            callback={handleCallback} 
          /> 
          */}
        </Dashboard>
      )}
    </Router>
  );
}
