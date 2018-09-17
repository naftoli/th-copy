import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Row, Col, Input, Button, ButtonGroup } from 'reactstrap';
import { BaseSelect, PlatoonSelect, SoldierSelect } from 'components/inputs';
import { ButtonBar, InlineSync, FontAwesome, Callout } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { isTeacher } from 'functions/login';
import { setTitle } from 'functions/utils';
// style
import './miles.scss';

class MilesPage extends Component {

  state = { 
    school_id: false,
    class_id: false,
    user_id: false,
    miles: 1
  };

  componentDidMount() {
    setTitle( 'Add / Subtract Miles' );
  }

  // event handler
  onSelect = key => option => {
    this.setState({ [key]: option.value });
  }
  onChange = ({ target }) => this.setState({ [target.name]: target.value });

  render() {
    let { login } = this.props;
    const { school_id, class_id, user_id, miles } = this.state;

    const showBase = !isTeacher( login.code );

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
            <Col sm={ 6 } xl={ 4 }>
              <label>Base</label>
              <BaseSelect
                value={ school_id }
                onChange={ this.onSelect('school_id') } />
            </Col>
          }
          <Col sm={ 6 } xl={ showBase ? 4 : 6 }>
            <label>Platoon</label>
            <PlatoonSelect
              value={ class_id }
              schoolId={ school_id }
              onChange={ this.onSelect('class_id') } />
          </Col>
          <Col sm={ 6 } xl={ showBase ? 4 : 6 }>
            <label>Soldier</label>
            <SoldierSelect
              showAllOption
              value={ user_id }
              classId={ class_id }
              onChange={ this.onSelect('user_id') } />
          </Col>
          <Col sm={ 6 }>
            <label>Miles</label>
            <Input min='1' max='10000' 
              type='number' name='miles' 
              value={ miles } onChange={ this.onChange }/>
            <div className='invalid-message'>1 - 10,000</div>
          </Col>
          <Col sm={ showBase ? 12 : 6 } xl={ 6 }>
            <label>Actions</label>
            <ButtonGroup>
              <Button color='primary'>
                <FontAwesome icon='plus' /> Add
              </Button>
              <Button color='danger'>
                <FontAwesome icon='minus' /> Subtract
              </Button>
            </ButtonGroup>
          </Col>
        </Row>

        <pre>
          { JSON.stringify( this.state, null, 2 ) }
        </pre>
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
