import React, { Component } from 'react';
import { connect } from 'react-redux';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';

import Dashboard from 'pages/dashboard/Dashboard';
import Login, { Logout } from 'pages/login';
import UsersPages from 'pages/users';

export class App extends Component {
  render() {
    if ( this.props.logged_in ) {
      return (
        <Router basename={ process.env.PUBLIC_URL } >
          <Dashboard>
            <Switch>
              <Route path={`/`} exact render={props => <h1>HomePage</h1>}/>
              <Route path={`/users`} component={ UsersPages } />

              <Route path={`/platoons`} exact render={props => <h1>Platoons</h1>}/>
              <Route path={`/parents`} exact render={props => <h1>Parents</h1>}/>
              <Route path={`/staff`} exact render={props => <h1>Staff</h1>}/>

              <Route path={`/base`} exact render={props => <h1>View / Edit Base</h1>}/>
              <Route path={`/base/settings`} exact render={props => <h1>Base Settings</h1>}/>
              <Route path={`/base/transactions`} exact render={props => <h1>Base Transactions</h1>}/>

              <Route path={`/logout`} component={Logout}/>

              <Route render={ props => <h1>404</h1> } />
            </Switch>
            <ToastContainer position="bottom-right" autoClose={ 8000 } closeOnClick={false} />
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
