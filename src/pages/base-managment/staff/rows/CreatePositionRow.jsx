import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { Select, BaseSelect, PlatoonSelect } from 'components/inputs'
import { ButtonBar, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button } from 'reactstrap';
// functions
import classnames from 'classnames';
import { findOption } from 'functions/selects';

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
  handleOptionChange = id => option => this.handleUpdates({ [id]: option.value });
  // handle when the target is blank
  handleInputChange = ({ target }) => this.handleUpdates({ [target.name]: target.value });

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
    const { showCreateButton, isAdmin } = this.props;
    const { auth, school_id, class_id, position } = this.state;

    const roleOptions = [
      { value: 'staff', label: 'Staff Member' },
      { value: 'class', label: 'Teacher' },
      { value: 'school', label: 'Base Commander' }
    ];
    const selectedRole = findOption( roleOptions, auth );
    const platoonClassnames = classnames('platoon', { 'hide': auth !== 'class' } );

    let baseSelect;
    if ( isAdmin ) 
      baseSelect = <BaseSelect value={ school_id } onChange={ this.handleOptionChange('school_id') } />;

    return (
      <div className='CreatePositionRow'>
        <Row>
          <Col xs={6}>
            <label>Role</label>
            <Select options={ roleOptions } value={ selectedRole } 
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
          <Col xs={ isAdmin ? 6 : 12 } className={platoonClassnames}>
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
