import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, Input } from 'reactstrap';
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
      user_serial, profilePicture, barcode, 
      first, last, first_he, last_he, 
      dob, dob_he, currentRank, gender
    } = this.props.soldier;
    dob = moment( dob );

    // render form
    return (
      <div id='PersonalTab'>
        <Row id='image-row'>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3'>
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
          <Col xs='12' sm='8' lg='9'>
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
                <DatePicker className='form-control' dropdownMode="select" showMonthDropdown
                  selected={moment( dob )} onChange={this.dateChange('dob')} showYearDropdown
                  dateFormat="LL"
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
        Address
        Registration Info
        {/* <pre>{ JSON.stringify( this.props, null, 2 ) }</pre> */}
      </div>
    );
  }
}

export default PersonalTab;