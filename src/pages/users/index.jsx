import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import UsersPage from './UsersPage/UsersPage';
import UserPage from './UserPage/UserPage';
import NewUserPage from './UserPage/NewUserPage';
import RegistrationPage from './RegistrationPage/RegistrationPage';
// functions
import { connect } from 'react-redux';


export class UsersIndexPage extends Component {

  render() {
    const { code } = this.props.login;
    const { path } = this.props.match;
    const isBC = ['HQ', 'CKIDS-ADMIN', 'BC'].includes( code );
    return (
      <Switch>
        <Route path={ path } exact component={ UsersPage } />
        { isBC && <Route path={`${path}/registration`} component={ RegistrationPage }/> }
        { isBC && <Route path={`${path}/cards`} render={props => <h1>User Rank Cards</h1>}/> }
        <Route path={`${path}/new`} component={ NewUserPage }/>
        <Route path={`${path}/:id([0-9]+)`} component={ UserPage }/>
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
})

export default connect( mapStateToProps )( UsersIndexPage );
