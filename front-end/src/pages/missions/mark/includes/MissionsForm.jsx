import React, { useState, useEffect, useRef } from 'react';
import { useDispatch, useSelector, shallowEqual } from 'react-redux';
// components
import { Row, Col, Button, Input, FormFeedback } from 'reactstrap';
import { InlineSync, FontAwesome } from 'components/ui';
import { BaseSelect, PlatoonSelect, SoldierSelect, ParshaSelect } from 'components/inputs';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { julianToday } from 'functions/dates';
// store and constants
import { getMissions } from 'store/missions/mark/operations';
import { LEGACY_URL } from 'components/constants';

const MissionsForm = (props) => {
  const { user_id, onSoldierChange } = props;
  const dispatch = useDispatch();

  const { login, loading, parshos, soldiers, school_id } = useSelector(({ login, missions, base }) => ({
    login: login.current_login,
    loading: missions.mark.loading,
    soldiers: base.soldiers.soldiers,
    parshos: missions.parshos.parshos,
    school_id: login.current_login.school_id
  }), shallowEqual);

  const [state, setState] = useState({
    school_id: false,
    class_id: false,
    parsha_id: false
  });
  const { class_id, parsha_id } = state;
  const initializedSchool = useRef(false);

  const [touched, setTouched] = useState({});

  useEffect(() => {
    if (initializedSchool.current) return;
    initializedSchool.current = true;
    // Set default school id
    setState(prev => ({ ...prev, school_id: school_id }));
  }, [school_id]);

  useEffect(() => {
    // Set default parsha
    if (!parshos || parshos.length === 0) return;

    const today = julianToday();
    let parsha = parshos.filter(p => p.end < today).pop();
    if (!parsha) parsha = parshos[0];

    // eslint-disable-next-line react-hooks/set-state-in-effect
    setState(prev => ({ ...prev, parsha_id: parsha.id }));
  }, [parshos]);

  const onSelectChange = key => option => {
    setState(prev => ({ ...prev, [key]: option ? option.value : false }));
  };

  const handlePlatoonChange = option => {
    setState(prev => ({ ...prev, class_id: option ? option.value : false }));
    if (option && option.value) onSoldierChange(false);
  };

  const handleSoldierChange = option => {
    if (option && option.value) onSoldierChange(option.value);
  };

  const loadMissionsData = (selectedUserId = user_id) => {
    if (!loading) {
      dispatch(getMissions(selectedUserId, parsha_id))
        .catch(e => toast.error(e.message));
    }
  };

  const selectSerial = ({ target }) => {
    setTouched(prev => ({ ...prev, serial: true }));

    // Original logic: returns false if not match '7[0-9]{6}'
    if (!target.value.match('7[0-9]{6}'))
      return;

    // find the soldier
    const serial_number = parseInt(target.value, 10);
    const soldier = soldiers.find(
      s => s.user_serial === serial_number
    );
    if (!soldier)
      return;

    // load his missions
    onSoldierChange(soldier.user_id);

    setState(prev => ({
      ...prev,
      class_id: soldier.class_id,
      school_id: soldier.school_id
    }));
    loadMissionsData(soldier.user_id);
  };

  const today = julianToday();

  return (
    <div id='BCMissionsForm'>

      <Row>
        <Col>
          <label>Serial Number (Quick Select)</label>
          <Input type='text' pattern='77[0-9]{5}' autoFocus
            onChange={selectSerial}
            invalid={touched.serial}
          />
          <FormFeedback>Please enter a valid serial number (e.g. 7765904)</FormFeedback>
        </Col>
      </Row>

      <hr />

      <form target='_blank' method='post' action={`${LEGACY_URL}/api/print/mark`}>
        <Row>
          <Col sm={6}>
            <label>Base</label>
            <BaseSelect
              name='school_id'
              value={state.school_id}
              isDisabled={!isAdmin(login.code)}
              onChange={onSelectChange('school_id')} />
            <input type='hidden' value={state.school_id || ''} name='school_id' />
          </Col>

          <Col sm={6}>
            <label>Platoon</label>
            <PlatoonSelect
              isClearable
              value={class_id}
              schoolId={state.school_id}
              openMenuOnFocus={false}
              onChange={handlePlatoonChange} />
            <input type='hidden' value={class_id || ''} name='class_id' />
          </Col>

          <Col sm={6}>
            <label>Soldier</label>
            <SoldierSelect
              registeredOnly
              value={user_id}
              classId={class_id}
              schoolId={state.school_id}
              openMenuOnFocus={false}
              onChange={handleSoldierChange} />
            <input type='hidden' value={user_id || ''} name='user_id' />
          </Col>

          <Col sm={6}>
            <label>Parsha</label>
            <ParshaSelect
              isClearable
              isDescending
              value={parsha_id}
              endDate={today}
              onChange={onSelectChange('parsha_id')} />
            <input type='hidden' value={parsha_id || ''} name='parsha_id' />
          </Col>
        </Row>

        <Row className='buttons'>
          <Col sm={6}>
            <Button color='primary' onClick={() => loadMissionsData()}
              disabled={!user_id || !parsha_id || loading} >
              <InlineSync loading={loading} /> Load Missions
            </Button>
          </Col>

          <Col sm={6}>
            <Button color='primary' disabled={!user_id || !parsha_id}>
              <FontAwesome icon='print' /> Mark Printed Version
            </Button>
          </Col>
        </Row>
      </form>
    </div>
  );
};

export default MissionsForm;
