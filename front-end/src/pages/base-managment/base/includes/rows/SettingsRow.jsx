import React, { useState, useEffect, Fragment } from 'react';
// components
import { Row, Col } from 'reactstrap';
import { Radio, Checkbox, Date, Label } from 'components/inputs';
import { LEGACY_URL } from 'components/constants';
// functions
import julian from 'julian';
import moment from 'moment';
import { toJulian } from 'functions/dates';
import { onCheckboxChange, onInputChange, onNumberChange } from 'functions/events';

export const SettingsRow = ({ base, onUpdate }) => {

  const [disabled, setDisabled] = useState(false);
  const [storeResetOptions, setStoreResetOptions] = useState([]);
  const [checked, setChecked] = useState(false);

  useEffect(() => {
    fetch(`${LEGACY_URL}/api/registration/store_resets.php`)
      .then(res => res.json())
      .then(resets => {
        console.log(resets.data)
        setStoreResetOptions(resets.data)
      })
  }, []);

  const onChange = onInputChange(onUpdate);
  const handleCheckbox = onCheckboxChange(onUpdate);
  const handleNumberChange = onNumberChange(onUpdate);

  const onDateChage = date =>
    onUpdate({ store_reset: date ? toJulian(date) : date });

  const changeSchoolReset = event => {
    const store_reset = event.target.value;
    onUpdate({ store_reset });
    setDisabled(true);
    setChecked(true);
  }

  const enableCustom = event => {
    const store_reset = event.target.value;
    onUpdate({ store_reset });
    setDisabled(false);
    setChecked(false);
  }

  let {
    pic_mission_type, store_reset, school_gender,
    print_parent_tasks, allow_parent_tasks, rewards,
    one_time_prize_reset
  } = base;

  const store_reset_jd = parseInt(store_reset, 10);
  store_reset = store_reset > 0 ? moment(julian.toDate(store_reset)) : undefined;
  console.log(store_reset_jd)

  // props for all inputs
  const checkboxProps = { onChange: handleCheckbox };
  const schoolGenderProps = { name: 'school_gender', onChange: onChange }
  const missionTypeProps = { name: 'pic_mission_type', onChange: handleNumberChange }
  const storeResetProps = { name: 'store_miles_reset', onChange: onChange }

  return (
    <Row id='SettingsRow'>
      <Col xs={12} sm={6} xl={4}>
        <Label>Base Gender</Label>
        <Radio value='M'
          {...schoolGenderProps}
          checked={school_gender === 'M'} >
          Boys
        </Radio>

        <Radio value='F'
          {...schoolGenderProps}
          checked={school_gender === 'F'}>
          Girls
        </Radio>

        <Radio value='B'
          {...schoolGenderProps}
          checked={school_gender === 'B'}>
          Both
        </Radio>
      </Col>

      <Col xs={12} sm={6} xl={4}>
        <Label>Mission Sheet Type</Label>
        <Radio value='1'
          {...missionTypeProps}
          checked={pic_mission_type === 1} >
          No Pictures
        </Radio>

        <Radio value='2'
          {...missionTypeProps}
          checked={pic_mission_type === 2}>
          Small Pictures
        </Radio>
      </Col>

      <Col xs={12} sm={6} xl={4}>
        <Label>Custom Parent Tasks</Label>
        <Checkbox checked={!!allow_parent_tasks} name='allow_parent_tasks' {...checkboxProps} >
          Allow
        </Checkbox>
        <Checkbox checked={!!print_parent_tasks} name='print_parent_tasks' {...checkboxProps} >
          Include on printable mission sheets
        </Checkbox>
      </Col>

      {/*<Col xs={12} sm={6} xl={4}>*/}
      {/*  <Label>Amount of Boys Chidon Posters</Label>*/}
      {/*  <Input type='number'*/}
      {/*      name='chidon_posters_boys'*/}
      {/*      onChange={ this.onNumberChange }*/}
      {/*      value={ chidon_posters_boys || 0 } />*/}
      {/*</Col>*/}
      {/*<Col xs={12} sm={6} xl={4}>*/}
      {/*  <Label>Amount of Girls Chidon Posters</Label>*/}
      {/*  <Input type='number'*/}
      {/*      name='chidon_posters_girls'*/}
      {/*      onChange={ this.onNumberChange }*/}
      {/*      value={ chidon_posters_girls || 0 } />*/}
      {/*</Col>*/}

      {rewards > 0 &&
        <Fragment>
          <Col sm={12} className='special-options'>
            <p className='title'>Store Miles Settings</p>
            <Label>Allow students to spend miles earned from:</Label>

            {storeResetOptions && storeResetOptions.map((reset, index) => (
              <Fragment key={reset.jd}>
                <Radio value={reset.jd}
                  name='store_miles_reset'
                  onChange={changeSchoolReset}
                  checked={store_reset_jd === parseInt(reset.jd, 10) || (index === 1 && store_reset === undefined)}>
                  {reset.title} ({reset.hDate} / {reset.date})
                </Radio>
                <br />
              </Fragment>
            ))}

            <Radio key={0} id='store_reset' value='0'
              name='store_miles_reset'
              onChange={changeSchoolReset}
              checked={store_reset === undefined && store_reset_jd === 0}>
              Always (This includes all miles from previous years)
            </Radio>
            <br />

            <Radio key={toJulian(moment())} value={toJulian(moment())}
              name='store_miles_reset'
              {...storeResetProps}
              checked={store_reset !== undefined && !checked}
              onChange={enableCustom}>
              Custom Date:
            </Radio>
            <br />

            <Date value={store_reset}
              disabled={disabled}
              onChange={onDateChage} />
          </Col>

          <Col sm={12} className='special-options'>
            <p className='title'>One Time Prize Settings</p>
            <Label>Students should not be able to purchase more than one of the "one time prize" if purchased from:</Label>
            {storeResetOptions && storeResetOptions.map((reset, i) => (
              <Fragment key={reset.jd}>
                <Radio value={reset.jd}
                  name='one_time_prize_reset'
                  onChange={onChange}
                  checked={parseInt(one_time_prize_reset, 10) === parseInt(reset.jd, 10) || (i === 1 && !one_time_prize_reset)}>
                  {reset.title} ({reset.hDate} / {reset.date})
                </Radio>
                <br />
              </Fragment>
            ))}
          </Col>
        </Fragment>
      }
    </Row>
  );
}
