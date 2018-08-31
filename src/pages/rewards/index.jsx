import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import TasksPage from './tasks/TasksPage';
// functions
// import { isBC } from 'functions/login';

export class BaseManagmentIndexPage extends Component {

  render() {
    const { path } = this.props.match;

    return (
      <Switch>
        <Route path={`${path}/cards`} render={ () => <h1>Achievement Cards Coming Soon!</h1> } />
        <Route path={`${path}/tasks`} component={ TasksPage } />

        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
})

export default connect( mapStateToProps )( BaseManagmentIndexPage );
// export link to legacy system
export { default as V2 } from './v2';
