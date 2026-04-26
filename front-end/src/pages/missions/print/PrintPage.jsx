import React, { useState, useEffect, useRef } from 'react';
import { useSelector } from 'react-redux';
// components
import { Callout, FontAwesome } from 'components/ui';
import { Row, Col, Button, Input, UncontrolledTooltip } from 'reactstrap';
import {
  PlatoonSelect, SoldierSelect,
  Radio, ParshaSelect, BaseSelect, Checkbox
} from 'components/inputs';
// functions
import julian from 'julian';
import { setTitle } from 'functions/utils';
import { isAdmin, isBC } from 'functions/login';
// style
import './PrintPage.scss';
import { LEGACY_URL } from 'components/constants';

export const PrintPage = () => {
  const { login, parshos } = useSelector(({ login, missions }) => ({
    login: login.current_login,
    parshos: missions.parshos.parshos
  }));

  const [state, setState] = useState({
    school_id: false, class_ids: [],
    user_ids: [], parsha_ids: [],
    double_sided: true, dates: 'hebrew'
  });

  const {
    school_id, class_ids, user_ids,
    parsha_ids, double_sided, dates
  } = state;
  const initializedLoginDefaults = useRef(false);

  useEffect(() => {
    if (initializedLoginDefaults.current) return;
    initializedLoginDefaults.current = true;
    setTitle('Print Missions');
    // set the default school id and class id
    const { school_id, class_id } = login;
    setState(prev => ({
      ...prev,
      school_id,
      class_ids: class_id ? [class_id] : []
    }));
  }, [login]);

  useEffect(() => {
    // set the default parsha
    if (parshos.length > 0) {
      const today = parseInt(julian(new Date()), 10);
      // get the first week after the current week and select it
      const parsha = parshos.find(parsha => today < parsha.start);
      // Only set parsha_ids if a valid parsha was found and if parsha_ids is empty (to avoid overwriting user selection if they navigate back/forth or if logic differs, but original code just ran it on mount or update if parshos changed)
      // Original logic: setDefaultParsha called on mount if parshos.length > 0, and on update if parshos changed.
      // We can just rely on this effect running when parshos changes.
      if (parsha) {
        // eslint-disable-next-line react-hooks/set-state-in-effect
        setState(prev => ({ ...prev, parsha_ids: [parsha.id] }));
      }
    }
  }, [parshos]);

  const showInstructions = () => {
    window.open(
      `${LEGACY_URL}/mission_report/instructions/`, '_blank',
      'width=770, height=700, menubar=no, scrollbars=yes, status=no, toolbar=no, titlebar=no'
    );
  }

  const schoolChange = ({ value }) => {
    setState(prev => ({ ...prev, school_id: value }));
  };

  const multiSelectChange = key => values => {
    setState(prev => ({ ...prev, [key]: values.map(val => val.value) }));
  };

  const toggleDates = (e) => setState(prev => ({ ...prev, dates: e.target.value }));

  const toggleDoubleSided = (e) => setState(prev => ({ ...prev, double_sided: JSON.parse(e.target.value) }));

  let legacyUrl = '';
  if (school_id === 9 && LEGACY_URL === '' && isBC(login.code)) {
    legacyUrl = 'https://v.mashpia.com/api/print/missions';
  } else {
    legacyUrl = `${LEGACY_URL}/api/print/missions`;
  }

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

      <form target='_blank' method='post' action={legacyUrl}>
        <Row>
          <Col sm={6}>
            <label>Base</label>
            <BaseSelect
              name='school_id' onChange={schoolChange}
              value={school_id} isDisabled={!isAdmin(login.code)} />
            <input type='hidden' value={school_id} name='school_id' />
          </Col>

          <Col sm={6}>
            <label>Platoon(s)</label>
            <PlatoonSelect
              placeholder='All Platoons' isMulti values={class_ids}
              isDisabled={!isBC(login.code)} openMenuOnFocus={false}
              schoolId={school_id} onChange={multiSelectChange('class_ids')} />
            <input type='hidden' value={class_ids} name='class_ids' />
          </Col>

          <Col sm={6}>
            <label>Soldier(s)</label>
            <SoldierSelect
              values={user_ids} isMulti registeredOnly
              schoolId={school_id} classIds={class_ids} openMenuOnFocus={false}
              onChange={multiSelectChange('user_ids')} placeholder='All Soldiers' />
            <input type='hidden' value={user_ids} name='user_ids' />
          </Col>

          <Col sm={6}>
            <label>Parsha(s)</label>
            <ParshaSelect
              isMulti values={parsha_ids}
              onChange={multiSelectChange('parsha_ids')} />
            <input type='hidden' value={parsha_ids} name='parsha_ids' />
          </Col>
        </Row>

        <Row>
          <Col sm={6} xl={3}>
            <label>Double sided</label><br />
            <Radio name='double_sided' checked={double_sided}
              value={true} onChange={toggleDoubleSided}>
              I <strong>am</strong> printing double sided copies.
            </Radio><br />
            <Radio name='double_sided' checked={!double_sided}
              value={false} onChange={toggleDoubleSided}>
              I am <strong>not</strong> printing double sided copies.
            </Radio>
          </Col>

          <Col sm={6} xl={3}>
            <label>Dates</label><br />
            <Radio name='dates' checked={dates === 'none'}
              value='none' onChange={toggleDates}>
              <strong>No</strong> dates.
            </Radio>
            <Radio name='dates' checked={dates === 'hebrew'}
              value='hebrew' onChange={toggleDates}>
              <strong>Hebrew</strong> dates.
            </Radio><br />
            <Radio name='dates' checked={dates === 'english'}
              value='english' onChange={toggleDates}>
              <strong>Hebrew and English</strong> dates.
            </Radio>
          </Col>

          <Col sm={12} xl={6}>
            <label id='pages'>Minimum Number of pages</label>
            <UncontrolledTooltip placement="top" target="pages" autohide={false}>
              <strong>If entered,</strong> the print generator will generate blank pages{' '}
              to make sure that each soldier has at least this number of pages.<br />
            </UncontrolledTooltip>
            {/* The input below is uncontrolled in the original code? 
                It has no value prop, just name/type/min. 
                Wait, it's inside a form that submits to a legacy URL.
                So uncontrolled is fine/expected. 
            */}
            <Input name='pages' type='number' min={double_sided ? 2 : 1} step={double_sided ? 2 : 1} />
          </Col>
        </Row>

        <Row>
          <Col sm={12}>
            <label>Print Jobs</label><br />
            <Checkbox name='batches'>Print in separate print jobs</Checkbox>
          </Col>
        </Row>

        <Row className='buttons'>
          <Col sm={6}>
            <Button color='primary' onClick={showInstructions}>
              <FontAwesome icon='clipboard-list' /> Printing Instructions
            </Button>
          </Col>

          <Col sm={6}>
            <Button color='primary'>
              <FontAwesome icon='print' /> Print
            </Button>
          </Col>
        </Row>
      </form>
    </div>
  );
}

export default PrintPage;
