import React from 'react';

import { Checkbox } from 'components/inputs';
import { Callout, CurrencyDisplay, FontAwesome } from 'components/ui';
import { Row, Col, TabPane, UncontrolledTooltip, Button } from 'reactstrap';

import { NavigationRow } from '../../rows/registration/NavigationRow';
import { PaymentForm } from 'components/functional/payments/PaymentForm';

import { getTotal, getCart } from './functions';

export class PaymentTab extends React.Component {
  // render the page
  render(){
    const { cc, tabId, base, back, terms } = this.props;
    const { customerProfile, balance } = base;

    const total = getTotal( base, true );
    const cart = getCart( base );

    return (
      <TabPane tabId={ tabId } id='PaymentTab'>

        <Callout title='Terms And Conditions' className='terms'>
          <ul className='checkboxes'>
            <li>I am the base commander responsible for supervising Tzivos Hashem, and I pledge to fully understand the goal and mission of Tzivos Hashem and how it works with my base's (school's) curriculum.</li>
            <li>I am fully committed to the ongoing growth of Tzivos Hashem on our base (school) and will attend the monthly base commanders meetings.</li>
            <li>I will ensure that I will provide all my teachers email addresses and cell phone numbers so we can be in touch with them to provide resources.</li>
            <li>I agree for my card to be charged the registration fee for every student that I register(s) into the Tzivos Hashem program from my base (school). [Parents who register directly will pay their own registration fee(s).]</li>
            <li>I understand that Tzivos Hashem reserves the right to use and store any data that I upload for this base (school). And that they may do with that data whatever it wants to.</li>
          </ul>

          <Checkbox checked={ terms } name='terms'
              onChange={ this.props.onStateUpdate( 'terms' ) } >
            I accept the above Terms and Conditions
          </Checkbox>
        </Callout>

        { total > 0 && 
          <Row id='totals'>
            <Col lg={4}>
              <div className='totals'>
                <p className='title'>
                  Final Summary
                </p>

                <ul className='cart'>
                  { cart.map( item =>
                    <li key={ item.name }>
                      { item.name }: <CurrencyDisplay value={ item.price } />
                    </li>
                  ) }

                  { !!balance &&
                    <li id='balance'>
                      Outstanding Balance: <CurrencyDisplay value={ balance } />
                    </li>
                  }

                  <li className='total'>
                    Total: <CurrencyDisplay value={ total } />
                  </li>
                </ul>
              </div>

              { !!balance &&
                <UncontrolledTooltip target='#balance' autohide={ false }>
                    If you have questions about this charge please contact{' '}
                    <a href='mailto:accounting@tzivoshashem.org?subject=Registration%20Balance' target='_blank' rel="noopener noreferrer">
                      accounting@tzivoshashem.org
                    </a>.
                </UncontrolledTooltip>
              }
            </Col>

            <Col lg={8}>
              <PaymentForm
                value={ cc }
                onChange={ this.props.onStateUpdate( 'cc' ) }
                payments={ customerProfile && customerProfile.paymentProfiles } />
            </Col>
          </Row>
        }

        <NavigationRow back={ back }>
          <Button color='primary' onClick={ this.props.register }>
            Pay and Register <FontAwesome icon='registered' regular />
          </Button>
        </NavigationRow>
        
      </TabPane>
    );
  }
}