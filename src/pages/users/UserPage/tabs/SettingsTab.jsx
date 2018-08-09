import React, { Component } from 'react';
// components
import { Row, Col, Button, Input } from 'reactstrap';
import { ParentRow } from '../rows';
import { Select, PlatoonSelect } from 'components/selects';
import Checkbox from 'components/ui/Checkbox';
// functions
import { findOption, missionTypeOptions } from 'functions/selects';

class SettingsTab extends Component {
  // handle checkbox events
  handleCheckbox = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.checked ? 1 : 0 } );
  }
  // handle react-select change events
  handleSelectChange = ( id ) => ( option ) => {
    this.props.handleChange( { [id]: option && option.value } );
  }
  // render the page
  render() {
    let { 
      user_id, class_id, school_id, chayolei, yan, chidon,
      allow_parent_tasks, print_parent_tasks, gender,
      school_type_id, lang_id, parentAccount, school
    } = this.props.soldier;
    // mission_type_options
    const mission_type_options = missionTypeOptions( gender );
    // language options
    const language_options = [
      { value: 1, label: 'English' },
      { value: 2, label: 'Yiddish' },
      { value: 3, label: 'French' }
    ];
    // render the settings tab
    return (
      <div id='SettingsTab'>
        <Row>
          <Col xs='12' sm='6'>
            <label>Base</label>
            <Input disabled value={ school.school_name } />
          </Col>
          <Col xs='12' sm='6'>
            <label>Platoon</label>
            <PlatoonSelect school_id={ school_id } value={ class_id } isClearable 
              onChange={this.handleSelectChange('class_id')} />
          </Col>
          <Col xs='6'>
            <label>Mission Type</label>
            <Select options={mission_type_options} onChange={this.handleSelectChange('school_type_id')}
              value={findOption( mission_type_options, school_type_id )} />
          </Col>
          <Col xs='6'>
            <label>Language</label>
            <Select options={language_options} onChange={this.handleSelectChange('lang_id')}
              value={findOption( language_options, lang_id )} />
          </Col>
        </Row>
        <Row>
          <Col xs='12' sm='6'>
            <label>Enrolled in:</label><br/>
            <Checkbox checked={!!chayolei} id='chayolei' onChange={this.handleCheckbox}>
              Chayolei
            </Checkbox>
            <Checkbox checked={!!chidon} id='chidon' onChange={this.handleCheckbox}>
              Chidon
            </Checkbox>
            <Checkbox checked={!!yan} id='yan' onChange={this.handleCheckbox}>
              Tanya
            </Checkbox>
          </Col>
          <Col xs='12' sm='6'>
            <label>Custom Parent Tasks</label><br/>
            <Checkbox checked={!!allow_parent_tasks} id='allow_parent_tasks' onChange={this.handleCheckbox}>
              Allow
            </Checkbox>
            <Checkbox checked={!!print_parent_tasks} id='print_parent_tasks' onChange={this.handleCheckbox}>
              Print on Mission Sheets
            </Checkbox>
          </Col>
        </Row>
        
        <p className='title'>Parent Account</p>
        <ParentRow parentAccount={parentAccount} userId={user_id} refresh={this.props.getSoldier}/> 
        
        <p className='title'>Delete Soldier</p>
        <Row>
          <Col xs='12'>
            <Button color="danger">Delete Soldier</Button>
          </Col>
        </Row>
      </div>
    );
  }
}

export default SettingsTab;