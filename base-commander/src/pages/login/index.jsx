import React from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import Login from './LoginNew'; // changed to use loginNew component
import LoginDashboard from './LoginDashboard';
import Forgot from './Forgot';
import NewAccount from './NewAccount';
// import the style sheet
import './includes/style.scss';

const LoginIndexPage = () => {
  return (
    <Switch>
      <Route path={`/forgot`} exact component={ Forgot }/>
      <Route path={`/signup`} exact component={ NewAccount }/>
      <Route path={`/login`} exact component={ Login }/>
      <Route component={ LoginDashboard } />
    </Switch>
  );
}

export default LoginIndexPage;
export { default as Logout } from './Logout';
