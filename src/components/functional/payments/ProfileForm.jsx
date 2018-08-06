import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Card, CardBody, CardTitle } from 'reactstrap';
import { Spinner, Radio } from 'components/ui';
import CCForm from './CCForm';
// functions
import propTypes from 'prop-types';
import { getPaymentProfiles } from 'store/payments/operations';
// styles
import './ProfileForm.scss';

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

class ProfileForm extends Component {

  defaultProps = {
    onProfileSelected: propTypes.func.isRequired
  }

  state = {
    loading: false, 
    payments: []
  }

  componentDidMount() {
    this.loadCards();
  }

  onChange = ( event ) => {
    debugger;
  }

  loadCards = () => {
    this.props.getPaymentProfiles();
  }

  render() {
    const { loading, payments } = this.props;
    return (
      <Card className='ProfileForm'>
        <CardBody>
          <CardTitle>Payment Method</CardTitle>
          { loading && <Spinner size='5' />}
          { !loading && 
            <div className='CCOptions'>
              { payments.map( (payment, index) => 
                <CCOption payment={ payment.payment.creditCard } key={index} onChange={ this.onChange }
                  id='payment-profile' name='payment-profile' value={payment.customerPaymentProfileId} />
              ) }
              <CCOption id='payment-profile' name='payment-profile' onChange={ this.onChange } />
            </div>
          }
        </CardBody>
      </Card>
    );
  }
}

const mapStateToProps = state => ({
  ...state.payments
})

export default connect( mapStateToProps, { getPaymentProfiles } )( ProfileForm );
