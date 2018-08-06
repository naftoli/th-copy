import React, { Component } from 'react';
import { Input } from 'reactstrap';
import Cards from 'react-credit-cards';
import Payment from 'payment';

class CCForm extends Component {
  render() {
    return (
      <div className={`CCForm`}>
        <Cards
          number={'4*** **** **** 1111'}
          name={'Menachem'}
          expiry={'XXXX'}
          cvc={559}
          focused={'name'}
        />
      </div>
    );
  }
}

export default CCForm;