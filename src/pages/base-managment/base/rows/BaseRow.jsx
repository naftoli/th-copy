import React, { Component } from 'react';
// components
import DatePicker from 'react-datepicker';
import { Row, Col, Input } from 'reactstrap';
import { Radio, Checkbox } from 'components/inputs';
// functions
import julian from 'julian';
import moment from 'moment';
import { toJulian } from 'functions/utils';
import { hebrewChange, eventToUpdate } from 'functions/events';

export class BaseRow extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  onDateChage = date => {
    this.props.onUpdate({ store_reset: toJulian( date ) });
  };

  disableSchoolReset = () => {
    const store_reset = this.props.store_reset > 0 ? 0 : toJulian( moment() );
    this.props.onUpdate({ store_reset });
  }

  render () {
    let { school_name, school_name_he, store_reset, school_gender, required } = this.props;
    // props for all inputs
    const inputProps = { required, onChange: this.onChange };
    
    store_reset = store_reset > 0 ? moment( julian.toDate( store_reset ) ) : undefined;
    
    return (
      <Row>
        {/* Base Name */}
        <Col xs='6'>
          <label>Base Name</label>
          <Input name='school_name' value={ school_name } { ...inputProps } 
            pattern='^.{3,}$' title="Three or more letters" />
          <div className='invalid-message'>Please enter 3 or more letters</div>
        </Col>
        <Col xs='6' dir='rtl'>
          <label htmlFor='school_name_he'>Hebrew Base Name</label>
          <Input name='school_name_he' value={ school_name_he }
            { ...inputProps } onChange={ hebrewChange( this.onChange ) }
            pattern='^[^a-zA-Z]{3,}$' title="Three or more Hebrew letters" />
          <div className='invalid-message'>Please enter 3 or more <em>Hebrew</em> letters</div>
          <p className='input-message'>(This is how it will appear on school banner)</p>
        </Col>
        {/* Base Gender */}
        <Col xs={12} sm={5} lg={6}>
          <label>Base Gender</label>
          <div id='gender-row'>
            <Radio type='radio' name='school_gender' id='school_gender' value='M' 
                checked={ school_gender === 'M' } onChange={ this.onChange }>
              Boys
            </Radio>
            <Radio type='radio' name='school_gender' id='school_gender' value='F'
                checked={ school_gender === 'F' } onChange={ this.onChange }>
              Girls
            </Radio>
            <Radio type='radio' name='school_gender' id='school_gender' value='B'
                checked={ school_gender === 'B' } onChange={ this.onChange }>
              Both
            </Radio>
          </div>
        </Col>
        <Col xs={12} sm={7} lg={6} id='store-miles'>
          <label>Store Miles Start From:</label>
          <DatePicker className='form-control' 
            // display formats
            dateFormat='LL' readOnly showMonthDropdown showYearDropdown dropdownMode='select'
            // current date and handle change
            selected={ store_reset } onChange={ this.onDateChage } 
          />
          <Checkbox checked={ !store_reset } onChange={ this.disableSchoolReset }>
            Allow soldiers to spend all their miles.
          </Checkbox>
        </Col>
      </Row>
    );
  }
}
