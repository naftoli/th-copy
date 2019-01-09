import React, { Component } from 'react';
// components
import { FontAwesome } from 'components/ui';
import { Row, Col, UncontrolledTooltip } from 'reactstrap';
import { Checkbox, Toggle, Radio, Label } from 'components/inputs';

export class SettingsRow extends Component {

  render() {
    const { inputProps, checkProps, onJSONChange } = this.props;
    const { 
      pic_mission_type,   allow_parent_tasks,
      print_parent_tasks, class_gender,   whatsapp,
    } = this.props.platoon;

    return (
      <Row>
        <Col sm={6} xl={3}>
          <Label>Show on WWTC Reports</Label>
          <Toggle
            name='whatsapp'
            { ...checkProps }
            checked={ !!whatsapp } />
        </Col>
        
        <Col sm={6} xl={3}>
          <Label>Class Gender</Label>
          <Radio
            required
            value='m'
            { ...inputProps }
            name='class_gender'
            checked={ class_gender === 'm' }>

            Boys <FontAwesome icon='male' />
          </Radio>

          <Radio
            value='f'
            { ...inputProps }
            name='class_gender'
            checked={ class_gender === 'f' }>

            Girls <FontAwesome icon='female' />
          </Radio>
        </Col>

        <Col sm={6} xl={3}>
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

        <Col sm={6} xl={3}>
          <Label>Mission Sheet Type</Label>
          <Radio value='1'
              name='pic_mission_type'
              onChange={ onJSONChange }
              checked={ pic_mission_type === 1 } >
            No Pictures
          </Radio>

          <Radio value='2'
              name='pic_mission_type'
              onChange={ onJSONChange }
              checked={ pic_mission_type === 2 }>
            Small Pictures
          </Radio>
        </Col>
      </Row>
    );
  }
}
