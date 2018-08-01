import React, { Component } from 'react';
import { connect } from 'react-redux';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
// pages
import Dashboard from 'pages/dashboard/Dashboard';
import Login, { Logout } from 'pages/login';
import UsersPages from 'pages/users';
// components
import ConfirmationModal from 'components/modals/ConfirmationModal';

export class App extends Component {
  // state for confirmation modal
  state = { message: '', isOpen: false }
  callback = false;
  // show the modal
  showDialog = ( message, callback ) => {
    this.callback = callback;
    this.setState({ message: message, isOpen: true });
  }
  // hide the modal when an option is selected
  handleCallback = ( ok ) => {
    this.callback( ok );
    this.setState({ isOpen: false });
  }
  // render the page
  render() {
    if ( this.props.logged_in ) {
      const { message, isOpen } = this.state;
      return (
        <Router basename={ process.env.PUBLIC_URL } getUserConfirmation={ this.showDialog } >
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

              <Route render={ () => <h1>404</h1> } />
            </Switch>
            <ToastContainer position="bottom-right" autoClose={ 8000 } closeOnClick={false} draggablePercent={40}/>
            <ConfirmationModal isOpen={ isOpen } message={ message } callback={ this.handleCallback } />
          </Dashboard>
        </Router>
      );
    } else {
      return <Login />;
    }
  }
}

const mapStateToProps = ( state ) => {
  const { current_login, current_user } = state.login;
  return {
    logged_in: !!current_login && !!current_user
  }
}

export default connect( mapStateToProps )( App );
