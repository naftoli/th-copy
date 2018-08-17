import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Card, CardBody, CardTitle } from 'reactstrap';
import { Radio } from 'components/inputs';
import { Spinner } from 'components/ui';
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
  const message = cardNumber ? `${payment.cardType} ending in ${cardNumber}` : 'Add New Credit Card';
  const indentifed = payment && payment.cardType ? payment.cardType.toLowerCase() : '';
  // and render radio
  return (
    <div className={`CCOption ${indentifed}`}>
      <Radio {...inputProps}>{ message }</Radio>
    </div>
  )
}

class ProfileForm extends Component {

  static propTypes = {
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
    const { onProfileSelected } = this.props;
    if ( event.target.value ) {
      return onProfileSelected( event.target.value );
    }
    onProfileSelected( false );
  }

  loadCards = () => {
    this.props.getPaymentProfiles()
    .then( profiles => {
      if ( profiles.length > 0 ) {
        this.props.onProfileSelected( profiles[0].customerPaymentProfileId );
      } else {
        this.props.onProfileSelected( false );
      }
    });
  }

  render() {
    const { loading, payments, value } = this.props;
    return (
      <Card className='ProfileForm'>
        <CardBody>
          <CardTitle>Payment Method</CardTitle>
          { loading && <Spinner size='5' />}
          { !loading && 
            <div className='CCOptions'>
              { payments.map( (payment, index) => 
                <CCOption payment={ payment.payment.creditCard } key={index} onChange={ this.onChange }
                  id='payment-profile' name='payment-profile' value={payment.customerPaymentProfileId} 
                  checked={ payment.customerPaymentProfileId === value } />
              ) }
              <CCOption id='payment-profile' name='payment-profile' onChange={ this.onChange } 
                checked={ value === false }/>
            </div>
          }
          { !loading && this.props.children }
        </CardBody>
      </Card>
    );
  }
}

const mapStateToProps = state => ({
  ...state.payments
})

export default connect( mapStateToProps, { getPaymentProfiles } )( ProfileForm );
