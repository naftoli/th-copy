import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Select, BaseSelect, PlatoonSelect } from 'components/selects'
import { FontAwesome } from 'components/ui';
import { Row, Col, Input, ButtonGroup, Button } from 'reactstrap';
// functions
import classnames from 'classnames';
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
import { createAuth } from 'store/staff/operations';

class CreatePositionRow extends Component {

  state = {
    auth: 'staff',
    school_id: false,
    class_id: false,
    position: ''
  }

  handleOptionChange = id => option => this.setState({ [id]: option.value });
  handleInputChange = ({ target }) => this.setState({ [target.name]: target.value });

  create = () => {
    const { school_id, class_id , ...auth } = this.state;
    auth.id = auth.auth === 'class' ? class_id : school_id;
    auth.admin_id = this.props.adminId;
    this.props.createAuth( auth )
    .catch( error => toast.error( error.message ));
  }

  render() {
    const { auth, school_id, class_id, position } = this.state;

    const roleOptions = [
      { value: 'staff', label: 'Staff Member' },
      { value: 'class', label: 'Teacher' },
      { value: 'school', label: 'Base Commander' }
    ];
    const selectedRole = findOption( roleOptions, auth );
    const platoonClassnames = classnames('platoon', { 'hide': auth !== 'class' } );
    const positionClassnames = classnames('position', { 'expand': auth !== 'class' } );

    return (
      <div className='CreatePositionRow'>
        <Row>
          <Col xs={6}>
            <label>Role</label>
            <Select options={ roleOptions } value={ selectedRole } 
              onChange={ this.handleOptionChange('auth') } />
          </Col>
          <Col xs={6}>
            <label>Base</label>
            <BaseSelect value={ school_id } onChange={ this.handleOptionChange('school_id') } />
          </Col>
          <Col xs={6} className={platoonClassnames}>
            <label>Platoon</label>
            <PlatoonSelect schoolId={ school_id } value={ class_id }
              onChange={ this.handleOptionChange('class_id') } tabIndex={ auth !== 'class' ? '-1' : '0' } />
          </Col>
          <Col xs={6} className={positionClassnames}>
            <label>Position</label>
            <Input name='position' value={ position } onChange={ this.handleInputChange } />
          </Col>
          <Col xs={12}>
            <ButtonGroup>
              <Button color='primary' onClick={this.create}>
                <FontAwesome icon='save'/> Create Position
              </Button>
            </ButtonGroup>
          </Col>
        </Row>
      </div>
    );
  }
}

export default connect( null, { createAuth } )( CreatePositionRow );
