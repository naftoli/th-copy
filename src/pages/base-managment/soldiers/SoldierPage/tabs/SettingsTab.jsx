import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { ParentRow } from '../../components';
import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { Select, Checkbox, MissionTypeSelect } from 'components/inputs';
import { Row, Col, Button, TabPane, UncontrolledTooltip } from 'reactstrap';
// functions
import { isBC } from 'functions/login';
import { findOption } from 'functions/selects';
import { FontAwesome } from 'components/ui/Icons';

class SettingsTab extends Component {
  // props we are expecting for this component
  static propTypes = {
    login: PropTypes.object,
    soldier: PropTypes.object,
    createAuth: PropTypes.func.isRequired,
    removeAuth: PropTypes.func.isRequired,
    handleChange: PropTypes.func.isRequired,
    deleteSoldier: PropTypes.func.isRequired,
  }
  // handle checkbox events
  handleCheckbox = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.checked ? 1 : 0 } );
  }
  // handle react-select change events
  onSelectChange = ( id ) => ( option ) => {
    this.props.handleChange( { [id]: option && option.value } );
  }
  // delete a soldier
  delete = () => {
    if ( window.confirm( 'Are you sure you want to delete this soldier?') )
      this.props.deleteSoldier( this.props.soldier.user_id );
  }

  // render the page
  render() {
    const { soldier, tabId, updated, onSubmit, onValidChange } = this.props;
    let { 
      user_id,  chayolei, yan,  chidon, lang_id,
      parentAccount,  allow_parent_tasks, print_parent_tasks,
    } = soldier;
    // language options
    const language_options = [
      { value: 1, label: 'English' },
      { value: 2, label: 'Yiddish' },
      { value: 3, label: 'French' }
    ];
    const checkboxProps = {
      onChange: this.handleCheckbox
    }
    // render the settings tab
    return (
      <TabPane tabId = { tabId }>
        <Form id='SettingsTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>

          <p className='title'>Mission Settings</p>
          <Row>
            <Col xs='6'>
              <label htmlFor='mission_type'>Mission Type</label>
              <MissionTypeSelect
                required id='mission_type'
                gender={ soldier.gender }
                value={ soldier.school_type_id }
                onChange={ this.onSelectChange( 'school_type_id' ) } />
            </Col>
            <Col xs='6'>
              <label htmlFor='mission_lang'>Mission Language</label>
              <Select
                required id='mission_lang'
                options={language_options}
                onChange={ this.onSelectChange('lang_id') }
                value={ findOption( language_options, lang_id ) } />
            </Col>
          </Row>

          <Row className='enrollment'>
            <Col xs='12' sm='6'>
              <label id='enrolled'>Enrolled in:</label>
              <UncontrolledTooltip placement="top" target="enrolled" autohide={ false }>
                Control what this soldier is enrolled in.
                <strong>Warning: This overrides any registration</strong>
              </UncontrolledTooltip>

              <Checkbox checked={!!chayolei} id='chayolei' { ...checkboxProps }>
                Chayolei Tzivos Hashem (CTH)
              </Checkbox>

              <Checkbox checked={!!chidon} id='chidon' { ...checkboxProps }>
                Chidon
              </Checkbox>

              <Checkbox checked={!!yan} id='yan' { ...checkboxProps }>
                Tanya
              </Checkbox>
            </Col>

            <Col xs='12' sm='6'>
              <label id='customize'>Custom Parent Tasks</label>
              <UncontrolledTooltip placement="top" target="customize" autohide={ false }>
                Allow parents to create completely custom tasks for this soldier.
                Custom tasks are worth 0.5 miles per day/week
              </UncontrolledTooltip>

              <Checkbox checked={!!allow_parent_tasks} id='allow_parent_tasks' { ...checkboxProps }>
                Allow
              </Checkbox>

              <Checkbox checked={!!print_parent_tasks} id='print_parent_tasks' { ...checkboxProps }>
                Print on Mission Sheets
              </Checkbox>
            </Col>
          </Row>
          
          <SaveButton show={ updated } />

          <p className='title'>Connected Parent Account</p>
          <ParentRow
            userId={ user_id }
            parentAccount={ parentAccount }
            createAuth={ this.props.createAuth }
            removeAuth={ this.props.removeAuth } /> 
            
          { isBC( this.props.login.code ) &&
            <Row>
              <Col xs='12'>
                <p className='title'>Delete Soldier</p>
                <Button color="danger" onClick={ this.delete }>
                  <FontAwesome icon='trash' /> Delete Soldier
                </Button>
              </Col>
            </Row>
          }
        </Form>
        
      </TabPane>
    );
  }
}

export default SettingsTab;
