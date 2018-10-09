import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Callout, FontAwesome } from 'components/ui';
import { Row, Col, Button, Input, UncontrolledTooltip } from 'reactstrap';
import { 
  Select, Radio,
  BaseSelect, PlatoonSelect, SoldierSelect 
} from 'components/inputs';
// functions
import { setTitle } from 'functions/utils';
import { isAdmin, isBC } from 'functions/login';
// style
import './PrintPage.scss';
import { LEGACY_URL } from 'components/constants';

class PrintPage extends Component {

  state = { 
    school_id: false,   class_ids: [],
    user_ids: [],       parsha_ids: [],
    double_sided: true, dates: 'hebrew'
  };

  componentDidMount() {
    const { school_id, class_id } = this.props.login;
    setTitle( 'Print Missions' );

    this.setState({ school_id });
    if ( class_id ) this.setState({ class_ids: [ class_id ] });
  }

  showInstructions = () => {
    window.open(
      `${ LEGACY_URL }/mission_report/instructions/`, '_blank', 
      'width=770, height=700, menubar=no, scrollbars=yes, status=no, toolbar=no, titlebar=no'
    );
  }

  schoolChange = ({ value }) => this.setState({ school_id: value });
  platoonChange = ( values ) => this.setState({ class_ids: values.map( val => val.value ) });
  soldierChange = ( values ) => this.setState({ user_ids: values.map( val => val.value ) });

  toggleDates = ( e ) => this.setState({ dates: e.target.value });
  toggleDoubleSided = ( e ) => this.setState({ double_sided: JSON.parse( e.target.value ) });

  render() {
    let { 
      school_id, class_ids, user_ids, 
      parsha_ids, double_sided, dates
    } = this.state;
    const { login } = this.props;

    return (
      <div id='PrintPage'>
        <Callout title='Print Missions'>
          <p><strong>Please check the printing instructions below before printing anything.</strong></p>
          <p>
            For performance reasons, printing missions will open in a new tab.
            It may take a while if you are printing missions for many soldiers.{' '}
            <strong>Please be paitient and wait for the pages to finish generating/loading.</strong>
          </p>
        </Callout>

        <form target='_blank' method='post' action={`${LEGACY_URL}/api/print/missions`}>
          <Row>
            <Col sm={6}>
              <label>Base</label>
              <BaseSelect name='school_id' isDisabled={ !isAdmin( login.code ) }
                value={ school_id } onChange={ this.schoolChange} />
            </Col>

            <Col sm={6}>
              <label>Platoon(s)</label>
              <PlatoonSelect isMulti isDisabled={ !isBC( login.code ) }
                values={ class_ids } placeholder='All Platoons'
                schoolId={ school_id } onChange={ this.platoonChange } />
              <input type='hidden' value={ class_ids } name='class_ids' />
            </Col>

            <Col sm={6}>
              <label>Soldier(s)</label>
              <SoldierSelect values={ user_ids } isMulti
                schoolId={ school_id } classIds={ class_ids } 
                onChange={ this.soldierChange } openMenuOnFocus={ false } placeholder='All Soldiers' />
              <input type='hidden' value={ user_ids } name='user_ids' />
            </Col>

            <Col sm={6}>
              <label>Parsha(s)</label>
              <Select />
              <input type='hidden' value={ parsha_ids } name='parsha_ids'/>
            </Col>
          </Row>

          <Row>
            <Col sm={6} xl={ 3 }>
              <label>Double sided</label><br/>
              <Radio name='double_sided' checked={ double_sided } value={ true } onChange={ this.toggleDoubleSided }>
                I <strong>am</strong> printing double sided copies.
              </Radio><br/>
              <Radio name='double_sided' checked={ !double_sided } value={ false } onChange={ this.toggleDoubleSided }>
                I am <strong>not</strong> printing double sided copies.
              </Radio>
            </Col>

            <Col sm={6} xl={ 3 }>
              <label>Dates</label><br/>
              <Radio checked={ dates == 'none' } value='none' onChange={ this.toggleDates }>
                <strong>No</strong> dates.
              </Radio>
              <Radio checked={ dates == 'hebrew' } value='hebrew' onChange={ this.toggleDates }>
                <strong>Hebrew</strong> dates.
              </Radio><br/>
              <Radio checked={ dates == 'english' } value='english' onChange={ this.toggleDates }>
                <strong>Hebrew and English</strong> dates.
              </Radio>
            </Col>

            <Col sm={12} xl={ 6 }>
              <label id='pages'>Number of pages</label>
              <UncontrolledTooltip placement="top" target="pages" autohide={ false }>
                <strong>If entered,</strong> the print generator will generate blank pages to make sure that each soldier has this number of pages.<br/>
                <strong>Additional Missions beyond this limit will not print.</strong>
              </UncontrolledTooltip>
              <Input name='pages' type='number' min={ double_sided ? 2 : 1 } step={ double_sided ? 2 : 1 }/>
            </Col>
          </Row>


          <Row className='buttons'>
            <Col sm={6}>
              <Button color='primary' onClick={ this.showInstructions }>
                <FontAwesome icon='clipboard-list'/> Printing Instructions
              </Button>
            </Col>

            <Col sm={6}>
              <Button color='primary'>
                <FontAwesome icon='print'/> Print
              </Button>
            </Col>
          </Row>
        </form>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
};

const mapDispatchToProps = {};

export default connect( mapStateToProps, mapDispatchToProps )( PrintPage );
