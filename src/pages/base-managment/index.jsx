import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import UsersPages from './users';
import PlatoonPages from './platoons';
import ParentPages from './parents';
import StaffPages from './staff';
// functions
import { connect } from 'react-redux';


export class BaseManagmentIndexPage extends Component {

  render() {
    const { code } = this.props.login;
    const { path } = this.props.match;
    const isBC = ['HQ', 'CKIDS-ADMIN', 'BC'].includes( code );

    const BCRoutes = [
      <Route key={1} path={`${path}/platoons`} component={ PlatoonPages } />,
      <Route key={2} path={`${path}/parents`} component={ ParentPages } />,
      <Route key={3} path={`${path}/staff`} component={ StaffPages } />,

      <Route key={4} path={`${path}/base`} exact render={props => <h1>View / Edit Base</h1>}/>,
      <Route key={5} path={`${path}/base/settings`} exact render={props => <h1>Base Settings</h1>}/>,
      <Route key={6} path={`${path}/base/transactions`} exact render={props => <h1>Base Transactions</h1>}/>
    ];

    return (
      <Switch>
        <Route path={`${path}/users`} component={ UsersPages } />
        {/* render BC only routes */}
        { isBC && BCRoutes }
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
})

export default connect( mapStateToProps )( BaseManagmentIndexPage );
