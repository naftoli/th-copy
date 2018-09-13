
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
      value: prize.prize_id,
      label: `${prize.prize_name} (${prize.points} Miles)`, 
      points: prize.points,
      isDisabled: prize.points > store.miles
    }));
  }

  render() {
    let { prize, store, qty, ...props } = this.props;
    // generate the max qty
    let max = 100;
    if ( prize )
      max = Math.floor( store.miles / prize.points );
    // generate the options
    let prizeOptions = this.getOptions();
    // calculate the total being spent
    const total = prize && qty ? prize.points * qty : 0;
  
    return (
      <div id='OrderForm'>
        <Row>
          <Col xs={ 9 } sm={ 8 }>
            <Label>Prize</Label>
            <Select options={ prizeOptions } value={ prize } onChange={ props.updatePrize }/>
          </Col>
  
          <Col xs={ 3 } sm={ 4 }>
            <Label>Qty</Label>
            <Input type='number' min={ 1 } max={ max } required
              value={ qty } onChange={ props.updateQty } />
            { max > 0 && <div className='invalid-message'>1 - <Number value={ max } /></div> }
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
              disabled={ !prize || total > store.miles }
              saving={ false }
              text='Place Order'
              show={ true } />
          </Col>
        </Row>
      </div>
    );
  }
};
