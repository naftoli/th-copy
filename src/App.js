import React, { Component } from 'react';
import { connect } from 'react-redux';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';

import Dashboard from 'pages/Dashboard/Dashboard';
import Login, { Logout } from 'pages/Login/';
import 'styles/App.css';

export class App extends Component {
  render() {
    if ( this.props.logged_in ) {
      return (
        <Router basename={ process.env.PUBLIC_URL } >
          <Dashboard>
            <Switch>
              <Route path={`/`} exact render={props => <h1>HomePage</h1>}/>
              <Route path={`/users`} exact render={props => <h1>View / Edit Users</h1>}/>
              <Route path={`/users/registration`} render={props => <h1>Bulk User Registration</h1>}/>
              <Route path={`/users/cards`} render={props => <h1>User Rank Cards</h1>}/>
              <Route path={`/users/:id`} render={props => <h1>View / Edit Single User</h1>}/>

              <Route path={`/plattons`} exact render={props => <h1>Plattons</h1>}/>
              <Route path={`/parents`} exact render={props => <h1>Parents</h1>}/>
              <Route path={`/staff`} exact render={props => <h1>Staff</h1>}/>

              <Route path={`/base`} exact render={props => <h1>View / Edit Base</h1>}/>
              <Route path={`/base/settings`} exact render={props => <h1>Base Settings</h1>}/>
              <Route path={`/base/transactions`} exact render={props => <h1>Base Transactions</h1>}/>

              <Route path={`/logout`} component={Logout}/>

              <Route render={ props => <h1>404 - {process.env.PUBLIC_URL}</h1> } />
            </Switch>
          </Dashboard>
        </Router>
      );
    } else {
      return <Login />;
    }
  }
}

const mapStateToProps = ( state ) => {
  return {
    logged_in: !!state.login.current_user
  }
}

export default connect( mapStateToProps )( App );
