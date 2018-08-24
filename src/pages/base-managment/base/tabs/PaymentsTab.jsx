import React, { Component } from 'react';
// components
import { FontAwesome } from 'components/ui';
import { CCForm } from 'components/functional/payments';
import { Row, Col, Button, TabPane } from 'reactstrap';
import Cards from 'react-credit-cards';
// network
import { deletePayment, addPayment } from 'store/bases/operations';
// functions
import { toast } from 'react-toastify';
import { validateCC } from 'functions/validations';

const CardDisplay = ({ cardType, cardNumber, profileId, onDelete }) => {
  let issuer, number;
  // Amex is special no?
  if ( cardType === 'AmericanExpress' ) {
    issuer = 'amex'; // fix AMEX
    number = cardNumber.replace( 'XXXX', '**** ****** *' );
  } else {
    issuer = cardType.toLowerCase(); // fix case
    number = cardNumber.replace( 'XXXX', '**** **** **** ' );
  }

  const onClick = ( e ) => onDelete( profileId );

  return (
    <div className='CardDisplay'>
      <Cards
        number={ number } preview
        name={' '} expiry={''} cvc={'****'}
        issuer={ issuer } focused='number' />
      <Button color='danger' role='button' onClick={ onClick }>
        <FontAwesome icon='trash'/> Delete Card
      </Button>
    </div>
  )
}

export class PaymentsTab extends Component {

  state = {
    ccInfo: {}
  }

  updateCC = ccInfo => {
    this.setState({ ccInfo });
  }

  addCC = () => {
    const { ccInfo } = this.state;
    return validateCC( ccInfo ).then( payment => addPayment( this.props.schoolId, payment ) )
    .then( res => this.props.refresh() )
    .catch( error => toast.error( error.message ) );
  }

  deleteCard = payment_profile_id => {
    return deletePayment( this.props.schoolId, payment_profile_id )
    .then( res => this.props.refresh() )
    .catch( error => toast.error( error.message ) );
  }

  render(){
    const { profile, isAdmin, tabId } = this.props;

    let cards;
    if ( profile && profile.paymentProfiles.length > 0 ) {
      cards = profile.paymentProfiles.map( ( profile, index ) => 
        <CardDisplay key={ index } 
          { ...profile.payment.creditCard } 
          profileId={ profile.customerPaymentProfileId }
          onDelete={ this.deleteCard } />
      );
    }

    return (
      <TabPane tabId={ tabId }>
        <div id='PaymentsTab'>

          { profile && isAdmin && 
            <div id='payment-profile'>
              <p className='title'>Payment Profile</p>
              <Row>
                <Col xs={6} sm={2}>
                  <strong>Profile ID</strong>
                  <p>{ profile.customerProfileId }</p>
                </Col>
                <Col xs={6} sm={2}>
                  <strong>TH ID</strong>
                  <p>{ profile.merchantCustomerId }</p>
                </Col>
                <Col xs={6} sm={4}>
                  <strong>E-mail Address</strong>
                  <p>{ profile.email }</p>
                </Col>
                <Col xs={6} sm={4}>
                  <strong>Description</strong>
                  <p>{ profile.description }</p>
                </Col>
              </Row>
            </div>
          }

          <div id='add-card'>
            <p className='title'>Add new card</p>
            <CCForm 
              onSubmit={ this.addCC }
              value={ this.state.ccInfo }
              onChange={ this.updateCC } />
          </div>

          { !!cards && 
            <div id='on-file'>
              <p className='title'>Cards on file</p>
              <Row id='credit-cards'>
                { cards }
              </Row>
            </div>
          }
        </div>
      </TabPane>
    )
  }
}
