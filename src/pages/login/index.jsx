import React from 'react';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
// sub pages
import Login from './Login';
import Forgot from './Forgot';
import { Construction } from 'pages/errors';

import './includes/style.scss';

const LoginIndexPage = () => {
  return (
    <Router basename={ process.env.PUBLIC_URL }>
      <Switch>
        <Route path={`/forgot`} exact component={ Forgot }/>
        <Route path={`/signup`} exact component={ Construction }/>
        <Route component={ Login } />
      </Switch>
    </Router>
  );
}

export default LoginIndexPage;
export { default as Logout } from './Logout';
