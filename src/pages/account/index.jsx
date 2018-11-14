import React from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import AccountPage from './AccountPage';
// import BasesPage from './BasesPage';
import { Page404 } from 'pages/errors';

import './includes/styles.scss';

const AccountIndexPage = props => {
  const { path } = props.match;

  return (
    <div id='AccountsPages'>
      <Switch>
        <Route path={ path } exact component={ AccountPage } />

        <Route path={`${path}/login`} exact render={ () => <h1>Login Settings</h1> } />
        
        <Route component={ Page404 } />
      </Switch>
    </div>
  );
}


export default AccountIndexPage;
