import React, { Component } from 'react';
import PropTypes  from 'prop-types';
// Components
import { Input, Row, Col } from 'reactstrap';
import Cards from 'react-credit-cards';
// Functions
import classnames from 'classnames';
import Payment from 'payment';

class CCForm extends Component {

  static propTypes = {
    onInputChange: PropTypes.func.isRequired,
    show: PropTypes.bool
  }

  static defaultProps = {
    show: false
  }

  state = {
    number: '', name: '',
    expiry: '', cvc: '',
    focused: '',
  };
  formRef = React.createRef();

  componentDidMount() {
    const form = this.formRef.current;
    if ( form ) {
      Payment.formatCardNumber(form.querySelector('input[name="number"]'));
      Payment.formatCardExpiry(form.querySelector('input[name="expiry"]'));
      Payment.formatCardCVC(form.querySelector('input[name="cvc"]'));
    }
  }

  handleInputFocus = ({ target }) => {
    this.setState({ focused: target.name });
  };
  // clean up the input as it is entered
  handleInputChange = ({ target }) => {
    const callback = () => { this.props.onInputChange( this.state ) }

    if (target.name === 'number') {
      this.setState({ [target.name]: target.value.replace(/ /g, '') }, callback);
    } else if (target.name === 'expiry') {
      this.setState({ [target.name]: target.value.replace(/ |\//g, '') }, callback);
    } else {
      this.setState({ [target.name]: target.value }, callback);
    }
  };
  
  render() {
    const { name, number, expiry, cvc, focused } = this.state;
    const inputProps = {
      onKeyUp: this.handleInputChange,
      onFocus: this.handleInputFocus
    }
    const classNames = classnames('CCForm row', {'show': this.props.show})
    return (
      <div className={ classNames } ref={ this.formRef }>
        <Col xs={{size: 12, order: 12}} md={{size: 7, order: 0}}>
          <Cards number={number} name={name} expiry={expiry} cvc={cvc} focused={focused} 
            acceptedCards={['visa', 'mastercard', 'amex', 'discover']}/>
        </Col>
        <Col xs='12' md='5'>
          <Row>
            <Col xs='12'>
              <Input type='tel' name='number' placeholder='Card Number' {...inputProps}/>
            </Col>
            <Col xs='12'>
              <Input type='text' name='name' placeholder='Name' {...inputProps}/>
            </Col>
            <Col xs='7'>
              <Input type='tel' name='expiry' placeholder='MM / YY' {...inputProps}/>
            </Col>
            <Col xs='5'>
              <Input type='tel' name='cvc' placeholder='CVC' {...inputProps}/>
            </Col>
          </Row>
        </Col>
      </div>
    );
  }
}

export default CCForm;
