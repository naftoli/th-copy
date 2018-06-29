import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';

import AllUsers from './AllUsers/AllUsers';

export class UsersPage extends Component {

  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ AllUsers } />
        <Route path={`${path}/registration`} render={props => <h1>Bulk User Registration</h1>}/>
        <Route path={`${path}/cards`} render={props => <h1>User Rank Cards</h1>}/>
        <Route path={`${path}/new`} render={props => <h1>Create New User</h1>}/>
        <Route path={`${path}/:id`} render={props => <h1>View / Edit Single User: { props.match.params.id }</h1>}/>
      </Switch>
    )
  }
}

export default UsersPage;
