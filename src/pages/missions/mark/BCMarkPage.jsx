import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { InlineSync, FontAwesome } from 'components/ui';
import { Row, Col, Button } from 'reactstrap';
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
    const parsha = parshos.find( parsha => today >= parsha.start && today <= parsha.end );
    this.setState({ parsha_id: parsha.id });
  }

  onSelectChange = key => option => this.setState({ [key]: option ? option.value : false });

  loadMissions = () => {
    debugger;
  }

  render() {
    let { school_id, class_id, user_id, parsha_id } = this.state;
    const today = parseInt( julian( new Date() ), 10 );
    const { login } = this.props;

    return (
      <div id='BCMarkPage'>
        <form target='_blank' method='post' action={`${LEGACY_URL}/api/print/mark`}>
          <Row>
            <Col sm={6}>
              <label>Base</label>
              <BaseSelect
                isClearable
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
                onChange={ this.onSelectChange('class_id') } />
              <input type='hidden' value={ class_id || '' } name='class_id' />
            </Col>

            <Col sm={6}>
              <label>Soldier</label>
              <SoldierSelect
                isClearable
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
                value={ parsha_id }
                endDate={ today }
                onChange={ this.onSelectChange('parsha_id') } />
              <input type='hidden' value={ parsha_id || '' } name='parsha_id'/>
            </Col>
          </Row>

          <Row className='buttons'>
            <Col sm={6}>
              <Button color='primary' onClick={ this.loadMissions }>
                <InlineSync /> Load Missions
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

const mapStateToProps = ({ login, missions }) => {
  return {
    login: login.current_login,
    parshos: missions.parshos.parshos
  }
};

const mapDispatchToProps = {};

export default connect( mapStateToProps, mapDispatchToProps )( BCMarkPage );
