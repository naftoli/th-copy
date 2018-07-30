import React from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
import DatePicker from 'react-datepicker';
// functions
import moment from 'moment';

const DobRow = ( { soldier, onChange, show_he, children } ) => {
  const { dob, dob_he } = soldier;
  return (
    <Row>
      <Col xs='6'>
        <label>Date of Birth</label>
        <DatePicker className='form-control' 
          // display formats
          dateFormat='LL' readOnly showMonthDropdown showYearDropdown dropdownMode='select'
          // current date and handle change
          selected={ dob ? moment( dob ) : undefined } onChange={ onChange } 
          // client side date validations
          minDate={moment().subtract( 20, 'years' )} maxDate={moment().subtract( 5, 'years' )}
        />
      </Col>
      { show_he && 
        <Col xs='6'dir='rtl'>
          <label>יום הולדת</label>
          <Input disabled value={ dob_he }/>
        </Col>
      }
      { children }
    </Row>
  )
}

export default DobRow;
