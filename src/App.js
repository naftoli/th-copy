import React, { Component } from 'react';
import { connect } from 'react-redux';
import Dashboard from 'pages/Dashboard/Dashboard';
import Login from 'pages/Login/Login';
import 'styles/App.css';

export class App extends Component {
  render() {
    if ( this.props.logged_in ) {
      return (
        <Dashboard>
          Hello World!
        </Dashboard>
      );
    } else {
      return <Login />;
    }
  }
}

const mapStateToProps = ( state ) => {
  return {
    logged_in: !!state.login.tokens.legacy
  }
}

export default connect( mapStateToProps )( App );
