import React from 'react';

import { Checkbox } from 'components/inputs';
import { Callout, CurrencyDisplay, FontAwesome } from 'components/ui';
import { Row, Col, TabPane, UncontrolledTooltip, Button } from 'reactstrap';

import { NavigationRow } from '../../rows/registration/NavigationRow';
import { PaymentForm } from 'components/functional/payments/PaymentForm';

import { getTotal, getCart } from './functions';
import { onCheckboxChange } from 'functions/events';

export class PaymentTab extends React.Component {
  //* initial state
  state = {
    terms: {
      meet: 0, tools: 0, data: 0, reg: 0, bc: 0,
    }
  }

  componentDidMount(){
    if ( this.props.terms ) {
      this.setState({
        terms: {
          meet: 1, tools: 1, data: 1, reg: 1, bc: 1,
        }
      })
    }
  }

  //* handle updating the terms
  onTermsChange = onCheckboxChange( ( update ) => {
    // get the terms from the state
    let { terms } = this.state;
    // update the terms
    terms = { ...terms, ...update };
    // check that all the checkboxes where checked
    const allTerms = Object.keys( terms )
      .every( k =>  terms[k] );
    
    this.setState({ terms });
    // update the master state.
    this.props.onStateUpdate( 'terms' )( allTerms );
  });

  //* render the page
  render(){
    const { terms } = this.state;
    const { cc, tabId, base, back } = this.props;
    const { customerProfile, balance } = base;

    const total = getTotal( base, true );
    const cart = getCart( base );

    const checkboxProps = { onChange: this.onTermsChange }

    return (
      <TabPane tabId={ tabId } id='PaymentTab'>

        <Callout title='Terms And Conditions' className='terms'>
          <Checkbox checked={ terms.bc } name='bc' { ...checkboxProps }>
            I am the base commander responsible for supervising Tzivos Hashem, 
            and I pledge to fully understand the goal and mission of Tzivos Hashem
            and how it works with my base's (school's) curriculum.
          </Checkbox>

          <Checkbox checked={ terms.meet } name='meet' { ...checkboxProps }>
            I am fully committed to the ongoing growth of Tzivos Hashem on our base (school)
            and will attend the monthly base commanders meetings.
          </Checkbox>

          <Checkbox checked={ terms.tools } name='tools' { ...checkboxProps }>
            I ensure that I will provide all my teachers email addresses and cell phone
            numbers so Tzivos Hashem can be in touch with them to provide resources.
          </Checkbox>

          <Checkbox checked={ terms.reg } name='reg' { ...checkboxProps }>
            I agree for my card to be charged the registration fee for every student that I register
            into the Tzivos Hashem program from my base (school).
            [Parents who register directly will pay their own registration fee(s).]
          </Checkbox>

          <Checkbox checked={ terms.data } name='data' { ...checkboxProps }>
            I understand that Tzivos Hashem reserves the right to use and store any data that I upload
            for this base (school). And that they may do with that data whatever it wants to.
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
          <Button color='primary'
              onClick={ this.props.register } disabled={ !this.props.terms }>
            Pay and Register <FontAwesome icon='registered' regular />
          </Button>
        </NavigationRow>
      </TabPane>
    );
  }
}
