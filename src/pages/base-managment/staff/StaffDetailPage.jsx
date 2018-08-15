import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Spinner, ProfilePicture, FontAwesome } from 'components/ui';
import { PhoneNumber } from 'components/inputs';
import { Row, Col, Input, Button, InputGroup, InputGroupAddon } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { loginStoreChanged } from 'functions/login';

class StaffDetailPage extends Component {

  state = { selectedUserId: false }

  componentDidMount() {
    if ( this.props.parents.length === 0 ) {
      // get the staff member
    }
  }

  componentDidUpdate({ login }) {
    if ( loginStoreChanged( login ) ) {
      // get the staff member
    };
  }

  render() {

    // and render the page
    return (
      <div id='StaffDetailPage'>
        <p className='title'>Account Information</p>
        <pre>{ JSON.stringify( this.props, null, 2 ) }</pre>
      </div>
    );
  }
}

const mapStateToProps = ( { parents, login } ) => ({
  login: login.current_login
})

const mapDispatchToProps = {}

export default connect( 
  mapStateToProps, mapDispatchToProps 
)( StaffDetailPage );
