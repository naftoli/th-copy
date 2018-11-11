import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { Row, Col, TabPane, Alert } from 'reactstrap';
import { NumberDisplay, DateDisplay } from 'components/ui';

export class RegistrationTab extends Component {
  // props we are expecting for this component
  static propTypes = {
    soldier: PropTypes.object,
  }

  render() {
    const { user_registered, start_date, registrationCharges } = this.props.soldier;
    
    return (
      <TabPane id='RegistrationTab' tabId= { this.props.tabId }>
        <Row>
          <Col sm={6}>
            <label>Member Since:</label>
            <h4>{ start_date || 'N/A' }</h4>
          </Col>
          <Col sm={6}>
            <label>Latest CTH Registration:</label>
            <h4>{ user_registered ? <DateDisplay value={ user_registered } format='l LT' /> : 'N/A' }</h4>
          </Col>
        </Row>

        <p className='title'>Registration Charges</p>

        { registrationCharges.length > 0 &&
          <Row>
            { registrationCharges.map( ( charge, index ) => 
              <RegistrationCharge key={ index } charge={ charge } />
            ) }
          </Row>
        }

        { registrationCharges.length === 0 &&
          <Alert color='danger'>No Charges Found.</Alert>
        }
        
      </TabPane>
    )
  }
}

const RegistrationCharge = props => {
  let { 
    type,   year,   amount,   date,   school_name, 
    school_number,  trans_id, response 
  } = props.charge;

  let transId, accountNumber, accountType;
  try {
    response = JSON.parse( response );
    transId = response.transactionResponse.transId;
    accountType = response.transactionResponse.accountType;
    accountNumber = response.transactionResponse.accountNumber;
  } catch ( e ) {}

  return (
    <Col sm={6} xl={4}>
      <div className='registration-charge'>
        <p>
          <strong>Registration Type:</strong> { type } { year }
        </p>
        <p>
          <strong>Amount Paid: </strong><NumberDisplay value={ amount } opts={{ style: 'currency', currency: 'USD' }} />
        </p>
        <p>
          <strong>Date Paid: </strong><DateDisplay value={ date } format='l LT' />
        </p>
        <p>
          <strong>To Base: </strong>{ school_name } ({ school_number })
        </p>
        <p>
          <strong>Transaction # </strong>{ transId || trans_id || 'N/A' }
        </p>
        { accountType && 
          <p>
            <strong>Charged To: </strong>{ accountNumber } ({ accountType })
          </p>
        }
        { !accountType && 
          <p>
            <strong>Response: </strong>{ response || 'N/A' }
          </p>
        }
      </div>
    </Col>
  );
}
