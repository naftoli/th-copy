import React, { Component } from 'react';
// components
import { FontAwesome } from 'components/ui';
import { CCForm } from 'components/functional/payments';
import { Row, Col, Input, Button, ButtonGroup } from 'reactstrap';
import { loadPaymentProfiles } from 'store/payments/operations';
import Cards from 'react-credit-cards';

const extractCardPreview = ({ cardNumber = '', cardType = '' }) => {
  let issuer, number;
  // Amex is special no?
  if ( cardType === 'AmericanExpress' ) {
    issuer = 'amex'; // fix AMEX
    number = cardNumber.replace( 'XXXX', '**** ****** *' );
  } else {
    issuer = cardType.toLowerCase(); // fix case
    number = cardNumber.replace( 'XXXX', '**** **** **** ' );
  }
  // return results
  return { number, issuer };
}

const CardDisplay = props => {
  const { number, issuer } = extractCardPreview( props );

  const onClick = ( e ) => {
    props.onDelete( props.profileId );
  }

  return (
    <div className='CardDisplay'>
      <Cards
        number={ number } preview
        name={' '} expiry={''} cvc={''}
        issuer={ issuer } />
      <Button color='danger' role='button' onClick={ onClick }>
        <FontAwesome icon='trash'/> Delete Card
      </Button>
    </div>
  )
}

export class PaymentsTab extends Component {

  state = {
    newCC: {}
  }

  updateCC = newCC => {
    this.setState({ newCC });
  }

  deleteCard = event => {
    console.log( event );
  }

  render(){
    const { profile } = this.props;

    const cards = profile.paymentProfiles.map( ( profile, index ) => 
      <CardDisplay key={ index } 
        { ...profile.payment.creditCard } 
        profileId={ profile.customerPaymentProfileId }
        onDelete={ this.deleteCard } />
    );

    return (
      <div id='PaymentsTab'>
        <Row id='credit-cards'>
          { cards }
        </Row>
        {/* <p className='title'>Add new card</p>
        <CCForm onInputChange={ this.updateCC }/> */}
      </div>
    )
  }
}
