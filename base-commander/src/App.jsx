import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
// screens
import { Page404 } from 'pages/errors';
import Login, { Logout } from 'pages/login';
// pages
import Account from 'pages/account';
import Rewards from 'pages/rewards';
import Missions from 'pages/missions';
import HomePage from 'pages/home/HomePage';
import BaseManagment from 'pages/base-managment';
import RegistrationPage from 'pages/base-managment/base/RegisterBasePage';
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

  static defaultProps = {
    title: '',
    login: {},
    current_user: false,
    logged_in: false
  }

  // validate the login every 5 seconds
  componentDidMount(){
    this.interval = setInterval( validateLogin, 5000 );
  }
  // clear the intervals
  componentWillUnmount(){
    clearTimeout( this.timeout );
    clearInterval( this.interval );

    console.log({ interval: this.interval, timeout: this.timeout });
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
    const { message, isOpen, refreshing } = this.state;
    const { title, login, current_user, logged_in } = this.props;

    const dashbardProps = { title, login, current_user };

    let routes = [
      { path: `/`, exact: true, component: HomePage },
      { path: `/myaccount`, exact: true, component: Account },
      { path: `/logout`, component: Logout },
      { component: RegistrationPage }
    ];

    if ( login.active ) {
      routes = [
          { path: `/`, exact: true, component:  HomePage },
          { path: `/rewards`, component:  Rewards },
          { path: `/bm`, component:  BaseManagment },
          { path: `/missions`, component:  Missions },
          { path: `/myaccount`, exact: true, component:  Account},
          { path: `/logout`, component: Logout },
          { path: `/register`, component: RegistrationPage },
          { component:  Page404 }
      ];
    }
      
    // render the core dashboard
    return (
      <Router
          basename={ process.env.PUBLIC_URL }
          getUserConfirmation={ this.showDialog }>
        
        <Fragment>
          { !logged_in &&
            <Login /> }

          { logged_in && 
            <Dashboard
              { ...dashbardProps }>
              { refreshing &&  <LoadingScreen /> }

              { !refreshing && 
                <Switch>
                  { routes.map( ( props, index ) => 
                    <Route { ...props } key={ index } />
                  )}
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
          }
        </Fragment>
      </Router>
    );
  }
}

const mapStateToProps = ( state ) => {
  const { current_login, current_user, title } = state.login;
  return {
    current_user, title,
    login: current_login,
    logged_in: Object.keys( current_login ).length > 0 && !!current_user,
  }
}

export default connect( mapStateToProps )( App );
