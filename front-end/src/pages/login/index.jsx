import React from 'react';
import { Routes, Route } from 'react-router-dom';
// sub pages
import Login from './LoginNew'; // changed to use loginNew component
import LoginDashboard from './LoginDashboard';
import Forgot from './Forgot';
import NewAccount from './NewAccount';
// import the style sheet
import './includes/style.scss';

const LoginIndexPage = () => {
  return (
    <Routes>
      <Route path="/forgot" element={<Forgot />} />
      <Route path="/signup" element={<NewAccount />} />
      <Route path="/login" element={<Login />} />
      <Route path="*" element={<LoginDashboard />} />
    </Routes>
  );
}

export default LoginIndexPage;
export { default as Logout } from './Logout';
