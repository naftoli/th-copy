import React from 'react';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
// sub pages
import Login from './Login';
import { Page404 } from 'pages/errors';

const LoginIndexPage = () => {
  return (
    <Router basename={ process.env.PUBLIC_URL }>
      <Switch>
        <Route path={`/forgot`} exact component={ Page404 }/>
        <Route path={`/signup`} exact component={ Page404 }/>
        <Route component={ Login } />
      </Switch>
    </Router>
  );
}

export default LoginIndexPage;
export { default as Logout } from './Logout';
