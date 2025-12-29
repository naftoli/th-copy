import React, { useEffect } from 'react';
import { connect } from 'react-redux';
// sub pages
import TeacherMarkPage from './TeacherMarkPage';
import BCMarkPage from './BCMarkPage';
// functions
import { setTitle } from 'functions/utils';
import { isTeacher } from 'functions/login';
// style
import './includes/MarkPage.scss';

const MarkPage = ({ login }) => {

  useEffect(() => {
    setTitle('Mark Missions');
  }, []);

  if (isTeacher(login.code))
    return <TeacherMarkPage />;

  return <BCMarkPage />;
}

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
};

export default connect(mapStateToProps)(MarkPage);
