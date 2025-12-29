import React, { useState } from 'react';
import PropTypes from 'prop-types';
// components
import { BaseSelect, PlatoonSelect } from 'components/inputs';
import { RoleSelect } from 'components/selects';
import { ButtonBar, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button } from 'reactstrap';
// functions
import classnames from 'classnames';
import { onInputChange, onSelectChange } from 'functions/events';

const CreatePositionRow = ({
  showCreateButton = false,
  onCreate,
  onChange,
  login,
  isAdmin = false,
  adminId
}) => {
  const [state, setState] = useState({
    auth: 'staff',
    position: '',
    school_id: false,
    class_id: false,
  });
  let { auth, school_id, class_id, position } = state;

  const getAuth = (currentState = state) => {
    let { school_id, class_id, ...authData } = currentState;
    let currentSchoolId = school_id;
    let currentClassId = class_id;

    if (!currentSchoolId) currentSchoolId = login.school_id;
    if (!currentClassId) currentClassId = login.class_id;

    authData.id = authData.auth === 'class' ? currentClassId : currentSchoolId;
    authData.admin_id = adminId;
    return authData;
  }

  // update the state and call onChange
  const handleUpdates = (updates) => {
    setState(prev => {
      const newState = { ...prev, ...updates };
      if (onChange) {
        onChange(getAuth(newState));
      }
      return newState;
    });
  }

  // handle when a select is changed
  const handleOptionChange = onSelectChange(handleUpdates);
  // handle when the target is blank
  const handleInputChange = onInputChange(handleUpdates);

  const create = () => onCreate(getAuth());

  const platoonClassnames = classnames('platoon', { 'hide': auth !== 'class' });

  let baseSelect;
  if (isAdmin)
    baseSelect = <BaseSelect value={school_id} onChange={handleOptionChange('school_id')} />;

  if (!school_id)
    school_id = login.school_id;

  if (!class_id)
    class_id = login.class_id;

  return (
    <div className='CreatePositionRow'>
      <Row>
        <Col xs={6}>
          <label>Role</label>
          <RoleSelect
            value={auth}
            onChange={handleOptionChange('auth')} />
        </Col>
        <Col xs={6}>
          <label>Position</label>
          <Input name='position' value={position} onChange={handleInputChange} />
        </Col>
        {isAdmin &&
          <Col xs={6}>
            <label>Base</label> {baseSelect}
          </Col>
        }
        <Col xs={isAdmin ? 6 : 12} className={platoonClassnames}>
          <label>Platoon</label>
          <PlatoonSelect schoolId={school_id} value={class_id}
            onChange={handleOptionChange('class_id')} tabIndex={auth !== 'class' ? '-1' : '0'} />
        </Col>

        {/* optional create button */}
        {showCreateButton &&
          <Col xs={12}>
            <ButtonBar>
              <Button color='primary' onClick={create}>
                <FontAwesome icon='save' /> Create Position
              </Button>
            </ButtonBar>
          </Col>
        }
      </Row>
    </div>
  );
}

CreatePositionRow.propTypes = {
  showCreateButton: PropTypes.bool,
  onCreate: PropTypes.func,
  onChange: PropTypes.func,
  login: PropTypes.object.isRequired,
  isAdmin: PropTypes.bool,
  adminId: PropTypes.any
};

export default CreatePositionRow;
