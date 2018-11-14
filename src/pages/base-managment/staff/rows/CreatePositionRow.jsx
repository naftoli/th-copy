import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { BaseSelect, PlatoonSelect } from 'components/inputs';
import { RoleSelect } from 'components/selects';
import { ButtonBar, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button } from 'reactstrap';
// functions
import classnames from 'classnames';
import { onInputChange, onSelectChange } from 'functions/events';

class CreatePositionRow extends Component {
  // props we expect
  static propTypes = {
    showCreateButton: PropTypes.bool,
    onCreate: PropTypes.func,
    onChange: PropTypes.func,
    login: PropTypes.object.isRequired
  }
  // default props
  static defaultProps = {
    showCreateButton: false,
    isAdmin: false
  }
  // initial state
  state = {
    auth: 'staff',    position: '',
    school_id: false, class_id: false,
  }
  // update the state and call onChange
  handleUpdates = updates => {
    this.setState(
      { ...updates }, 
      () => { this.props.onChange && this.props.onChange( this.getAuth() ) }
    );
  }
  // handle when a select is changed
  handleOptionChange = onSelectChange( this.handleUpdates );
  // handle when the target is blank
  handleInputChange = onInputChange( this.handleUpdates );

  getAuth = () => {
    let { school_id, class_id , ...auth } = this.state;

    if ( !school_id ) school_id = this.props.login.school_id;
    if ( !class_id ) class_id = this.props.login.class_id;

    auth.id = auth.auth === 'class' ? class_id : school_id;
    auth.admin_id = this.props.adminId;
    return auth;
  }

  create = () => this.props.onCreate( this.getAuth() );

  render() {
    const { showCreateButton, isAdmin, login } = this.props;
    let { auth, school_id, class_id, position } = this.state;

    const platoonClassnames = classnames('platoon', { 'hide': auth !== 'class' } );

    let baseSelect;

    if ( isAdmin ) 
      baseSelect = <BaseSelect value={ school_id } onChange={ this.handleOptionChange('school_id') } />;

    if ( !school_id )
      school_id = login.school_id;

    if ( !class_id )
      class_id = login.class_id;

    return (
      <div className='CreatePositionRow'>
        <Row>
          <Col xs={6}>
            <label>Role</label>
            <RoleSelect
              value={ auth } 
              onChange={ this.handleOptionChange('auth') } />
          </Col>
          <Col xs={6}>
            <label>Position</label>
            <Input name='position' value={ position } onChange={ this.handleInputChange } />
          </Col>
          { isAdmin && 
            <Col xs={6}>
              <label>Base</label> { baseSelect }
            </Col>
          }
          <Col xs={ isAdmin ? 6 : 12 } className={ platoonClassnames }>
            <label>Platoon</label>
            <PlatoonSelect schoolId={ school_id } value={ class_id }
              onChange={ this.handleOptionChange('class_id') } tabIndex={ auth !== 'class' ? '-1' : '0' } />
          </Col>
          
          {/* optional create button */}
          { showCreateButton && 
          <Col xs={12}>
            <ButtonBar>
              <Button color='primary' onClick={this.create}>
                <FontAwesome icon='save'/> Create Position
              </Button>
            </ButtonBar>
          </Col>
          }
        </Row>
      </div>
    );
  }
}

export default CreatePositionRow;
