import React, { Component } from 'react';
import { connect } from 'react-redux';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
// screens
import { Page404 } from 'pages/errors';
import { Login, Logout, AccountPage } from 'pages/login';
// pages
import HomePage from 'pages/home/HomePage';
import Rewards from 'pages/rewards';
import BaseManagment from 'pages/base-managment';
import Missions from 'pages/missions';
// components
import { Dashboard } from 'components/navigation';
import { LoadingScreen } from 'components/ui';
import ConfirmationModal from 'components/modals/ConfirmationModal';
// functions
import { loginStoreChanged } from 'functions/login';
import { validateLogin } from 'store/login/operations';

export class App extends Component {
  // state for confirmation modal
  state = { 
    message: '',
    isOpen: false,
    refreshing: true, // by default the app is refreshing as we have nothing in redux
  }

  // validate the login every 5 seconds
  componentDidMount(){
    this.interval = setInterval( validateLogin, 5000 );
  }
  // clear the intervals
  componentWillUnmount(){
    console.log( this.interval, this.timeout );
    debugger;
    clearInterval( this.interval );
    clearTimeout( this.timeout );
  }

  /**
   * Note that if the redux store is cached or hydrated you will need to update the folowing behavior....
   */
  componentDidUpdate({ login }) { // once we get a login ( which starts off as {} ) then refresh the dashboard.
    if ( loginStoreChanged( login ) ) {
      const hadLogin = Object.keys( login ).length > 0;
      // show the user the refreshing screen when changing logins.
      // This would be a good time to do any work we might need to do.
      this.setState({ refreshing: hadLogin });
      if ( hadLogin ) {
        this.timeout = setTimeout(
          () => this.setState({ refreshing: false }),
          500
        );
      }
    }
  }
  
  // show the confirmation modal for react-router
  callback = false;
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
    if ( !this.props.logged_in ) {
      return (
        <Router basename={ process.env.PUBLIC_URL }>
          <Switch>
            <Route path={`/forgot`} exact component={ Page404 }/>
            <Route path={`/signup`} exact component={ Page404 }/>
            <Route component={ Login } />
          </Switch>
        </Router>
      );
    }
    const { message, isOpen, refreshing } = this.state;
      
    // render the core dashboard
    return (
      <Router
          basename={ process.env.PUBLIC_URL }
          getUserConfirmation={ this.showDialog }>

        <Dashboard>
          { refreshing &&  <LoadingScreen /> }

          { !refreshing && 
            <Switch>
              <Route path={`/`} exact component={ HomePage }/>
              <Route path={`/rewards`} component={ Rewards } />
              <Route path={`/bm`} component={ BaseManagment } />
              <Route path={`/missions`} component={ Missions } />
              
              <Route path={`/myaccount`} exact component={ AccountPage }/>
              {/* Action only pages */}
              <Route path={`/logout`} component={Logout}/>
              <Route component={ Page404 } />
            </Switch>
          }

          <ToastContainer 
            position="bottom-right" 
            autoClose={ 5000 } 
            closeOnClick={ false } 
            draggablePercent={ 40 } />

          <ConfirmationModal 
            isOpen={ isOpen } 
            message={ message } 
            callback={ this.handleCallback } />

        </Dashboard>
      </Router>
    );
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
