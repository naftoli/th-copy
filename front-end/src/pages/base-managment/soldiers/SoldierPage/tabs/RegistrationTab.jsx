import React from 'react';
import PropTypes from 'prop-types';

import { Row, Col, TabPane, Alert } from 'reactstrap';
import { CurrencyDisplay, DateDisplay } from 'components/ui';

export const RegistrationTab = ({ soldier, tabId }) => {
  const { user_registered, start_date, registrationCharges } = soldier;

  return (
    <TabPane id='RegistrationTab' tabId={tabId}>
      <Row>
        <Col sm={6}>
          <label>Member Since:</label>
          <h4>{start_date || 'N/A'}</h4>
        </Col>
        <Col sm={6}>
          <label>Latest CTH Registration:</label>
          <h4>{user_registered ? <DateDisplay value={user_registered} format='l LT' /> : 'N/A'}</h4>
        </Col>
      </Row>

      <p className='title'>Registration Charges</p>

      {registrationCharges && registrationCharges.length > 0 &&
        <Row>
          {registrationCharges.map((charge, index) =>
            <RegistrationCharge key={index} charge={charge} />
          )}
        </Row>
      }

      {(!registrationCharges || registrationCharges.length === 0) &&
        <Alert color='danger'>No Charges Found.</Alert>
      }

    </TabPane>
  )
}

RegistrationTab.propTypes = {
  soldier: PropTypes.object,
}

const RegistrationCharge = props => {
  let {
    type, year, amount, date, school_name,
    school_number, trans_id, response
  } = props.charge;

  let transId, accountNumber, accountType;
  try {
    response = JSON.parse(response);
    transId = response.transactionResponse.transId;
    accountType = response.transactionResponse.accountType;
    accountNumber = response.transactionResponse.accountNumber;
  } catch (e) { }

  return (
    <Col sm={6} xl={4}>
      <div className='registration-charge'>
        <p>
          <strong>Registration Type:</strong> {type} {year}
        </p>
        <p>
          <strong>Amount Paid: </strong><CurrencyDisplay value={amount} />
        </p>
        <p>
          <strong>Date Paid: </strong><DateDisplay value={date} format='l LT' />
        </p>
        <p>
          <strong>To Base: </strong>{school_name} ({school_number})
        </p>
        <p>
          <strong>Transaction # </strong>{transId || trans_id || 'N/A'}
        </p>
        {accountType &&
          <p>
            <strong>Charged To: </strong>{accountNumber} ({accountType})
          </p>
        }
        {!accountType &&
          <p>
            <strong>Response: </strong>{response || 'N/A'}
          </p>
        }
      </div>
    </Col>
  );
}
