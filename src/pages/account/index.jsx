import React from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import AccountPage from './AccountPage';
// import BasesPage from './BasesPage';
import { Page404 } from 'pages/errors';

const AccountIndexPage = props => {
  const { path } = props.match;

  return (
    <Switch>
      <Route path={ path } exact component={ AccountPage } />
      
      <Route path={`${path}/login`} exact render={ () => <h1>Login Settings</h1> } />
      
      <Route path={`${path}/payments`} exact render={ () => <h1>Payment Settings</h1> } />
      
      <Route path={`${path}/info`} exact render={ () => <h1>Account Info Settings</h1> } />
      {/* 404 page */}
      <Route component={ Page404 } />
    </Switch>
  );
}


export default AccountIndexPage;
