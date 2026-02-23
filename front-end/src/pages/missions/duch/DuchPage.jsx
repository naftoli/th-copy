import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
import { Callout, FontAwesome } from 'components/ui';
import { Row, Col, Button } from 'reactstrap';
import { PlatoonSelect, SoldierSelect, Radio, BaseSelect } from 'components/inputs';
import { Select } from 'components/selects';
import { setTitle } from 'functions/utils';
import { isAdmin, isBC } from 'functions/login';
import { LEGACY_URL } from 'components/constants';

const DuchPage = ({ login }) => {
  const [state, setState] = useState({
    school_id: false,
    class_ids: [],
    user_ids: [],
    double_sided: true,
    dates: 'hebrew',
    start: '',
    end: '',
    date_range: '30',
    selectedMonth: ''
  });

  useEffect(() => {
    setTitle('Duch');
    const { school_id, class_id } = login;
    setState(prev => ({
      ...prev,
      school_id,
      class_ids: class_id ? [class_id] : []
    }));
  }, []);

  const schoolChange = ({ value }) => {
    setState(prev => ({
      ...prev,
      school_id: value,
      class_ids: [],
      user_ids: [],
    }));
  };

  const handlePlatoonChange = (values) => {
    const class_ids = Array.isArray(values) ? values.map(v => v.value) : [];
    setState(prev => ({ ...prev, class_ids, user_ids: [] }));
  };

  const handleSoldierChange = (values) => {
    const user_ids = Array.isArray(values) ? values.map(v => v.value) : [];
    setState(prev => ({ ...prev, user_ids }));
  };

  const toggleDates = (e) => setState(prev => ({ ...prev, dates: e.target.value }));
  const toggleDoubleSided = (e) => setState(prev => ({ ...prev, double_sided: JSON.parse(e.target.value) }));

  const handleChange = (e) => {
    const name = e.target.name;
    const value = e.target.value;
    setState(prev => ({ ...prev, [name]: value }));
  };

  const handleSelectChange = (key) => (option) => {
    const value = option ? option.value : '';
    setState(prev => ({ ...prev, [key]: value }));
  };

  const heMonths = {
    'טבת': '12/21/25 - 1/18/26',
    'שבט': '1/19/26 - 2/17/26',
    'אדר': '2/18/26 - 3/18/26',
    'ניסן': '3/19/26 - 4/16/26',
    'אייר': '4/17/26 - 5/16/26',
    'סיון': '5/17/26 - 6/15/26',
    'תמוז': '6/16/26 - 7/14/26',
    'אב': '7/15/26 - 8/13/26',
    'אלול': '8/14/26 - 9/11/26'
  };

  let legacyUrl = '';
  if (LEGACY_URL === '') {
    legacyUrl = 'https://v.mashpia.com/api/print/duch';
  } else {
    legacyUrl = `${LEGACY_URL}/api/print/duch`;
  }

  return (
    <div id='DuchPage'>
      <Callout title='Duch'>
        <p><strong>Prepare and submit a Duch.</strong></p>
        <p>Select the parameters below and submit to generate the Duch.</p>
      </Callout>

      <form target='_blank' method='post' action={legacyUrl}>
        <Row>
          <Col sm={4}>
            <label>Base</label>
            <BaseSelect
              name='school_id'
              onChange={schoolChange}
              value={state.school_id}
              isDisabled={!isAdmin(login.code)} />
            <input type='hidden' value={state.school_id} name='school_id' />
          </Col>

          <Col sm={4}>
            <label>Platoon(s)</label>
            <PlatoonSelect
              placeholder='All Platoons'
              isMulti
              values={state.class_ids}
              isDisabled={!isBC(login.code)}
              openMenuOnFocus={false}
              schoolId={state.school_id}
              onChange={handlePlatoonChange} />
            <input type='hidden' value={(state.class_ids || []).join(',')} name='class_ids' />
          </Col>

          <Col sm={4}>
            <label>Soldier(s)</label>
            <SoldierSelect
              values={state.user_ids}
              isMulti
              registeredOnly
              schoolId={state.school_id}
              classIds={state.class_ids}
              openMenuOnFocus={false}
              onChange={handleSoldierChange}
              placeholder='All Soldiers' />
            <input type='hidden' value={(state.user_ids || []).join(',')} name='user_ids' />
          </Col>
        </Row>

        <Row style={{ height: 10 }} />
        <Row>
          <Col sm={4}>
            <label>Start</label>
            <input type='date' name='start' value={state.start} onChange={handleChange} className='form-control' />
          </Col>
          <Col sm={4}>
            <label>End</label>
            <input type='date' name='end' value={state.end} onChange={handleChange} className='form-control' />
          </Col>
          <Col sm={4}>
            <label>Or last Number of days</label>
            <Select
              options={[
                { value: '7', label: '7' },
                { value: '30', label: '30' },
                { value: '60', label: '60' },
                { value: '90', label: '90' },
              ]}
              value={[
                { value: '7', label: '7' },
                { value: '30', label: '30' },
                { value: '60', label: '60' },
                { value: '90', label: '90' },
              ].find(o => String(o.value) === String(state.date_range)) || null}
              onChange={handleSelectChange('date_range')}
            />
            <input type='hidden' name='date_range' value={state.date_range} />
          </Col>
        </Row>

        <Row>
          <Col sm={6}>
            <label>OR Choose a month</label>
            <Select
              options={Object.keys(heMonths).map(key => ({ value: heMonths[key], label: key }))}
              value={Object.keys(heMonths).map(key => ({ value: heMonths[key], label: key }))
                .find(o => String(o.value) === String(state.selectedMonth)) || null}
              onChange={handleSelectChange('selectedMonth')}
            />
            <input type='hidden' name='selectedMonth' value={state.selectedMonth} />
          </Col>

          <Col sm={6}>
            <label>Dates</label><br />
            <Radio name='dates' checked={state.dates === 'none'}
              value='none' onChange={toggleDates}>
              <strong>No</strong> dates.
            </Radio>
            <Radio name='dates' checked={state.dates === 'hebrew'}
              value='hebrew' onChange={toggleDates}>
              <strong>Hebrew</strong> dates.
            </Radio><br />
            <Radio name='dates' checked={state.dates === 'english'}
              value='english' onChange={toggleDates}>
              <strong>Hebrew and English</strong> dates.
            </Radio>
          </Col>
        </Row>

        <Row className='buttons' style={{ marginTop: '20px' }}>
          <Col sm={6}>
            <Button color='primary'>
              <FontAwesome icon='file' /> Generate Duch
            </Button>
          </Col>
        </Row>
      </form>
    </div>
  );
};

const mapStateToProps = ({ login }) => ({
  login: login.current_login
});

export default connect(mapStateToProps)(DuchPage);
