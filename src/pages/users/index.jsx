import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';

import UsersPage from './UsersPage/UsersPage';
import UserPage from './UserPage/UserPage';
import NewUserPage from './UserPage/NewUserPage';

export class UsersIndexPage extends Component {

  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ UsersPage } />
        <Route path={`${path}/registration`} render={props => <h1>Bulk User Registration</h1>}/>
        <Route path={`${path}/cards`} render={props => <h1>User Rank Cards</h1>}/>
        <Route path={`${path}/new`} component={ NewUserPage }/>
        <Route path={`${path}/:id`} component={ UserPage }/>
      </Switch>
    )
  }
}

export default UsersIndexPage;
