import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col } from 'reactstrap';
import ProfilePicture from 'components/ui/ProfilePicture';

class PersonalTab extends Component {

  render(){
    const { 
      user_serial, user_id, profilePicture, barcode
    } = this.props.soldier;
    return (
      <div id='PersonalTab'>
        <Row>
          <Col sm="9">
            <h3>Serial #: {user_serial}</h3>
            <h3>Barcode: {barcode}</h3>
          </Col>
          <Col sm="3">
            <ProfilePicture src={`${LEGACY_URL}${profilePicture}`} className='inline-profile' />
          </Col>
        </Row>
        <pre>{ JSON.stringify( this.props, null, 2 ) }</pre>
      </div>
    );
  }
}

export default PersonalTab;