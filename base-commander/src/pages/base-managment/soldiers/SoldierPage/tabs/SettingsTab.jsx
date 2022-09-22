import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { ParentRow } from '../../components';
import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { Select, Label, Radio, Checkbox, MissionTypeSelect } from 'components/inputs';
import { Row, Col, Button, TabPane, UncontrolledTooltip } from 'reactstrap';
// functions
import { isBC } from 'functions/login';
import { findOption } from 'functions/selects';
import { FontAwesome } from 'components/ui/Icons';
import { onCheckboxChange, onSelectChange, onJSONChange } from 'functions/events';
// data
import { allMissionLanguages } from 'data/languages.json'

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
  // handle input events
  onInputChange = onJSONChange( this.props.handleChange );
  // handle checkbox events
  onCheckboxChange = onCheckboxChange( this.props.handleChange );
  // handle react-select change events
  onSelectChange = onSelectChange( this.props.handleChange )
  // delete a soldier
  delete = () => {
    if ( window.confirm( 'Are you sure you want to remove / delete this soldier?') )
      this.props.deleteSoldier( this.props.soldier.user_id );
  }

  updateBirthday = () => {
    this.props.updateBirthday( this.props.soldier.user_id );
  }

  // render the page
  render() {
    const { 
      soldier,  tabId,  updated,  login,
      onSubmit, saving, onValidChange 
    } = this.props;
    let { 
      user_id,  chayolei, yan,  chidon, lang_id,  pic_mission_type,
      parentAccount,  allow_parent_tasks, print_parent_tasks,
    } = soldier;
    const checkboxProps = {
      onChange: this.onCheckboxChange
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
                options={allMissionLanguages}
                onChange={ this.onSelectChange('lang_id') }
                value={ findOption( allMissionLanguages, lang_id ) } />
            </Col>
          </Row>

          <Row className='enrollment'>
            <Col xs='12' sm='6' xl={4}>
              <label id='enrolled'>Enrolled in: <i class="fas fa-info-circle" id="enrollment_comment"></i></label>
              <UncontrolledTooltip placement="top" target="enrollment_comment" autohide={ false }>
                Control what this soldier is enrolled in.
                <strong>Warning: This overrides any registration</strong>
              </UncontrolledTooltip>

              { login.modules.chayolei && 
                <Checkbox name='chayolei'
                    { ...checkboxProps }
                    checked={!!chayolei}>
                  Chayolei Tzivos Hashem (CTH)
                </Checkbox>
              }
              { login.modules.chidon && 
                <Checkbox name='chidon' 
                    checked={!!chidon}
                    { ...checkboxProps }>
                  Chidon
                </Checkbox>
              }
              { login.modules.tanya && 
                <Checkbox name='yan'
                    checked={!!yan}
                    { ...checkboxProps }>
                  Tanya
                </Checkbox>
              }
            </Col>

            <Col xs='12' sm='6' xl={4}>
              <label id='customize'>Custom Parent Tasks <i class="fas fa-info-circle" id="parent_comment"></i></label>
              <UncontrolledTooltip placement="top" target="parent_comment" autohide={ false }>
                Allow parents to create completely custom tasks for this soldier.
                Custom tasks are worth 0.5 miles per day/week
              </UncontrolledTooltip>

              <Checkbox
                  { ...checkboxProps }
                  name='allow_parent_tasks'
                  checked={!!allow_parent_tasks}>
                Allow
              </Checkbox>

              <Checkbox
                  { ...checkboxProps }
                  name='print_parent_tasks'
                  checked={!!print_parent_tasks} >
                Print on Mission Sheets
              </Checkbox>
            </Col>

            <Col xs={12} sm={6} xl={4}>
              <Label>Mission Sheet Type <i class="fas fa-info-circle" id="mission_comment"></i></Label>
              <UncontrolledTooltip placement="top" target="mission_comment" autohide={ false }>
                Determines whether to show pictures on the mission sheets or not
              </UncontrolledTooltip>

              <Radio value='1'
                  name='pic_mission_type'
                  onChange={ this.onInputChange }
                  checked={ pic_mission_type === 1 } >
                No Pictures
              </Radio>

              <Radio value='2'
                  name='pic_mission_type'
                  onChange={ this.onInputChange }
                  checked={ pic_mission_type === 2 }>
                Small Pictures
              </Radio>

              <Radio value='4'
                     name='pic_mission_type'
                     onChange={ this.onInputChange }
                     checked={ pic_mission_type === 4 }>
                Day School Missions
              </Radio>
            </Col>
          </Row>
          
          <SaveButton
            show={ updated }
            saving={ saving } 
            disabled={ saving } />

          <p className='title'>Connected Parent Account</p>
          <ParentRow
            userId={ user_id }
            parentAccount={ parentAccount }
            createAuth={ this.props.createAuth }
            removeAuth={ this.props.removeAuth } /> 
            
          { isBC( login.code ) &&
          <div>
            <Row>
              <Col xs='12'>
                <p className='title'>Remove Soldier from School (Deletes from system if soldier has no points)</p>
                <Button color="danger" onClick={ this.delete }>
                  <FontAwesome icon='trash' /> Remove Soldier
                </Button>
              </Col>
            </Row>

            <Row>
              <Col xs='12'>
                <p className='title'>Update Soldier's Birthday Missions</p>
                <Button color="info" onClick={ this.updateBirthday }>
                  <FontAwesome icon='user-edit' /> Update Birthday Missions
                </Button>
              </Col>
            </Row>
          </div>
          }
        </Form>
        
      </TabPane>
    );
  }
}

export default SettingsTab;
