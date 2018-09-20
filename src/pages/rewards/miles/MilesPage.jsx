import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Page404 } from 'pages/errors';
import { Row, Col, Input, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect, SoldierSelect } from 'components/inputs';
import { ButtonBar, InlineSync, Callout } from 'components/ui';
// functions
import API from 'api/api';
import { setTitle } from 'functions/utils';
import { isTeacher, isAdmin, isBC } from 'functions/login';
import { createNotifcation, updateNotifcation } from 'functions/notifications';
// style
import './miles.scss';

// API request used for this page
const updateMiles = data => {
  const toast_id = createNotifcation('Updating Miles...');
  API.post( '/rewards/miles?action=manual', data )
  .then( res => updateNotifcation( toast_id, 'Miles Updated', res.error, res.success ) )
  .catch( e => updateNotifcation( toast_id, '', e.message, false ));
}

class MilesPage extends Component {

  state = { 
    school_id: false,
    class_id: false,
    user_id: false,
    miles: 1
  };

  componentDidMount() {
    const { login } = this.props;
    setTitle( 'Add / Subtract Miles' );

    if ( isBC( login.code, true ) ) {
      this.setState({ school_id: login.id })
    } else if ( isTeacher( login.code ) ) {
      this.setState({ class_id: login.id })
    }
  }

  // event handlers
  onSelect = key => option =>
    this.setState({ [key]: option.value });
  onPlatoonChange = ({ value }) =>
    this.setState({ class_id: value, user_id: false });
  onNumberChange = ({ target }) => this.setState({ [target.name]: parseInt( target.value, 10 ) });

  // submit buttons
  addMiles = () => {
    updateMiles({ ...this.state, action: 'add', auction: true })
  }
  subtractMiles = auction => () => {
    updateMiles({ ...this.state, action: 'subtract', auction })
  }

  render() {
    let { login } = this.props;
    let { school_id, class_id, user_id, miles } = this.state;

    const showBase = isAdmin( login.code );
    const showPlatoon = !isTeacher( login.code );
    const disableActions = !class_id || !miles; // make sure a platoon is selected and miles are entered.

    if ( isTeacher( login.code ) )
      return <Page404 />;

    return (
      <div id='MilesPage'>
        <Callout title='Add / Subtract Miles'>
          <p>
            Award soldiers miles to spend in their store.
            <strong> Please note that this does not effect miles earned from Chayolei missions</strong>
          </p>
        </Callout>

        <Row>
          { showBase &&
            <Col sm={ 6 } xl={ 3 }>
              <label>Base</label>
              <BaseSelect
                value={ school_id }
                onChange={ this.onSelect('school_id') } />
            </Col>
          }
          { showPlatoon && 
            <Col sm={ showBase ? 6 : 12 } xl={ showBase ? 3 : 4 }>
              <label>Platoon</label>
              <PlatoonSelect
                value={ class_id }
                schoolId={ school_id }
                onChange={ this.onPlatoonChange } />
            </Col>
          }
          <Col sm={ 6 } xl={ showBase ? 3 : 4 }>
            <label>Soldier</label>
            <SoldierSelect
              showAllOption
              value={ user_id }
              classId={ class_id }
              onChange={ this.onSelect('user_id') } />
          </Col>
          <Col sm={ 6 } xl={ showBase ? 3 : 4 } >
            <label>Miles</label>
            <Input min='1' max='10000' 
              type='number' name='miles' 
              value={ miles } onChange={ this.onNumberChange }/>
            <div className='invalid-message'>1 - 10,000</div>
          </Col>
          {/* If we do not show the base and we show the platoon, fit the buttons inline */}
          <Col sm={ 12 }>
            <label>Actions</label>
            <ButtonBar>
              <Button color='primary' 
                onClick={ this.addMiles }
                disabled={ disableActions }>
                  <InlineSync icon='plus' /> Add
              </Button>
              <Button color='danger' 
                onClick={ this.subtractMiles( true ) }
                disabled={ disableActions }>
                  <InlineSync icon='minus' /> Subtract (Global)
              </Button>
              <Button color='danger' 
                onClick={ this.subtractMiles( false ) }
                disabled={ disableActions }>
                  <InlineSync icon='minus' /> Subtract (Store Only)
              </Button>
            </ButtonBar>
          </Col>
        </Row>
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

export default connect( mapStateToProps, mapDispatchToProps )( MilesPage );
