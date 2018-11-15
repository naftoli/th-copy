import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import { logout } from 'store/login/actions';

class Logout extends Component {
  // log the user out
  componentDidMount() {
    this.props.logout();
  }
  // redirect to the homepage
  render() {
    return <Redirect to="/" />;
  }
}

export default connect( false, { logout } )( Logout );