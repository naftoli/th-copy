import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import TasksPage from './tasks/TasksPage';
import CardsPage from './cards/CardsPage';
// functions
// import { isBC } from 'functions/login';

export class BaseManagmentIndexPage extends Component {

  render() {
    const { path } = this.props.match;

    return (
      <Switch>
        <Route path={`${path}/cards`} component={ CardsPage } />
        <Route path={`${path}/tasks`} component={ TasksPage } />
        <Route path={`${path}/prizes`} render={ () => <h1>Prizes Pages Coming Soon!</h1> } />
        <Route path={`${path}/orders`} render={ () => <h1>Orders Page Coming Soon!</h1> } />

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
