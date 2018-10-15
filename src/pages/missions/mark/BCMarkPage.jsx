import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Row, Col, Button, Input } from 'reactstrap';
import { InlineSync, FontAwesome, Callout } from 'components/ui';
import { BaseSelect, PlatoonSelect, SoldierSelect, ParshaSelect } from 'components/inputs';
// functions
import julian from 'julian';
import { isAdmin } from 'functions/login';
// style
import { LEGACY_URL } from 'components/constants';

class BCMarkPage extends Component {

  state = { 
    school_id: false, class_id: false,
    parsha_id: false, user_id: false,
    loading: false
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
    
    const today = parseInt( julian( new Date() ), 10 );
    // get the first week after the current week and select it
    const parsha = parshos.filter( parsha => parsha.end < today ).pop();
    this.setState({ parsha_id: parsha.id });
  }

  onSelectChange = key => option => this.setState({ [key]: option ? option.value : false });
  onPlatoonChange = option =>
    this.setState({
      class_id: option ? option.value : false,
      user_id: option.value ? false : this.state.user_id
    });

  selectSerial = ({ target }) => {
    if ( !target.value.match('77[0-9]{5}') )
      return false;
    // find the soldier
    const serial_number = parseInt( target.value, 10 );
    const soldier = this.props.soldiers.find( s => s.user_serial === serial_number );
    if ( !soldier )
      return false;
    // load his missions
    this.setState({
      user_id: soldier.user_id,
      class_id: soldier.class_id,
      school_id: soldier.school_id,
    }, this.loadMissions );
  }

  loadMissions = () => {
    if ( !this.state.parsha_id )
      return false;
    this.setState({ loading: true });
  }

  render() {
    let { school_id, class_id, user_id, parsha_id, loading } = this.state;
    const today = parseInt( julian( new Date() ), 10 );
    const { login } = this.props;

    return (
      <div id='BCMarkPage'>
        <Callout title='Mark Missions'>
          <p><strong>Load the</strong> missions to mark the soldiers missions inline in a mobile-frendly way.</p>
          <p>Press the <strong>Mark Printed Version</strong> button to use the old marking page and mark the sheets as printed.</p>
          <p>Enter a <strong>valid Serial Number</strong> (77XXXXX) in the serial number box below to auto-fill.</p>
        </Callout>

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
                onChange={ this.onSelectChange('user_id') } />
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
                  disabled={ !user_id || !parsha_id } >
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
    parshos: missions.parshos.parshos,
    soldiers: base.soldiers.soldiers
  }
};

const mapDispatchToProps = {};

export default connect( mapStateToProps, mapDispatchToProps )( BCMarkPage );
