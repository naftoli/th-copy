import React, { Component } from 'react';
import { Row, Col } from 'reactstrap';
import Select from 'react-select';
import Checkbox from 'components/ui/Checkbox';

class SettingsTab extends Component {
  // format the data for the UserPage component
  handleCheckbox = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.checked ? 1 : 0 } );
  }
  
  handleSelectChange = ( id ) => ( option ) => {
    this.props.handleChange( { [id]: option.value } );
  }

  render() {
    let { 
      class_id, school, chayolei, yan, chidon,
      allow_parent_tasks, print_parent_tasks, 
      school_type_id, gender, lang_id
    } = this.props.soldier;

    const findOption = ( options, value ) => options.find( option => option.value === value );

    // get the list of platoons from the user for now ( TODO, replace with advanced platoon dropdown )
    const platoon_options = school.platoons.map( 
      platoon => ({ value: platoon.class_id, label: platoon.name})
    );
    // generate the mission_type options
    let mission_type_options = [
      { value: 2, label: 'Chabad' },
      { value: 12, label: 'Frum' }
    ];
    if ( gender === 'F' ) {
      mission_type_options = mission_type_options.map( 
        option => Object.assign( {}, option, { value: option.value + 1 } )
      );
    }
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
              value={findOption( platoon_options, class_id )} />
          </Col>
          <Col xs='6' sm='4'>
            <label>Mission Type</label>
            <Select options={mission_type_options} onChange={this.handleSelectChange('school_type_id')}
              value={findOption( mission_type_options, school_type_id )}/>
          </Col>
          <Col xs='6' sm='4'>
            <label>Language</label>
            <Select options={language_options} onChange={this.handleSelectChange('lang_id')}
              value={findOption( language_options, lang_id )}/>
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
              WWTC
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
      </div>
    );
  }
}

export default SettingsTab;