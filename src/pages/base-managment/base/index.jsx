import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import BasePage from './BasePage';
import BasesPage from './BasesPage';
import { Page404 } from 'pages/errors';
// styles for all base pages
import './base.scss'; 

class BaseIndexPage extends Component {
  render() {
    const { path } = this.props.match;

    return (
      <Switch>
        {/* Index routes */}
        <Route path={ path } exact component={ BasesPage } />
        {/* <Route path={`${path}/transactions`} exact render={props => <h1>Base Transactions List</h1>}/> */}
        {/* Detail Page Routes */}
        <Route path={`${path}/:id([0-9]+)`} exact component={ BasePage } />
        {/* <Route path={`${path}/:id([0-9]+)/transactions`} exact render={props => <h1>Base Transactions</h1>}/> */}
        {/* All other routes */}
        <Route component={ Page404 } />
      </Switch>
    )
  }
}


export default BaseIndexPage;
