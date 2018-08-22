import React, { Component } from 'react';
// components
import DatePicker from 'react-datepicker';
import { Row, Col, Input } from 'reactstrap';
import { Radio, Checkbox } from 'components/inputs';
// functions
import julian from 'julian';
import moment from 'moment';
import { toJulian } from 'functions/utils';
import { eventToUpdate, handleCheckbox } from 'functions/events';

export class SettingsTab extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  handleCheckbox = handleCheckbox( this.props.onUpdate );

  disableSchoolReset = () => {
    const store_reset = this.props.base.store_reset > 0 ? 0 : toJulian( moment() );
    this.props.onUpdate({ store_reset });
  }


  render(){
    let { 
      store_reset, school_gender, allow_parent_tasks, print_parent_tasks, 
      chayolei, chidon, tanya, tehillim, ckids
    } = this.props.base;
    
    store_reset = store_reset > 0 ? moment( julian.toDate( store_reset ) ) : undefined;

    return (
      <div id='SettingsTab'>
        <Row id='image-row'>
          <Col xs={12} sm={4} xl={2}>
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
          <Col xs={12} sm={8} xl={4}>
            <label>Base Enrolled in:</label><br/>
            <Checkbox checked={!!chayolei} name='chayolei' onChange={this.handleCheckbox}>
              Chayolei
            </Checkbox>
            <Checkbox checked={!!chidon} name='chidon' onChange={this.handleCheckbox}>
              Chidon
            </Checkbox>
            <Checkbox checked={!!tanya} name='tanya' onChange={this.handleCheckbox}>
              Tanya
            </Checkbox>
            <Checkbox checked={!!tehillim} name='tehillim' onChange={this.handleCheckbox}>
              WWTC
            </Checkbox>
            <Checkbox checked={!!ckids} name='ckids' onChange={this.handleCheckbox}>
              C-Kids
            </Checkbox>
          </Col>
          <Col xs={12} sm={6} xl={3}>
            <label>Custom Parent Tasks</label><br/>
            <Checkbox checked={!!allow_parent_tasks} name='allow_parent_tasks' onChange={this.handleCheckbox}>
              Allow
            </Checkbox>
            <Checkbox checked={!!print_parent_tasks} name='print_parent_tasks' onChange={this.handleCheckbox}>
              Print on Mission Sheets
            </Checkbox>
          </Col>
          <Col xs={12} lg={6} id='store-miles'>
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
      </div>
    )
  }
}
