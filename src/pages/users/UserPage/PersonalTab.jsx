import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, Input, Label } from 'reactstrap';
import DatePicker from 'react-datepicker';
import ProfilePicture from 'components/ui/ProfilePicture';
// functions
import { toHebrew } from 'functions/utils';
import moment from 'moment';

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
      user_serial, profilePicture, barcode, gender,
      first, last, first_he, last_he, dob, dob_he
    } = this.props.soldier;
    dob = moment( dob );

    // render form
    return (
      <div id='PersonalTab'>
        <Row id='image-row'>
          <Col xs={{ size: 4, order: 12 }}>
            <ProfilePicture src={`${LEGACY_URL}${profilePicture}`} className='inline-profile' />
          </Col>
          <Col xs='8'>
            <h3>Serial #: {user_serial}</h3>
            <h3>Barcode: {barcode}</h3>
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
          </Col>
        </Row>
        <Row>
          <Col xs='6' sm='4'>
            <label>Date of Birth</label>
            <DatePicker className='form-control' dropdownMode="select" showMonthDropdown
              selected={moment( dob )} onChange={this.dateChange('dob')} showYearDropdown
              dateFormat="LL"
              minDate={moment().subtract( 20, 'years' )} maxDate={moment().subtract( 5, "years" )}
            />
          </Col>
          <Col xs='6' sm='4' dir='rtl'>
            <label>יום הולדת</label>
            <Input disabled value={ dob_he }/>
          </Col>
          <Col xs='12' sm='4'>
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
        {/* <pre>{ JSON.stringify( this.props, null, 2 ) }</pre> */}
      </div>
    );
  }
}

export default PersonalTab;