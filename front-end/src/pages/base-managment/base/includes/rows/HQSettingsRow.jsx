import React, { Fragment } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
import { Radio, Checkbox, Date, Label } from 'components/inputs';
// functions
import { onCheckboxChange, onNumberChange, onInputChange } from 'functions/events';
import { AccountingRow } from "./AccountingRow";

export const HQSettingsRow = ({ base, onUpdate }) => {

  const onChange = onInputChange(onUpdate);
  const handleNumberChange = onNumberChange(onUpdate);
  const handleCheckbox = onCheckboxChange(onUpdate);

  const onDateChage = date => {
    onUpdate({ early_bird: date.format('Y-MM-DD') });
  }

  // prevent the "cannot retun null" errors
  const getValue = val => val === null ? '' : val;

  // props for all inputs
  const checkboxProps = { onChange: handleCheckbox };
  const numberProps = { onChange: handleNumberChange, type: 'number', min: 0 };
  const regTypeProps = { onChange: onChange, name: 'reg_type' }

  return (
    <Fragment>
      <Row id='HQSettingsRow'>
        <Col xl={6}>
          <Label><strong>Currently</strong> enrolled in:</Label>
          <Checkbox {...checkboxProps} checked={!!base.chayolei} name='chayolei'>
            Chayolei (CTH)
          </Checkbox>
          <Checkbox {...checkboxProps} checked={!!base.chidon} name='chidon'>
            Chidon
          </Checkbox>
          <Checkbox {...checkboxProps} checked={!!base.tanya} name='tanya'>
            Tanya
          </Checkbox>
          <Checkbox {...checkboxProps} checked={!!base.tehillim} name='tehillim'>
            WWTC
          </Checkbox>
        </Col>

        <Col xl={6} className='special-options'>
          <Label>Extra Hachayols</Label>

          <Input type='number'
            name='hachayol_extra'
            onChange={handleNumberChange}
            value={base.hachayol_extra === null || base.hachayol_extra === undefined ? '' : base.hachayol_extra} />

          <Checkbox
            name='hachayol_extra_total'
            checked={base.hachayol_extra_total || false}
            onChange={handleCheckbox}>
            Make this the <strong>total</strong> number.
          </Checkbox>
        </Col>

        <Col xs={12}>
          <hr />
          <Label>Registration Type</Label>
          <Radio value='1' {...regTypeProps}
            checked={base.reg_type === '1'}>
            In Tuition
          </Radio>
          <Radio value='2' {...regTypeProps}
            checked={base.reg_type === '2'}>
            Guaranteed
          </Radio>
          <Radio value='3' {...regTypeProps}
            checked={base.reg_type === '3'}>
            By Parent
          </Radio>
        </Col>

        <Col sm={6} xl={3}>
          <Label>CTH Registration Fee</Label>
          <Input {...numberProps} name='chayolei_fee' step=".01" max='9999.99'
            value={getValue(base.chayolei_fee)} onChange={handleNumberChange} disabled />
        </Col>

        <Col sm={6} xl={3}>
          <Label>Chidon Registration Fee</Label>
          <Input {...numberProps} name='chidon_fee' step=".01" max='9999.99'
            value={getValue(base.chidon_fee)} onChange={handleNumberChange} disabled />
        </Col>

        <Col sm={6} xl={3}>
          <Label>Tanya Registration Fee</Label>
          <Input {...numberProps} name='tanya_fee' step=".01" max='9999.99'
            value={getValue(base.tanya_fee)} onChange={handleNumberChange} disabled />
        </Col>

        <Col sm={6} xl={3}>
          <Label>Rewards Registration Fee</Label>
          <Input {...numberProps} name='rewards_fee' step=".01" max='9999.99'
            value={getValue(base.rewards_fee)} onChange={handleNumberChange} disabled />
        </Col>

        <Col sm={4}>
          <Label>Balance</Label>
          <Input {...numberProps} name='balance' step=".01" max='9999.99' min={undefined}
            value={getValue(base.balance)} onChange={handleNumberChange} disabled />
        </Col>

        <Col sm={4}>
          <Label>Early Bird</Label>
          <Date value={base.early_bird}
            onChange={onDateChage} disabled />
        </Col>

        <Col sm={4}>
          <Label>Base Soldier Fee</Label>
          <Input {...numberProps} name='child_fee'
            value={getValue(base.child_fee)}
            onChange={handleNumberChange} placeholder='Default' disabled />
        </Col>
      </Row>

      <AccountingRow
        {...base}
        title='Accounting Dept Info'
        prefix='accounting_'
        onChange={onChange} />

      <p className='title'>Accounting Notes</p>
      <Input type="textarea" name='hq_notes' rows='6'
        value={base.hq_notes || ''} onChange={onChange} />
    </Fragment>
  );
}
