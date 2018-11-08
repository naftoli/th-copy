import React, { Component } from 'react';
// components
import { FontAwesome } from 'components/ui';
import { Row, Col, UncontrolledTooltip } from 'reactstrap';
import { Checkbox, Toggle, Radio, Label } from 'components/inputs';

export class SettingsRow extends Component {

  render() {
    const { inputProps, checkProps } = this.props;
    const { 
      allow_parent_tasks, whatsapp,
      print_parent_tasks, class_gender
    } = this.props.platoon;

    return (
      <Row>
        <Col sm='6' xl='3'>
          <Label>Show on Whatsapp Reports</Label>
          <Toggle
            name='whatsapp'
            { ...checkProps }
            checked={ !!whatsapp } />
        </Col>
        
        <Col sm='6' xl='3'>
          <Label>Class Gender</Label>
          <Radio 
            value='M'
            { ...inputProps }
            name='class_gender'
            checked={ class_gender === 'M' }>

            Boys <FontAwesome icon='male' />
          </Radio>

          <Radio
            value='F'
            { ...inputProps }
            name='class_gender'
            checked={ class_gender === 'F' }>

            Girls <FontAwesome icon='female' />
          </Radio>
        </Col>

        <Col sm='6' xl='6'>
          <Label id='customize'>Custom Parent Tasks</Label>
          <UncontrolledTooltip placement="top" target="customize" autohide={ false }>
            Allow parents to create completely custom tasks for this soldier.
            Custom tasks are worth 0.5 miles per day/week
          </UncontrolledTooltip>

          <Checkbox
            { ...checkProps }
            name='allow_parent_tasks'
            checked={!!allow_parent_tasks }>

            Allow
          </Checkbox>

          <Checkbox { ...checkProps }
            name='print_parent_tasks'
            checked={!!print_parent_tasks }>

            Print on Mission Sheets
          </Checkbox>
        </Col>
      </Row>
    );
  }
}
