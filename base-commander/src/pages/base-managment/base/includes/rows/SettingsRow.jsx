import React, { Component } from 'react';
// components
import { Row, Col } from 'reactstrap';
import { Radio, Checkbox, Date, Label } from 'components/inputs';
// functions
import julian from 'julian';
import moment from 'moment';
import { toJulian } from 'functions/dates';
import { onCheckboxChange, onInputChange, onNumberChange, onJSONChange } from 'functions/events';

export class SettingsRow extends Component {

  state = {
    disabled: false
  };

  onChange = onInputChange( this.props.onUpdate );
  handleCheckbox = onCheckboxChange( this.props.onUpdate );
  onNumberChange = onNumberChange( this.props.onUpdate );
  // handle input events
  onInputChange = onJSONChange( this.props.handleChange );

  onDateChage = date =>
    this.props.onUpdate({ store_reset: date ? toJulian( date ) : date });

  disableSchoolReset = () => {
    const store_reset = this.props.base.store_reset > 0 ? 0 : toJulian( moment() );
    this.props.onUpdate({ store_reset });
    this.setState({ disabled: true });
  }

  enableSchoolReset = event => {
    const store_reset = event.target.value;
    this.props.onUpdate({ store_reset });
    this.setState({ disabled: false });
  }



  render () {
    const { base } = this.props;
    let { 
      pic_mission_type,   store_reset,  school_gender,
      print_parent_tasks, allow_parent_tasks,
    } = base;

    const store_reset_jd = store_reset;
    store_reset = store_reset > 0 ? moment( julian.toDate( store_reset ) ) : undefined;
    //console.log( store_reset );
    console.log( store_reset_jd );

    // props for all inputs
    const checkboxProps = { onChange: this.handleCheckbox };
    const schoolGenderProps = { name: 'school_gender', onChange: this.onChange }
    const missionTypeProps = { name: 'pic_mission_type', onChange: this.onNumberChange }
    //const storeResetProps = { name: 'store_reset', onChange: this.onChange } 


    return (
      <Row id='SettingsRow'>
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

        <Col sm={12} className='special-options'>
          <p className='title'>Store Miles Settings</p>
          <Label>Store Miles Start From:</Label>

          <Radio name='store_miles_reset' value='2458663' 
            onChange={ this.onInputChange }
            checked={ store_reset_jd === 2458663 }>
            Friday, 25 Sivan (June 28) (Chayolim can use the points they earned from summer missions and on)
          </Radio>

          <Radio name='store_miles_reset' value='2458733' 
            onChange={ this.onInputChange }
            checked={ store_reset_jd === 2458733 }>
            Friday, 6 Elul (Sep 6) (Chayolim will not be able to use the points they earned from the majority of summer missions)
          </Radio>

          <Radio name='store_miles_reset' id='store_reset' value='0'
            oonChange={ this.onInputChange }
            checked={ store_reset_jd === 0 }>
            Never (This includes all miles from previous years) 
          </Radio>
          <br />

          <Radio name='store_miles_reset' onChange={ this.enableSchoolReset } value={ toJulian( moment() ) }
            checked={ store_reset_jd === toJulian( moment() ) }>
            Custom Date:
          </Radio>
          <br />

          <Date value={ store_reset }
            disabled = { this.state.disabled }
            onChange={ this.onDateChage } />

          {/* <Date value={ store_reset }
            disabled = { !store_reset }
            onChange={ this.onDateChage } /> */}
          
          {/* <Radio name='store_miles_reset' checked={ !store_reset } id='store_reset' onChange={ this.disableSchoolReset }>
            Never (Points will continue accumulating from last year)
          </Radio> */}
            
          {/* <Checkbox checked={ !store_reset }
              id='store_reset' onChange={ this.disableSchoolReset }>
            Allow soldiers to spend all their miles.
          </Checkbox> */}

          {/* <UncontrolledTooltip placement="top" target="store_reset" autohide={ false }>
            This includes all miles from previous years
          </UncontrolledTooltip> */}
        </Col>
      </Row>
    );
  }
}
