import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Row, Col, Button } from 'reactstrap';
import { ButtonBar, InlineSync, Callout } from 'components/ui';
// import { BaseSelect, PlatoonSelect, SoldierSelect } from 'components/inputs';
// functions
import { setTitle } from 'functions/utils';
// import { isTeacher, isAdmin, isBC } from 'functions/login';
// import { createNotifcation, updateNotifcation } from 'functions/notifications';
// style
import './PrintPage.scss';
import { LEGACY_URL } from 'components/constants';

class PrintPage extends Component {

  state = { 
    school_id: false,
    class_ids: [],
    user_ids: []
  };

  componentDidMount() {
    const { school_id, class_id } = this.props.login;
    setTitle( 'Print Missions' );

    this.setState({ school_id });
    if ( class_id ) this.setState({ class_ids: [ class_id ] });
  }

  showInstructions = () => {
    window.open(
      `${ LEGACY_URL }/mission_report/instructions/`, '_blank', 
      'width=770, height=700, menubar=no, scrollbars=yes, status=no, toolbar=no, titlebar=no'
    );
  }

  render() {
    let { school_id, class_ids, user_ids } = this.state;

    return (
      <div id='PrintPage'>
        <Callout title='Print Missions'>
          <p><strong>Please check the printing instructions below before printing anything.</strong></p>
          <p>
            For performance reasons, printing missions will open in a new tab.
            It may take a while if you are printing missions for many soldiers.{' '}
            <strong>Please be paitient and wait for the pages to finish generating/loading.</strong>
          </p>
        </Callout>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
};

const mapDispatchToProps = {};

export default connect( mapStateToProps, mapDispatchToProps )( PrintPage );
