import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import SoldiersPages from './soldiers';
import PlatoonPages from './platoons';
import ParentPages from './parents';
import StaffPages from './staff';
import BasePages from './base';
// functions
import { isBC } from 'functions/login';

export class BaseManagmentIndexPage extends Component {

  render() {
    const { code } = this.props.login;
    const { path } = this.props.match;
    // pages only Base Commanders can access
    const BCRoutes = [
      <Route key={1} path={`${path}/platoons`} component={ PlatoonPages } />,
      <Route key={2} path={`${path}/parents`} component={ ParentPages } />,
      <Route key={3} path={`${path}/staff`} component={ StaffPages } />,
      <Route key={4} path={`${path}/base`} component={ BasePages } />,
    ];

    return (
      <Switch>
        <Route path={`${path}/soldiers`} component={ SoldiersPages } />
        {/* render BC only routes */}
        { isBC( code ) && BCRoutes }
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect( mapStateToProps )( BaseManagmentIndexPage );
