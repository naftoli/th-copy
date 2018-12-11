import React, { Component } from 'react';
// components
import { Row } from 'reactstrap';
import { Radio } from 'components/inputs';
import { DateDisplay, NumberDisplay } from 'components/ui';
import { onNumberChange } from 'functions/events';

export class RegTypeRow extends Component {

  static defaultProps = {
    prices: {
      discounts: {},
      rates: []
    }
  }

  onChange = onNumberChange( this.props.onUpdate );

  render () {
    const { prices, earlyBird, childFee, regType } = this.props;

    return (
      <Row className='RegTypeRow'>
        <div className='registration-option'>
          <Radio value='1'
              name='reg_type'
              checked={ regType === '1' }
              onChange={ this.onChange }>
            In Tuition
          </Radio>
          
          <ul className='checkboxes details'>
            <li>Tzivos Hashem is included in your school tuition.</li>
            <li>Tzivos Hashem will bill the base directly for all chayolei soldiers at the discretion of the accounting department.</li>
          </ul>

          <div className='discounts'>
            Eligible Discounts:
            <ul>
              <li>None</li>
            </ul>
          </div>

          <div className='price'>
            Rate: <Rate rate={ prices.rates[0] } childFee={ childFee } />
          </div>
        </div>

        <div className='registration-option'>
          <Radio value='2'
              name='reg_type'
              checked={ regType === '2' }
              onChange={ this.onChange }>
            Guaranteed by Base
          </Radio>

          <ul className='checkboxes details'>
            <li>All chayolei soldiers will be registered by the deadline.</li>
            <li>Soldiers are given an additional discount.</li>
            <li>If any soldiers are not registered by the deadline, you will be charged for the total discount given.</li>
          </ul>

          <div className='discounts'>
            Eligible Discounts:
            <ul>
              <li>Guaranteed: $<NumberDisplay value={ prices.discounts.guaranteed } /></li>
              <li>Early Bird (<DateDisplay value={earlyBird} />): $<NumberDisplay value={ prices.discounts.early_bird } /></li>
            </ul>
          </div>
          <div className='price'>
            Rate: <Rate rate={ prices.rates[1] } childFee={ childFee } />
          </div>
        </div>

        <div className='registration-option'>
            <Radio value='3'
              name='reg_type'
              checked={ regType === '3' }
              onChange={ this.onChange }>
            Paid by Parents
          </Radio>

          <ul className='checkboxes details'>
            <li>Each soldier will register when they wish</li>
            <li>Soldiers are eligible for the early bird discount.</li>
          </ul>

          <div className='discounts'>
            Eligible Discounts:
            <ul>
              <li>Early Bird (<DateDisplay value={earlyBird} />): $<NumberDisplay value={ prices.discounts.early_bird } /></li>
            </ul>
          </div>
          <div className='price'>
            Rate: <Rate rate={ prices.rates[1] } childFee={ childFee } />
          </div>
        </div>
      </Row>
    );
  }
}

const Rate = ({ rate, childFee }) => {

  if ( typeof childFee === 'number' ) {
    return (
      <span className='rate'>
        <span className='cross-out'>
          $<NumberDisplay value={ rate } />
        </span>{' '}
        $<NumberDisplay value={ childFee } />/soldier
      </span>
    );
  }

  return (
    <span className='rate'>
     $<NumberDisplay value={ rate } />/soldier
    </span>
  )
}
