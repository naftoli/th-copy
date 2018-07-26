import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, Input } from 'reactstrap';
import DatePicker from 'react-datepicker';
import MaskedInput from 'react-text-mask'
import ProfilePicture from 'components/ui/ProfilePicture';
// functions
import { toHebrew } from 'functions/utils';
import julian from 'julian';
import moment from 'moment';
// data
import masks from 'components/masks';

class PersonalTab extends Component {

  handleChange = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.value } );
  }

  hebrewChange = ( event ) => {
    event.target.value = toHebrew( event.target.value );
    this.handleChange( event );
  }

  dateChange = ( name ) => ( date ) => {
    this.props.handleChange(
      { [name]: date.format("YYYY-MM-DD HH:mm:ss") }
    )
  }

  render(){
    let { 
      user_serial, profilePicture, barcode, 
      first, last, first_he, last_he, 
      dob, dob_he, currentRank, gender,
      user_address1, user_city, user_state, user_phone,
      user_postal, user_country, user_start_date, user_registered
    } = this.props.soldier;
    // render form
    return (
      <div id='PersonalTab'>
        <Row id='image-row'>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
            <Row>
              <Col xs='3' sm='12'>
                <ProfilePicture src={`${LEGACY_URL}${profilePicture}`} className='inline-profile' 
                  rank={ currentRank.rank_ord} />
              </Col>
              <Col xs='9' sm='12'>
                <label>Gender</label>
                <div id='gender-row'>
                  <label>
                    <Input type='radio' name='gender' id='gender' value='M' 
                      checked={ gender === 'M' } onChange={ this.handleChange }/>{' '}
                    Male <i className='fas fa-male'></i>
                  </label>
                  <label>
                    <Input type='radio' name='gender' id='gender' value='F'
                      checked={ gender === 'F' } onChange={ this.handleChange }/>{' '}
                    Female <i className='fas fa-female'></i>
                  </label>
                </div>
              </Col>
            </Row>
          </Col>
          <Col xs='12' sm='8' lg='9' xl='10'>
            <h4>Serial #: {user_serial}</h4>
            <h4>Barcode: {barcode}</h4>
            <Row>
              <Col xs='6'>
                <label>First Name</label>
                <Input id='first' value={ first } onChange={ this.handleChange } />
              </Col>
              <Col xs='6'>
                <label>Last Name</label>
                <Input id='last' value={ last } onChange={ this.handleChange } />
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
            <Row>
              <Col xs='6'>
                <label>Date of Birth</label>
                <DatePicker className='form-control' dropdownMode="select" 
                  selected={ dob ? moment( dob ) : undefined } onChange={ this.dateChange('dob') } 
                  dateFormat="LL" readOnly showMonthDropdown showYearDropdown
                  minDate={moment().subtract( 20, 'years' )} maxDate={moment().subtract( 5, "years" )}
                />
              </Col>
              <Col xs='6'dir='rtl'>
                <label>יום הולדת</label>
                <Input disabled value={ dob_he }/>
              </Col>
            </Row>
          </Col>
        </Row>
        <Row id='address-row'>
          <Col xs='12'>
            <p className='title'>Address</p>
          </Col>
          <Col xs='12'>
            <label>Address</label>
            <Input id='user_address1' value={ user_address1 } onChange={ this.handleChange } />
          </Col>
          <Col xs='6'>
            <label>City</label>
            <Input id='user_city' value={ user_city } onChange={ this.handleChange } />
          </Col>
          <Col xs='3'>
            <label>State</label>
            <Input id='user_state' value={ user_state } onChange={ this.handleChange } />
          </Col>
          <Col xs='3'>
            <label>Zip</label>
            <Input id='user_postal' value={ user_postal } onChange={ this.handleChange } />
          </Col>
          <Col xs='6'>
            <label>Country</label>
            <Input id='user_country' value={ user_country } onChange={ this.handleChange } />
          </Col>
          <Col xs='6'>
            <label>Phone #</label>
            <MaskedInput className='form-control' id='user_phone' value={ user_phone } 
              onChange={ this.handleChange } mask={ masks.phone } />
          </Col>
        </Row>
        <Row id='registration-row'>
          <Col xs='12'>
            <p className='title'>Registration Information</p>
            <label>Member Since:</label>
            <h4>{moment( julian.toDate( user_start_date ) ).format("LLLL")}</h4>
            <label>Registered:</label>
            <h4>{ user_registered ? moment( user_registered ).format("LLLL") : 'Not Registered.'}</h4>
          </Col>
        </Row>
      </div>
    );
  }
}

export default PersonalTab;