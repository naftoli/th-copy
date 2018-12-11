import React from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import Login from './Login';
import Forgot from './Forgot';
import NewAccount from './NewAccount';
// import the style sheet
import './includes/style.scss';

const LoginIndexPage = () => {
  return (
    <Switch>
      <Route path={`/forgot`} exact component={ Forgot }/>
      <Route path={`/signup`} exact component={ NewAccount }/>
      <Route component={ Login } />
    </Switch>
  );
}

export default LoginIndexPage;
export { default as Logout } from './Logout';
