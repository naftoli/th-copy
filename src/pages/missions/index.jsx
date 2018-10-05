import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404, Construction } from 'pages/errors';
import PrintPage from './print/PrintPage';
// import PlatoonPages from './platoons';
// import ParentPages from './parents';
// import StaffPages from './staff';
// import BasePages from './base';
// functions
import { isBC } from 'functions/login';

export class BaseManagmentIndexPage extends Component {

  render() {
    const { code } = this.props.login;
    const { path } = this.props.match;
    // pages only Base Commanders can access
    const BCRoutes = [
      <Route key={1} path={`${path}/personalize`} component={ Construction } />,
      <Route key={2} path={`${path}/tasks`} component={ Construction } />,
    ];

    return (
      <Switch>
        <Route path={`${path}/print`} component={ PrintPage } />
        <Route path={`${path}/mark`} component={ Construction } />

        { isBC( code ) && BCRoutes }

        <Route path={`${path}/report`} component={ Construction } />
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect( mapStateToProps )( BaseManagmentIndexPage );
