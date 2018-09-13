
import React, { Component } from 'react';
import { Number } from 'components/ui';
import { Select } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { Row, Col, Label, Input } from 'reactstrap';

export class OrderForm extends Component {

  componentDidMount(){
    let { prize, updatePrize } = this.props;
    let prizeOptions = this.getOptions();
    // select the first prize
    if ( !prize && prizeOptions.length > 0 )
      updatePrize( prizeOptions[0] );
  }

  getOptions = () => {
    let { store } = this.props;

    return store.prizes.map( prize => ({
      ...prize,
      value: prize.prize_id,
      label: `${prize.prize_name} (${prize.points.toLocaleString( navigator.language )} Miles)`, 
      isDisabled: prize.points > store.miles
    }));
  }

  render() {
    let { prize, store, qty, saving, ...props } = this.props;
    // generate the max qty
    let max = 100;
    if ( prize )
      max = Math.floor( store.miles / prize.points );
    if ( prize && max > prize.prize_count )
      max = prize.prize_count;
    if ( prize.one_per_user )
      max = 1;
    // generate the options
    let prizeOptions = this.getOptions();
    // calculate the total being spent
    const total = prize && qty ? prize.points * qty : 0;
    // set disabled and error messages
    const one_per_user_invalid = qty > 1 && !!prize.one_per_user;
    const disabled = !prize || qty > max;
  
    return (
      <div id='OrderForm'>
        <Row>
          <Col xs={ 9 } sm={ 8 }>
            <Label>Prize</Label>
            <Select value={ prize }
              options={ prizeOptions }
              openMenuOnFocus={ false }
              onChange={ props.updatePrize }/>
          </Col>
  
          <Col xs={ 3 } sm={ 4 }>
            <Label>Qty</Label>
            <Input type='number' min={ 1 } max={ max } required
              value={ qty } onChange={ props.updateQty } />
            { max > 0 && !prize.one_per_user && <div className='invalid-message'>1 - <Number value={ max } /></div> }
            { one_per_user_invalid && <div className='invalid-message'>1 per soldier</div> }
          </Col>
        </Row>
  
        <Row id='total-row'>
          <Col xs={ 6 } sm={ 4 }>
            <Label>Soldier's Miles</Label>
            <Number className='total' value={ store.miles } />
          </Col>
  
          <Col xs={ 6 } sm={ 4 }>
            <Label>Final Price</Label>
            <Number className='total' value={ total } />
          </Col>
  
          <Col xs={ 12 } sm={ 4 }>
            <SaveButton 
              show={ true } 
              saving={ saving }
              text='Place Order'
              disabled={ disabled } />
          </Col>
        </Row>
      </div>
    );
  }
};
