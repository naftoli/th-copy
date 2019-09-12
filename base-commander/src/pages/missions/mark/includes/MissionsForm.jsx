import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Row, Col, Button, Input } from 'reactstrap';
import { InlineSync, FontAwesome } from 'components/ui';
import { BaseSelect, PlatoonSelect, SoldierSelect, ParshaSelect } from 'components/inputs';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { julianToday } from 'functions/dates';
// store and constants
import { getMissions } from 'store/missions/mark/operations';
import { LEGACY_URL } from 'components/constants';

class MissionsForm extends Component {

  state = { 
    school_id: false, class_id: false,
    parsha_id: false
  };

  componentDidMount() {
    // set the default school id and class id
    const { school_id } = this.props.login;
    this.setState({ school_id });
    // set the default parsha
    this.setDefaultParsha();
  }

  componentDidUpdate( prevProps ) {
    if ( prevProps.parshos !== this.props.parshos )
      this.setDefaultParsha();
  }

  setDefaultParsha() {
    const { parshos } = this.props;
    
    if ( parshos.length === 0 )
      return false;
    
    const today = julianToday();
    // get the first week after the current week and select it
    const parsha = parshos.filter( parsha => parsha.end < today ).pop();
    this.setState({ parsha_id: parsha.id });
  }

  onSelectChange = key => option => this.setState({ [key]: option ? option.value : false });
  onPlatoonChange = option => {
    this.setState({ class_id: option ? option.value : false });
    if ( option && option.value ) this.props.onSoldierChange( false );
  }
  onSoldierChange = option => {
    if ( option && option.value ) this.props.onSoldierChange( option.value );
  }
  
  selectSerial = ({ target }) => {
    if ( !target.value.match('7[0-9]{6}') )
      return false;
    // find the soldier
    const serial_number = parseInt( target.value, 10 );
    const soldier = this.props.soldiers.find(
      s => s.user_serial === serial_number
    );
    if ( !soldier )
      return false;
    // load his missions
    this.props.onSoldierChange( soldier.user_id );
    this.setState({
      class_id: soldier.class_id,
      school_id: soldier.school_id,
    }, this.loadMissions );
  }

  loadMissions = () => {
    if ( !this.props.loading )
      this.props.getMissions( this.props.user_id, this.state.parsha_id )
      .catch( e => toast.error( e.message ) );
  }

  render() {
    let { school_id, class_id, parsha_id } = this.state;
    const today = julianToday();
    const { login, loading, user_id } = this.props;

    return (
      <div id='BCMissionsForm'>

        <Row>
          <Col>
            <label>Serial Number (Quick Select)</label>
            <Input type='text' pattern='77[0-9]{5}' autoFocus
              onChange={ this.selectSerial } />
            <div className='invalid-message'>Please enter a valid serial number (e.g. 7765904)</div>
          </Col>
        </Row>

        <hr/>

        <form target='_blank' method='post' action={`${LEGACY_URL}/api/print/mark`}>
          <Row>
            <Col sm={6}>
              <label>Base</label>
              <BaseSelect
                name='school_id'
                value={ school_id }
                isDisabled={ !isAdmin( login.code ) }
                onChange={ this.onSelectChange('school_id') } />
              <input type='hidden'  value={ school_id || '' } name='school_id' />
            </Col>

            <Col sm={6}>
              <label>Platoon</label>
              <PlatoonSelect
                isClearable
                value={ class_id }
                schoolId={ school_id }
                openMenuOnFocus={ false }
                onChange={ this.onPlatoonChange } />
              <input type='hidden' value={ class_id || '' } name='class_id' />
            </Col>

            <Col sm={6}>
              <label>Soldier</label>
              <SoldierSelect
                registeredOnly
                value={ user_id }
                classId={ class_id }
                schoolId={ school_id }
                openMenuOnFocus={ false }
                onChange={ this.onSoldierChange } />
              <input type='hidden' value={ user_id || '' } name='user_id' />
            </Col>

            <Col sm={6}>
              <label>Parsha</label>
              <ParshaSelect 
                isClearable
                isDescending
                value={ parsha_id }
                endDate={ today }
                onChange={ this.onSelectChange('parsha_id') } />
              <input type='hidden' value={ parsha_id || '' } name='parsha_id'/>
            </Col>
          </Row>

          <Row className='buttons'>
            <Col sm={6}>
              <Button color='primary' onClick={ this.loadMissions }
                  disabled={ !user_id || !parsha_id || loading } >
                <InlineSync loading={ loading } /> Load Missions
              </Button>
            </Col>

            <Col sm={6}>
              <Button color='primary' disabled={ !user_id || !parsha_id }>
                <FontAwesome icon='print'/> Mark Printed Version
              </Button>
            </Col>
          </Row>
        </form>
      </div>
    );
  }
}

const mapStateToProps = ({ login, missions, base }) => {
  return {
    login: login.current_login,
    loading: missions.mark.loading,
    soldiers: base.soldiers.soldiers,
    parshos: missions.parshos.parshos,
  }
};

const mapDispatchToProps = { getMissions };

export default connect( mapStateToProps, mapDispatchToProps )( MissionsForm );
