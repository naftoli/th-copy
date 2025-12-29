import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import { Page404 } from 'pages/errors';
import { Row, Col, Input, Button, FormFeedback } from 'reactstrap';
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
  API.post('/rewards/miles?action=manual', data)
    .then(() => updateNotifcation(toast_id, 'Miles Updated', '', true))
    .catch(e => updateNotifcation(toast_id, '', e.message, false));
}

const MilesPage = ({ login }) => {
  const [school_id, setSchoolId] = useState(false);
  const [class_id, setClassId] = useState(false);
  const [user_id, setUserId] = useState(false);
  const [miles, setMiles] = useState(1);
  const [touched, setTouched] = useState(false);

  useEffect(() => {
    setTitle('Add / Subtract Miles');

    if (isBC(login.code, true)) {
      setSchoolId(login.id.toString());
    } else if (isTeacher(login.code)) {
      setClassId(login.id.toString());
      setSchoolId(false); // Teacher doesn't need school_id state set? Original code set class_id.
    }
  }, [login]);

  // event handlers
  const onSchoolChange = option => setSchoolId(option.value);
  const onSoldierChange = option => setUserId(option.value);
  const onPlatoonChange = ({ value }) => {
    setClassId(value);
    setUserId(false);
  };
  const onNumberChange = ({ target }) => {
    setMiles(parseInt(target.value, 10));
  };
  const onBlur = () => setTouched(true);

  // submit buttons
  const handleUpdate = (action, storeOnly = false, auctionOnly = false) => () => {
    updateMiles({ school_id, class_id, user_id, miles, action, storeOnly, auctionOnly });
  };

  const showBase = isAdmin(login.code);
  const showPlatoon = !isTeacher(login.code);
  const disableActions = !class_id || !miles; // make sure a platoon is selected and miles are entered.

  if (isTeacher(login.code))
    return <Page404 />;

  const isInvalidMiles = touched && (miles < 1 || miles > 10000);

  return (
    <div id='MilesPage'>
      <Callout title='Add / Subtract Miles'>
        <p>
          Award soldiers miles to spend in their store.
          <strong> Please note that this does not effect miles earned from Chayolei missions</strong>
        </p>
      </Callout>

      <Row>
        {showBase &&
          <Col sm={6} xl={3}>
            <label>Base</label>
            <BaseSelect
              value={school_id}
              onChange={onSchoolChange} />
          </Col>
        }
        {showPlatoon &&
          <Col sm={showBase ? 6 : 12} xl={showBase ? 3 : 4}>
            <label>Platoon</label>
            <PlatoonSelect
              value={class_id}
              schoolId={school_id}
              onChange={onPlatoonChange} />
          </Col>
        }
        <Col sm={6} xl={showBase ? 3 : 4}>
          <label>Soldier</label>
          <SoldierSelect
            showAllOption
            value={user_id}
            schoolId={school_id}
            classId={class_id}
            onChange={onSoldierChange} />
        </Col>
        <Col sm={6} xl={showBase ? 3 : 4} >
          <label>Miles</label>
          <Input min='1' max='10000'
            type='number' name='miles'
            value={miles} onChange={onNumberChange}
            onBlur={onBlur}
            invalid={isInvalidMiles} // Check only if touched
          />
          <FormFeedback>1 - 10,000</FormFeedback>
        </Col>
        {/* If we do not show the base and we show the platoon, fit the buttons inline */}
        <Col sm={12}>
          <label>Actions</label>
          <ButtonBar>
            <Button color='primary'
              onClick={handleUpdate('add')}
              disabled={disableActions}>
              <InlineSync icon='plus' /> Add (All)
            </Button>
            <Button color='primary'
              onClick={handleUpdate('add', true, false)}
              disabled={disableActions}>
              <InlineSync icon='plus' /> Add (Store Only)
            </Button>
            <Button color='primary'
              onClick={handleUpdate('add', false, true)}
              disabled={disableActions}>
              <InlineSync icon='plus' /> Add (Auction Only)
            </Button>
          </ButtonBar>
          <ButtonBar style={{ marginTop: '12px' }}>
            <Button color='danger'
              onClick={handleUpdate('subtract')}
              disabled={disableActions}>
              <InlineSync icon='minus' /> Subtract (All)
            </Button>
            <Button color='danger'
              onClick={handleUpdate('subtract', true, false)}
              disabled={disableActions}>
              <InlineSync icon='minus' /> Subtract (Store Only)
            </Button>
            <Button color='danger'
              onClick={handleUpdate('subtract', false, true)}
              disabled={disableActions}>
              <InlineSync icon='minus' /> Subtract (Auction Only)
            </Button>
          </ButtonBar>
        </Col>
      </Row>
    </div>
  );
};

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
};

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(MilesPage);
