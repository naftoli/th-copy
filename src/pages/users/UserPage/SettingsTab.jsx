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
    let { class_id, school, chayolei, yan, chidon } = this.props.soldier;
    // get the list of platoons from the user for now ( TODO, replace with advanced platoon dropdown )
    const platoon_options = school.platoons.map( 
      platoon => ({ value: platoon.class_id, label: platoon.name})
    );
    const selected_platoon = platoon_options.find( option => option.value === class_id );

    const mission_type_options = [
      { value: 2, label: 'Chabad' },
      { value: 3, label: 'Frum' }
    ];

    const language_options = [
      { value: 1, label: 'English' },
      { value: 2, label: 'Yiddish' },
      { value: 3, label: 'French' }
    ];

    return (
      <div id='settings-tab'>
        <Row>
          <Col xs='12' sm='4'>
            <label>Platoon</label>
            <Select options={ platoon_options } value={ selected_platoon } 
              onChange={ this.handleSelectChange('class_id') } />
          </Col>
          <Col xs='6' sm='4'>
            <label>Mission Type</label>
            <Select options={ mission_type_options } />
          </Col>
          <Col xs='6' sm='4'>
            <label>Language</label>
            <Select options={ language_options } />
          </Col>
        </Row>
        <Row>
          <Col xs='12' sm='6'>
            <label>Enrolled in:</label><br/>
            <Checkbox checked={ !!chayolei } id='chayolei' onChange={ this.handleCheckbox }>
              Chayolei
            </Checkbox>
            <Checkbox checked={ !!chidon } id='chidon' onChange={ this.handleCheckbox }>
              Chidon
            </Checkbox>
            <Checkbox checked={ !!yan } id='yan' onChange={ this.handleCheckbox }>
              WWTC
            </Checkbox>
          </Col>
        </Row>
      </div>
    );
  }
}

export default SettingsTab;