import React, { Component } from 'react';
import { Input, Card, CardBody, CardTitle } from 'reactstrap';
import Cards from 'react-credit-cards';
import Payment from 'payment';
import { Spinner, Radio } from 'components/ui';
import './styles/PaymentForm.scss';

// sub-components
const CCOption = ({ onChange, checked, id, name, value, payment }) => {
  // format props
  const inputProps = { onChange, checked, id, name, value };
  const cardNumber = payment ? payment.cardNumber.replace(/\D/g,'') : '';
  const message = cardNumber ? `${payment.cardType} ending in ${cardNumber}` : 'New Credit Card';
  const indentifed = payment && payment.cardType ? payment.cardType.toLowerCase() : '';
  // and render radio
  return (
    <div className={`CCOption ${indentifed}`}>
      <Radio {...inputProps}>{ message }</Radio>
    </div>
  )
}

const CCForm = ( props ) => {
  return (
    <div className={`CCForm`}></div>
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
                  id='payment-profile' name='payment-profile' value={payment.customerPaymentProfileId} />
              ) }
              <CCOption id='payment-profile' name='payment-profile' />
            </div>
          }
          {/* <Cards
            number={'4*** **** **** 1111'}
            name={'Menachem'}
            expiry={'XXXX'}
            cvc={559}
            focused={'name'}
          /> */}
        </CardBody>
      </Card>
    );
  }
}

export default PaymentForm;
