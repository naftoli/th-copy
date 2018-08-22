import React, { Component } from 'react';
import PropTypes  from 'prop-types';
// Components
import { Input, Row, Col, Label } from 'reactstrap';
import Cards from 'react-credit-cards';
// Functions
import classnames from 'classnames';
import Payment from 'payment';
// styles
import './CCForm.scss';

class CCForm extends Component {

  static propTypes = {
    onInputChange: PropTypes.func.isRequired,
    show: PropTypes.bool
  }

  static defaultProps = {
    show: true
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
    const classNames = classnames('CCForm', {'show': this.props.show})
    return (
      <div className={ classNames } ref={ this.formRef }>
        <div id='preview'>
          <Cards number={number} name={name} expiry={expiry} cvc={cvc} focused={focused} 
            acceptedCards={['visa', 'mastercard', 'amex', 'discover']}/>
        </div>
        <div id='form'>
          <Row>
            <Col xs='12'>
              <Label>Card Number</Label>
              <Input type='tel' name='number' placeholder='4725 9182 9976 7854' {...inputProps}/>
            </Col>
            <Col xs='7'>
              <Label>Expiration</Label>
              <Input type='tel' name='expiry' placeholder='MM / YY' {...inputProps}/>
            </Col>
            <Col xs='5'>
              <Label>CVC</Label>
              <Input type='tel' name='cvc' placeholder='CVC' {...inputProps}/>
            </Col>
          </Row>
        </div>
      </div>
    );
  }
}

export default CCForm;
