import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, Input } from 'reactstrap';
import ProfilePicture from 'components/ui/ProfilePicture';
// functions
import { toHebrew } from 'functions/utils';

class PersonalTab extends Component {

  hebrewChange = ( event ) => {
    event.target.value = toHebrew( event.target.value );
    this.props.handleChange( event );
  }

  render(){
    const { 
      user_serial, profilePicture, barcode,
      first, last, first_he, last_he, 
    } = this.props.soldier;
    const { handleChange } = this.props;

    // render form
    return (
      <div id='PersonalTab'>
        <Row id='image-row'>
          <Col xs={{ size: 4, order: 12 }} lg='3'>
            <ProfilePicture src={`${LEGACY_URL}${profilePicture}`} className='inline-profile' />
          </Col>
          <Col xs='8' lg='9'>
            <h3>Serial #: {user_serial}</h3>
            <h3>Barcode: {barcode}</h3>
            <Row>
              <Col xs='6'>
                <label>First Name</label>
                <Input id='first' value={ first } onChange={ handleChange } />
              </Col>
              <Col xs='6'>
                <label>Last Name</label>
                <Input id='last' value={ last } onChange={ handleChange } />
              </Col>
            </Row>
            <Row>
              <Col xs='6' dir='rtl'>
                <label>שם פרטי (First Name)</label>
                <Input id='first_he' value={first_he} onChange={this.hebrewChange}/>
              </Col>
              <Col xs='6' dir='rtl'>
                <label>שם משפחה (Last Name)</label>
                <Input id='last_he' value={last_he} onChange={this.hebrewChange}/>
              </Col>
            </Row>
          </Col>
        </Row>
        <pre>{ JSON.stringify( this.props, null, 2 ) }</pre>
      </div>
    );
  }
}

export default PersonalTab;