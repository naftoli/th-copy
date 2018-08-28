import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { ParentRow } from '../rows';
import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { Row, Col, Button, Input, TabPane } from 'reactstrap';
import { Select, PlatoonSelect, Checkbox } from 'components/inputs';
// functions
import { isBC } from 'functions/login';
import { findOption, missionTypeOptions } from 'functions/selects';
import { deleteSoldier, getSoldiers } from 'store/soldiers/operations';

class SettingsTab extends Component {
  // handle checkbox events
  handleCheckbox = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.checked ? 1 : 0 } );
  }
  // handle react-select change events
  handleSelectChange = ( id ) => ( option ) => {
    this.props.handleChange( { [id]: option && option.value } );
  }

  delete = () => {
    if ( confirm( 'Are you sure you want to delete this soldier?') ) {
      this.props.deleteSoldier( this.props.soldier.user_id )
      .then( () => this.props.getSoldiers() ) // refresh the main list
      .then( () => this.props.getSoldier() ); // refresh the single soldier
    }
  }

  // render the page
  render() {
    const { soldier, tabId, updated, onSubmit, onValidChange } = this.props;
    let { 
      user_id, class_id, school_id, chayolei, yan, chidon,
      allow_parent_tasks, print_parent_tasks, gender,
      school_type_id, lang_id, parentAccount, school
    } = soldier;
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
      <TabPane tabId = { tabId }>
        <Form id='SettingsTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>
          <Row>
            <Col xs='12' sm='6'>
              <label>Base</label>
              <Input disabled value={ school.school_name } />
            </Col>
            <Col xs='12' sm='6'>
              <label>Platoon</label>
              <PlatoonSelect schoolId={ school_id } value={ class_id } isClearable 
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
          
          <SaveButton show={ updated } />

          <p className='title'>Parent Account</p>
          <ParentRow parentAccount={parentAccount} userId={user_id} refresh={this.props.getSoldier}/> 
            
          { isBC( this.props.login.code ) &&
            <Row>
              <Col xs='12'>
                <p className='title'>Delete Soldier</p>
                <Button color="danger" onClick={ this.delete }>Delete Soldier</Button>
              </Col>
            </Row>
          }
        </Form>
        
      </TabPane>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
});

export default connect( mapStateToProps, { deleteSoldier, getSoldiers } )( SettingsTab );
