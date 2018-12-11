import React, { Component } from 'react';
import { connect } from 'react-redux';
// sub pages
import TeacherMarkPage from './TeacherMarkPage';
import BCMarkPage from './BCMarkPage';
// functions
import { setTitle } from 'functions/utils';
import { isTeacher } from 'functions/login';
// style
import './includes/MarkPage.scss';

class MarkPage extends Component {

  componentDidMount() {
    setTitle( 'Mark Missions' );
  }

  render() {
    const { login } = this.props;

    if ( isTeacher( login.code ) )
      return <TeacherMarkPage />;

    return <BCMarkPage />;
  }
}

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
};

export default connect( mapStateToProps )( MarkPage );
