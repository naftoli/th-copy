import React, { Component } from 'react';
// components
import { Row, Col } from 'reactstrap';
import { ParentRow } from '../rows';
import Select from 'react-select';
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
    this.props.handleChange( { [id]: option.value } );
  }
  // render the page
  render() {
    let { 
      user_id, class_id, school, chayolei, yan, chidon,
      allow_parent_tasks, print_parent_tasks, 
      school_type_id, gender, lang_id, parentAccount
    } = this.props.soldier;
    // get the list of platoons from the user for now ( TODO, replace with advanced platoon dropdown )
    const platoon_options = school.platoons.map( 
      platoon => ({ value: platoon.class_id, label: platoon.name})
    );
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
          <Col xs='12' sm='4'>
            <label>Platoon</label>
            <Select options={platoon_options} onChange={this.handleSelectChange('class_id')}
              value={findOption( platoon_options, class_id )} openMenuOnFocus />
          </Col>
          <Col xs='6' sm='4'>
            <label>Mission Type</label>
            <Select options={mission_type_options} onChange={this.handleSelectChange('school_type_id')}
              value={findOption( mission_type_options, school_type_id )} openMenuOnFocus/>
          </Col>
          <Col xs='6' sm='4'>
            <label>Language</label>
            <Select options={language_options} onChange={this.handleSelectChange('lang_id')}
              value={findOption( language_options, lang_id )} openMenuOnFocus/>
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

        { !!parentAccount && <ParentRow parentAccount={parentAccount} userId={user_id}/> }
        {/* <Row>
          <Col xs='12'>
            <p className='title'>Actions</p>
          </Col>
          <Col xs='12' style={{display: 'flex', justifyContent: 'space-around'}}>
            <Button color="danger" outline>Remove Soldier From School</Button>
            <Button color="danger">Delete Soldier</Button>
            <Button color="danger" outline>Remove From Parent Account</Button>
          </Col>
        </Row> */}
      </div>
    );
  }
}

export default SettingsTab;