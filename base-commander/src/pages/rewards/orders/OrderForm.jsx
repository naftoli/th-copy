
import React, { Component } from 'react';
import { NumberDisplay } from 'components/ui';
import { Select } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { Row, Col, Label, Input, Button } from 'reactstrap';

export class OrderForm extends Component {

  componentDidMount(){
    let { items, addItem } = this.props;
    let prizeOptions = this.getOptions();
    // select the first prize if no items exist
    if ( (!items || items.length === 0) && prizeOptions.length > 0 ) {
      // Initialize with first item using addItem
      addItem();
    }
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
    let { items, store, saving, ...props } = this.props;
    
    // generate the options
    let prizeOptions = this.getOptions();
    
    // calculate the total being spent
    const total = items ? items.reduce((sum, item) => {
      return sum + (item.prize && item.qty ? item.prize.points * item.qty : 0);
    }, 0) : 0;
    
    // check if total exceeds available miles
    const exceedsMiles = total > store.miles;
    
    // check if any item is invalid
    const hasInvalidItems = items ? items.some(item => {
      if (!item.prize) return true;
      
      let max = 100;
      if (item.prize) {
        max = Math.floor(store.miles / item.prize.points);
        if (max > item.prize.prize_count) max = item.prize.prize_count;
        if (item.prize.one_per_user) max = 1;
      }
      
      return item.qty > max || (item.qty > 1 && !!item.prize.one_per_user);
    }) : true;
    
    const disabled = !items || items.length === 0 || hasInvalidItems || exceedsMiles;
  
    return (
      <div id='OrderForm'>
        {items && items.map((item, index) => {
          // generate the max qty for this item
          let max = 100;
          if (item.prize) {
            max = Math.floor(store.miles / item.prize.points);
            if (max > item.prize.prize_count) max = item.prize.prize_count;
            if (item.prize.one_per_user) max = 1;
          }
          
          const one_per_user_invalid = item.qty > 1 && !!(item.prize && item.prize.one_per_user);
          
          return (
            <div key={index} className="mb-3">
              <Row>
                <Col xs={ 9 } sm={ 8 }>
                  <Label>Prize {index + 1}</Label>
                  <Select 
                    value={ item.prize }
                    options={ prizeOptions }
                    openMenuOnFocus={ false }
                    onChange={ (prize) => props.updateItem(index, { ...item, prize }) }
                  />
                </Col>
        
                <Col xs={ 3 } sm={ 4 }>
                  <Label>Qty</Label>
                  <Input 
                    type='number' 
                    min={ 1 } 
                    max={ max } 
                    required
                    value={ item.qty } 
                    onChange={ (e) => props.updateItem(index, { ...item, qty: parseInt(e.target.value) || 1 }) } 
                  />
                  { max > 0 && !(item.prize && item.prize.one_per_user) && <div className='invalid-message'>1 - <NumberDisplay value={ max } /></div> }
                  { one_per_user_invalid && <div className='invalid-message'>1 per soldier</div> }
                </Col>
              </Row>
              
              {items.length > 1 && (
                <Row>
                  <Col xs={12} className="text-end">
                    <Button 
                      color="danger" 
                      size="sm" 
                      onClick={() => props.removeItem(index)}
                      className="mt-2"
                    >
                      Remove Item
                    </Button>
                  </Col>
                </Row>
              )}
            </div>
          );
        })}
        
        <Row className="mb-3">
          <Col xs={12} className="text-center">
            <Button 
              color="outline-primary" 
              size="sm" 
              onClick={props.addItem}
            >
              Add Another Item
            </Button>
          </Col>
        </Row>
  
        {exceedsMiles && (
          <Row className="mb-3">
            <Col xs={12}>
              <div className="alert alert-danger">
                <strong>Insufficient Miles:</strong> Total order cost ({total.toLocaleString()} miles) exceeds available miles ({store.miles.toLocaleString()} miles). 
                Please reduce quantities or remove items.
              </div>
            </Col>
          </Row>
        )}
        
        <Row id='total-row'>
          <Col xs={ 6 } sm={ 4 }>
            <Label>Soldier's Miles</Label>
            <NumberDisplay className='total' value={ store.miles } />
          </Col>
  
          <Col xs={ 6 } sm={ 4 }>
            <Label>Final Price</Label>
            <NumberDisplay className='total' value={ total } />
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
