import React, { Component } from 'react';
import { connect } from 'react-redux';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
// pages
import { Page404 } from 'pages/errors';
import V2 from 'pages/rewards/v2';
import HomePage from 'pages/home/HomePage';
import { LoadingScreen } from 'components/ui';
import BaseManagment from 'pages/base-managment';
import Dashboard from 'pages/dashboard/Dashboard';
import { Login, Logout, AccountPage } from 'pages/login';
// components
import ConfirmationModal from 'components/modals/ConfirmationModal';
// functions
import { loginStoreChanged } from 'functions/login';

export class App extends Component {
  // state for confirmation modal
  state = { 
    message: '', isOpen: false,
    refreshing: true, // by default the app is refreshing as we have nothing in redux
  }

  /**
   * Note that if the redux store is cached or hydrated you will need to update the folowing behavior....
   */
  componentDidUpdate({ login }) { // once we get a login ( which starts off as {} ) then refresh the dashboard.
    if ( loginStoreChanged( login ) ) {
      const hadLogin = Object.keys( login ).length > 0;
      this.setState({ refreshing: hadLogin });
      if ( hadLogin ) {
        // show the user the refreshing screen. This would be a good time to do any work we might need to do.
        setTimeout(() => { this.setState({ refreshing: false }); }, 500)
      }
    }
  }
  
  // show the confirmation modal for react-router
  callback = false;
  showDialog = ( message, callback ) => { this.callback = callback; this.setState({ message: message, isOpen: true }); }
  // hide the modal when an option is selected
  handleCallback = ( ok ) => { this.callback( ok ); this.setState({ isOpen: false }); }
  
  // render the page
  render() {
    if ( this.props.logged_in ) {
      const { message, isOpen, refreshing } = this.state;
      // make sure we are not "refreshing"
      
      // render the core dashboard
      return (
        <Router basename={ process.env.PUBLIC_URL } getUserConfirmation={ this.showDialog } >
          <Dashboard>

            { refreshing &&  <LoadingScreen /> }

            { !refreshing && 
              <Switch>
                <Route path={`/v2`} exact component={ V2 } />
                <Route path={`/`} exact component={ HomePage }/>
                <Route path={`/bm`} component={ BaseManagment } />
                <Route path={`/myaccount`} exact component={ AccountPage }/>
                {/* Action only pages */}
                <Route path={`/logout`} component={Logout}/>
                <Route component={Page404} />
              </Switch>
            }
            
            <ToastContainer 
              position="bottom-right" 
              autoClose={ 5000 } 
              closeOnClick={false} 
              draggablePercent={40} />

            <ConfirmationModal 
              isOpen={ isOpen } 
              message={ message } 
              callback={ this.handleCallback } />

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
