import React, { Component } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
import { Radio, Checkbox, Date, Label } from 'components/inputs';
// functions
import { onCheckboxChange, onNumberChange, onInputChange } from 'functions/events';

export class HQSettingsRow extends Component {

  onChange = onInputChange( this.props.onUpdate );
  onNumberChange = onNumberChange( this.props.onUpdate );
  handleCheckbox = onCheckboxChange( this.props.onUpdate );

  onDateChage = date => {
    this.props.onUpdate({ early_bird: date.format('Y-MM-DD') });
  }
  // prevent the "cannot retun null" errors
  getValue = val => val === null ? '' : val;

  render () {
    const { base } = this.props;
    // props for all inputs
    const checkboxProps = { onChange: this.handleCheckbox };
    const numberProps = { onChange: this.onNumberChange, type: 'number', min: 0 };
    const regTypeProps = { onChange: this.onChange, name: 'reg_type' }
    
    return (
      <Row id='HQSettingsRow'>
        <Col xl={6}>
          <Label><strong>Currently</strong> enrolled in:</Label>
          <Checkbox { ...checkboxProps} checked={!!base.chayolei} name='chayolei'>
            Chayolei (CTH)
          </Checkbox>
          <Checkbox { ...checkboxProps} checked={!!base.chidon} name='chidon'>
            Chidon
          </Checkbox>
          <Checkbox { ...checkboxProps} checked={!!base.tanya} name='tanya'>
            Tanya
          </Checkbox>
          <Checkbox { ...checkboxProps} checked={!!base.tehillim} name='tehillim'>
            WWTC
          </Checkbox>
        </Col>

        <Col xl={6} className='special-options'>
          <Label>Extra Hachayols</Label>

          <Input type='number'
            name='hachayol_extra'
            onChange={ this.onNumberChange }
            value={ base.hachayol_extra === null || base.hachayol_extra === undefined ? '' : base.hachayol_extra } />
            
          <Checkbox
              name='hachayol_extra_total'
              checked={ base.hachayol_extra_total || false }
              onChange={ this.handleCheckbox }>
            Make this the <strong>total</strong> number.
          </Checkbox>
        </Col>

        <Col xs={12}>
          <hr />
          <Label>Registration Type</Label>
          <Radio value='1' { ...regTypeProps }
            checked={ base.reg_type === '1' }>
            In Tuition
          </Radio>
          <Radio value='2' { ...regTypeProps }
            checked={ base.reg_type === '2' }>
            Guaranteed
          </Radio>
          <Radio value='3' { ...regTypeProps }
            checked={ base.reg_type === '3' }>
            By Parent
          </Radio>
        </Col>

        <Col sm={6} xl={3}>
          <Label>CTH Registration Fee</Label>
          <Input { ...numberProps } name='chayolei_fee' step=".01" max='9999.99'
            value={ this.getValue( base.chayolei_fee ) } onChange={ this.onNumberChange } />
        </Col>

        <Col sm={6} xl={3}>
          <Label>Chidon Registration Fee</Label>
          <Input { ...numberProps } name='chidon_fee' step=".01" max='9999.99'
            value={ this.getValue( base.chidon_fee ) } onChange={ this.onNumberChange } />
        </Col>

        <Col sm={6} xl={3}>
          <Label>Tanya Registration Fee</Label>
          <Input { ...numberProps } name='tanya_fee' step=".01" max='9999.99'
            value={ this.getValue( base.tanya_fee ) } onChange={ this.onNumberChange } />
        </Col>

        <Col sm={6} xl={3}>
          <Label>Rewards Registration Fee</Label>
          <Input { ...numberProps } name='rewards_fee' step=".01" max='9999.99'
            value={ this.getValue( base.rewards_fee ) } onChange={ this.onNumberChange } />
        </Col>

        <Col sm={4}>
          <Label>Balance</Label>
          <Input { ...numberProps } name='balance' step=".01" max='9999.99' min={ undefined }
            value={ this.getValue( base.balance ) } onChange={ this.onNumberChange } />
        </Col>

        <Col sm={4}>
          <Label>Early Bird</Label>
          <Date value={ base.early_bird }
            onChange={ this.onDateChage } />
        </Col>

        <Col sm={4}>
          <Label>Base Soldier Fee</Label>
          <Input { ...numberProps } name='child_fee'
            value={ this.getValue( base.child_fee )  }
            onChange={ this.onNumberChange } placeholder='Default' />
        </Col>
      </Row>
    );
  }
}
