import React, { Component } from 'react';
import { Card, CardText, CardBody, CardTitle, CardSubtitle } from 'reactstrap';
import { Spinner, Radio } from 'components/ui';
import classnames from 'classnames';
import './styles/PaymentForm.scss';

// sub-components
const CCLogos = () => (
  <div className='CCLogos'>
    <i className='fab fa-cc-visa' />
    <i className='fab fa-cc-mastercard' />
    <i className='fab fa-cc-discover' />
    <i className='fab fa-cc-amex' />
  </div>
);

const CCOption = ({ onChange, checked, id, name, value, payment }) => {
  const inputProps = { onChange, checked, id, name, value };
  const { cardNumber, cardType } = payment;
  return (
    <div className={`CCOption ${cardType ? cardType.toLowerCase() : ''}`}>
      <Radio {...inputProps}>
        {cardType} ending in {cardNumber.replace(/\D/g,'')}
      </Radio>
    </div>
  )
}

class PaymentForm extends Component {

  state = { loading: false, payments: [] }

  componentDidMount() {
    this.loadCards();
  }

  loadCards = () => {
    this.setState({ loading: true });
    setTimeout(() => {
      this.setState({
        loading: false,
        payments: [
          { customerPaymentProfileId: 1503990984 , payment: {
            creditCard: { cardNumber: "XXXX9253", expirationDate: "XXXX", cardType: "Visa"}}},
          { customerPaymentProfileId: 1503957626 , payment: {
            creditCard: { cardNumber: "XXXX1111", expirationDate: "XXXX", cardType: "Discover"}}},
          { customerPaymentProfileId: 1503957626 , payment: {
            creditCard: { cardNumber: "XXXX1111", expirationDate: "XXXX", cardType: "MasterCard"}}},
          { customerPaymentProfileId: 1503957626 , payment: {
            creditCard: { cardNumber: "XXXX1111", expirationDate: "XXXX", cardType: "AmericanExpress"}}}
        ]
      });
    }, 1000)
  }

  render() {
    const { loading, payments } = this.state;
    return (
      <Card className='PaymentForm'>
        <CardBody>
          <CardTitle>Payment Method</CardTitle>
          { loading && <Spinner size='5' />}
          { !loading && 
            <div className='CCOptions'>
              { payments.map( (payment, index) => 
                <CCOption payment={ payment.payment.creditCard } key={index} 
                  id='payment-profile' name='payment-profile'/>
              ) }
            </div>
          }
        </CardBody>
      </Card>
    );
  }
}

export default PaymentForm;