import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Switch, Route } from 'react-router-dom';
// sub pages
import PersonalizePage from './personalize/PersonalizePage';
import { Page404, Construction } from 'pages/errors';
import PrintPage from './print/PrintPage';
import TasksPage from './tasks/TasksPage';
import MarkPage from './mark';
// functions
import { isBC } from 'functions/login';

export class BaseManagmentIndexPage extends Component {

  render() {
    const { code } = this.props.login;
    const { path } = this.props.match;

    return (
      <Switch>
        <Route path={`${path}/print`}       component={ PrintPage } />
        <Route path={`${path}/mark`}        component={ MarkPage } />
        <Route path={`${path}/personalize`} component={ PersonalizePage } />
        <Route path={`${path}/report`}      component={ Construction } />

        { isBC( code ) &&
          <Route path={`${path}/tasks`}     component={ TasksPage } /> }

        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect( mapStateToProps )( BaseManagmentIndexPage );
