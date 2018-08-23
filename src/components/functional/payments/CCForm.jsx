import React, { Component } from 'react';
import PropTypes  from 'prop-types';
// Components
import Cards from 'react-credit-cards';
import { FontAwesome } from 'components/ui';
import { Input, Row, Col, Label, Button } from 'reactstrap';
// Functions
import classnames from 'classnames';
import Payment from 'payment';
// styles
import './CCForm.scss';

class CCForm extends Component {

  static propTypes = {
    onChange: PropTypes.func.isRequired,
    value: PropTypes.object.isRequired,
    show: PropTypes.bool
  }

  static defaultProps = {
    show: true,
    value: {}
  }

  state = { focused: '' };
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
    let { value, onChange } = this.props;

    if (target.name === 'number')
      value[target.name] = target.value.replace(/ /g, '');
    else if (target.name === 'expiry')
      value[target.name] = target.value.replace(/ |\//g, '');
    else
      value[target.name] = target.value;
    
    onChange( value );
  };
  
  render() {
    const { value, onSubmit } = this.props;
    const { name, number, expiry, cvc } = value;
    const inputProps = {
      onKeyUp: this.handleInputChange,
      onFocus: this.handleInputFocus
    }
    const classNames = classnames('CCForm', {'show': this.props.show})
    return (
      <div className={ classNames } ref={ this.formRef }>
        <div id='preview'>
          <Cards 
            cvc={ cvc || '' }
            name={ name || '' }
            number={ number || '' }
            expiry={ expiry || '' }
            focused={ this.state.focused } 
            acceptedCards={['visa', 'mastercard', 'amex', 'discover']}/>
        </div>
        <div id='form'>
          <Row>
            <Col xs='12'>
              <Label>Card Number</Label>
              <Input type='tel' name='number' placeholder='4725 9182 9976 7854' {...inputProps}/>
            </Col>
            <Col xs={ onSubmit ? 5 : 7 }>
              <Label>Expiration</Label>
              <Input type='tel' name='expiry' placeholder='MM / YY' {...inputProps}/>
            </Col>
            <Col xs={ onSubmit ? 3 : 5 }>
              <Label>CVC</Label>
              <Input type='tel' name='cvc' placeholder='CVC' {...inputProps}/>
            </Col>
            { onSubmit &&
              <Col xs={ 4 } className='save'>
                <Button color='primary' onFocus={ this.handleInputFocus } name='number'>
                  <FontAwesome icon='plus'/> Add
                </Button>
              </Col>
            }
          </Row>
        </div>
      </div>
    );
  }
}

export default CCForm;
