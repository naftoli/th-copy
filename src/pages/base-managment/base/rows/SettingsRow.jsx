import React, { Component } from 'react';
// components
import { Row, Col } from 'reactstrap';
import { Radio, Checkbox, Date } from 'components/inputs';
// functions
import julian from 'julian';
import moment from 'moment';
import { toJulian } from 'functions/utils';
import { eventToUpdate, handleCheckbox } from 'functions/events';

export class SettingsRow extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  disableSchoolReset = () => {
    const store_reset = this.props.base.store_reset > 0 ? 0 : toJulian( moment() );
    this.props.onUpdate({ store_reset });
  }

  onDateChage = date => {
    this.props.onUpdate({ store_reset: date ? toJulian( date ) : date });
  }

  handleCheckbox = handleCheckbox( this.props.onUpdate );

  render () {
    let { 
      store_reset, school_gender, 
      allow_parent_tasks, print_parent_tasks,
      chayolei, chidon, tanya, tehillim // modules
    } = this.props.base;

    store_reset = store_reset > 0 ? moment( julian.toDate( store_reset ) ) : undefined;
    // props for all inputs
    const checkboxProps = { onChange: this.handleCheckbox };
    
    return (
      <Row id='SettingsRow'>
        <Col xs={12} sm={6}>
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
        {/* <Col xs={12} sm={8} xl={5}>
          <label>Base Enrolled in:</label><br/>
          <Checkbox checked={!!chayolei} name='chayolei' { ...checkboxProps} >
            Chayolei
          </Checkbox>
          <Checkbox checked={!!chidon} name='chidon' { ...checkboxProps} >
            Chidon
          </Checkbox>
          <Checkbox checked={!!tanya} name='tanya' { ...checkboxProps} >
            Tanya
          </Checkbox>
          <Checkbox checked={!!tehillim} name='tehillim' { ...checkboxProps} >
            WWTC
          </Checkbox>
        </Col> */}
        <Col xs={12} sm={6}>
          <label>Custom Parent Tasks</label><br/>
          <Checkbox checked={!!allow_parent_tasks} name='allow_parent_tasks' { ...checkboxProps} >
            Allow
          </Checkbox>
          <Checkbox checked={!!print_parent_tasks} name='print_parent_tasks' { ...checkboxProps} >
            Print on Mission Sheets
          </Checkbox>
        </Col>
        <Col xs={12} lg={6} id='store-miles'>
          <label>Store Miles Start From:</label>
          <Date
            value={ store_reset }
            disabled = { !store_reset }
            onChange={ this.onDateChage } />
            
          <Checkbox checked={ !store_reset } onChange={ this.disableSchoolReset }>
            Allow soldiers to spend all their miles.
          </Checkbox>
        </Col>

        { }
      </Row>
);
  }
}
