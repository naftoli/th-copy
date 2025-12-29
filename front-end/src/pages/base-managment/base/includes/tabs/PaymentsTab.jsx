import React, { useState } from 'react';
// components
import { FontAwesome } from 'components/ui';
import { CCForm } from 'components/functional/payments';
import { Row, Col, Button, TabPane } from 'reactstrap';
import Cards from 'react-credit-cards';
// network
import { deletePayment, addPayment } from 'store/base/bases/operations';
// functions
import { toast } from 'react-toastify';
import { validateCC } from 'functions/validations';

const CardDisplay = ({ info, profileId, onDelete }) => {
  let issuer, number;
  if (info.bankAccount) {
    issuer = 'bank';
    number = info.bankAccount.accountNumber.replace('XXXX', '**** **** **** ');
  } else {
    issuer = info.creditCard.cardType === 'AmericanExpress' ? 'Amex' : info.creditCard.cardType.toLowerCase();
    number = info.creditCard.cardNumber.replace('XXXX', '**** **** **** ');
  }

  const onClick = (e) => onDelete(profileId);

  return (
    <div className='CardDisplay'>
      <Cards
        number={number} preview
        name={' '} expiry={''} cvc={'****'}
        issuer={issuer} focused='number' />
      <Button color='danger' role='button' onClick={onClick}>
        <FontAwesome icon='trash' /> Delete Card
      </Button>
    </div>
  )
}

export const PaymentsTab = ({
  profile,
  isAdmin,
  tabId,
  schoolId,
  refresh
}) => {
  const [ccInfo, setCcInfo] = useState({});

  const updateCC = (info) => {
    setCcInfo(info);
  }

  const addCC = () => {
    return validateCC(ccInfo).then(payment => addPayment(schoolId, payment))
      .then(res => refresh())
      .catch(error => {
        toast.error(error.message);
        return Promise.reject(error);
      });
  }

  const deleteCard = (payment_profile_id) => {
    return deletePayment(schoolId, payment_profile_id)
      .then(res => refresh())
      .catch(error => toast.error(error.message));
  }

  let cards;
  if (profile && profile.paymentProfiles.length > 0) {
    console.log(profile.paymentProfiles)
    cards = profile.paymentProfiles.map((profile, index) =>
      <CardDisplay key={index}
        info={profile.payment}
        profileId={profile.customerPaymentProfileId}
        onDelete={deleteCard} />
    )
  }

  return (
    <TabPane tabId={tabId}>
      <div id='PaymentsTab'>

        {profile && isAdmin &&
          <div id='payment-profile'>
            <p className='title'>Payment Profile</p>
            <Row>
              <Col xs={6} sm={2}>
                <strong>Profile ID</strong>
                <p>{profile.customerProfileId}</p>
              </Col>
              <Col xs={6} sm={2}>
                <strong>TH ID</strong>
                <p>{profile.merchantCustomerId}</p>
              </Col>
              <Col xs={6} sm={4}>
                <strong>E-mail Address</strong>
                <p>{profile.email}</p>
              </Col>
              <Col xs={6} sm={4}>
                <strong>Description</strong>
                <p>{profile.description}</p>
              </Col>
            </Row>
          </div>
        }

        <div id='add-card'>
          <p className='title'>Add new card</p>
          <CCForm
            onSubmit={addCC}
            value={ccInfo}
            onChange={updateCC} />
        </div>

        {!!cards &&
          <div id='on-file'>
            <p className='title'>Cards on file</p>
            <Row id='credit-cards'>
              {cards}
            </Row>
          </div>
        }
      </div>
    </TabPane>
  )
}
