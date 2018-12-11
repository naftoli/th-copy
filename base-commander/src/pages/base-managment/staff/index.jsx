import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import StaffPage from './StaffPage';
import StaffDetailPage from './StaffDetailPage';
// styles for all staff pages
import './staff.scss'; 

class StaffIndexPage extends Component {
  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ StaffPage } />
        <Route path={`${path}/:id([0-9]+)`} component={ StaffDetailPage } />
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

export default StaffIndexPage;
