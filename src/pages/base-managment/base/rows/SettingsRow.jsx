import React, { Component } from 'react';
// components
import { Row, Col } from 'reactstrap';
import { Radio, Checkbox, Date, Label } from 'components/inputs';
// functions
import julian from 'julian';
import moment from 'moment';
import { toJulian } from 'functions/dates';
import { onCheckboxChange, onInputChange } from 'functions/events';
import { isHQ } from 'functions/login';

export class SettingsRow extends Component {

  onChange = onInputChange( this.props.onUpdate );
  handleCheckbox = onCheckboxChange( this.props.onUpdate );

  onNumberChange = ({ target }) =>
    this.props.onUpdate( { [target.name]: parseInt( target.value, 10 ) } );

  onDateChage = date =>
    this.props.onUpdate({ store_reset: date ? toJulian( date ) : date });

  disableSchoolReset = () => {
    const store_reset = this.props.base.store_reset > 0 ? 0 : toJulian( moment() );
    this.props.onUpdate({ store_reset });
  }

  render () {
    const { base, login } = this.props;
    let { 
      pic_mission_type,   store_reset,  allow_parent_tasks,
      chidon,   tanya,    chayolei,     tehillim,
      school_gender,      print_parent_tasks,
    } = base;

    store_reset = store_reset > 0 ? moment( julian.toDate( store_reset ) ) : undefined;
    // props for all inputs
    const checkboxProps = { onChange: this.handleCheckbox };
    const schoolGenderProps = { name: 'school_gender', onChange: this.onChange }
    const missionTypeProps = { name: 'pic_mission_type', onChange: this.onNumberChange }
    
    return (
      <div id='SettingsRow'>
        <Row>
          <Col xs={12} sm={6} xl={4}>
            <Label>Base Gender</Label>
            <Radio value='M' 
                { ...schoolGenderProps }
                checked={ school_gender === 'M' } >
              Boys
            </Radio>

            <Radio value='F'
                { ...schoolGenderProps }
                checked={ school_gender === 'F' }>
              Girls
            </Radio>

            <Radio value='B'
                { ...schoolGenderProps }
                checked={ school_gender === 'B' }>
              Both
            </Radio>
          </Col>

          <Col xs={12} sm={6} xl={4}>
            <Label>Mission Sheet Type</Label>
            <Radio value='1' 
                { ...missionTypeProps }
                checked={ pic_mission_type === 1 } >
              No Pictures
            </Radio>

            <Radio value='2'
                { ...missionTypeProps }
                checked={ pic_mission_type === 2 }>
              Small Pictures
            </Radio>
          </Col>

          <Col xs={12} sm={6} xl={4}>
            <Label>Custom Parent Tasks</Label>
            <Checkbox checked={!!allow_parent_tasks} name='allow_parent_tasks' { ...checkboxProps} >
              Allow
            </Checkbox>
            <Checkbox checked={!!print_parent_tasks} name='print_parent_tasks' { ...checkboxProps} >
              Print on Mission Sheets
            </Checkbox>
          </Col>

          <Col xs={12} sm={6} id='store-miles'>
            <Label>Store Miles Start From:</Label>
            <Date
              value={ store_reset }
              disabled = { !store_reset }
              onChange={ this.onDateChage } />
              
            <Checkbox checked={ !store_reset } onChange={ this.disableSchoolReset }>
              Allow soldiers to spend all their miles.
            </Checkbox>
          </Col>
        </Row>

        { isHQ( login.code ) && 
          <div id='hq-options'>
            <hr/>
            <Row>
              <Col xs={12}>
                <Label><strong>Base Enrolled in:</strong></Label>
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
              </Col>
            </Row>
          </div>
        }
      </div>
    );
  }
}
