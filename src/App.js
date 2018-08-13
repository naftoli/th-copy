import React, { Component } from 'react';
import { connect } from 'react-redux';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
// pages
import Dashboard from 'pages/dashboard/Dashboard';
import Login, { Logout } from 'pages/login';
import BaseManagment from 'pages/base-managment';
// components
import ConfirmationModal from 'components/modals/ConfirmationModal';
import { Page404 } from 'pages/errors';

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
      const { code } = this.props.login;
      const { message, isOpen } = this.state;
      return (
        <Router basename={ process.env.PUBLIC_URL } getUserConfirmation={ this.showDialog } >
          <Dashboard>
            <Switch>
              <Route path={`/`} exact render={props => <h1>HomePage</h1>}/>
              <Route path={`/bm`} component={ BaseManagment } />
              
              <Route path={`/logout`} component={Logout}/>
              <Route component={Page404} />
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
    logged_in: !!current_login && !!current_user,
    login: current_login
  }
}

export default connect( mapStateToProps )( App );
