import React, { useState, useEffect } from 'react';

import { Checkbox } from 'components/inputs';
import { Callout, CurrencyDisplay } from 'components/ui';
import { Row, Col, TabPane, UncontrolledTooltip, Button } from 'reactstrap';

import { NavigationRow } from '../../rows/registration/NavigationRow';
import { PaymentForm } from 'components/functional/payments/PaymentForm';

import { getTotal, getCart } from './functions';
import { onCheckboxChange } from 'functions/events';

export const PaymentTab = (props) => {
  const {
    cc, tabId, base, back, discount,
    onStateUpdate, updateSiddurGift
  } = props;
  const { customerProfile, balance } = base;

  const [terms, setTerms] = useState({
    meet: 0, tools: 0, wwtc: 0, yan: 0, mivtzoim: 0, whatsapp: 0, data: 0, reg: 0, bc: 0, siddur_gift: 0
  });

  useEffect(() => {
    if (props.terms) {
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setTerms(prev => ({ ...prev, ...props.terms }));
    }
  }, [props.terms]);

  const onTermsChange = onCheckboxChange((update) => {
    // calculate new terms based on current state and update
    const newTerms = { ...terms, ...update };

    // check that all the checkboxes where checked except for the siddur gift
    const allTerms = Object.keys(newTerms)
      .filter(k => k !== 'siddur_gift')
      .map(k => newTerms[k])
      .reduce((a, b) => a && b, true);

    setTerms(newTerms);
    // update the master state.
    onStateUpdate('terms')(allTerms);
  });

  const checkTerms = () => {
    console.log(terms)
    // make sure that if the yearly gift is checked, we alert the user
    if (terms.siddur_gift) {
      const msg = "I realize I have committed to learning Peirush Hamilos this year, " +
        "thanks to the Siddurim sponsored by Rabbi Moshe and Ruti Weiss.\nIf you are not committed, please click 'cancel'.";
      if (window.confirm(msg)) updateSiddurGift(1);
      else {
        updateSiddurGift(0, false)
        setTerms(prev => ({
          ...prev,
          siddur_gift: 0
        }))
      }
      // this.props.register()
    } else {
      updateSiddurGift(0);
      // this.props.register()
    }
  }

  const total = getTotal(base, true, discount);
  const cart = getCart(base);

  const checkboxProps = { onChange: onTermsChange }

  return (
    <TabPane tabId={tabId} id='PaymentTab'>

      <Callout title='Terms And Conditions' className='terms'>
        <Checkbox required checked={!!terms.bc} name='bc' {...checkboxProps}>
          I am the base commander responsible for supervising Tzivos Hashem,
          and I pledge to fully understand the goal and mission of Tzivos Hashem
          and how it works with my base's (school's) curriculum.
        </Checkbox>

        <Checkbox required checked={!!terms.meet} name='meet' {...checkboxProps}>
          I am fully committed to the ongoing growth of Tzivos Hashem on our base (school)
          and will attend the monthly base commanders meetings.
        </Checkbox>

        <Checkbox required checked={!!terms.wwtc} name='wwtc' {...checkboxProps}>
          I am fully committed to try my best to reach my school’s monthly
          Shabbos Mevorchim Tehillim quota goal, and I understand that the entire army
          relies on my base to do their part for the Army-Wide Goal to be reached.
        </Checkbox>

        <Checkbox required checked={!!terms.yan} name='yan' {...checkboxProps}>
          I am fully committed to try my best to reach my school’s Tanya Baal Peh goal for Yud Alef Nissan,
          and I understand that the entire army relies on my base to do their part for the Army-Wide Goal to be reached.
        </Checkbox>

        <Checkbox required checked={!!terms.mivtzoim} name='mivtzoim' {...checkboxProps}>
          I am fully committed to try my best to reach my school’s Mivtzoim quotas
        </Checkbox>

        <Checkbox required checked={!!terms.whatsapp} name='whatsapp' {...checkboxProps}>
          I understand that it is my responsibility to check the messages posted by Headquarters on the BC Info Whatsapp group. This is a group that only HQ posts on.
        </Checkbox>

        <Checkbox required checked={!!terms.tools} name='tools' {...checkboxProps}>
          I ensure that I will provide all my teachers email addresses and cell phone
          numbers so Tzivos Hashem can be in touch with them to provide resources.
        </Checkbox>

        <Checkbox required checked={!!terms.reg} name='reg' {...checkboxProps}>
          I agree for my card to be charged the registration fee for every student that I register
          into the Tzivos Hashem program from my base (school).
          [Parents who register directly will pay their own registration fee(s).]
        </Checkbox>

        <Checkbox required checked={!!terms.data} name='data' {...checkboxProps}>
          I understand that Tzivos Hashem reserves the right to use and store any data that I upload
          for this base (school). And that they may do with that data whatever it wants to.
        </Checkbox>

        <Checkbox checked={!!terms.siddur_gift} name='siddur_gift' {...checkboxProps}>
          OPTIONAL: My school is committed to learning Peirush Hamilos in my school with the Tzivos Hashem Peirush Hamilos Curriculum
          and I would like to receive a Siddur for every chayol in my school that passes the tests. (Sponsored by Rabbi Moshe and Ruti Weiss)
        </Checkbox>
      </Callout>

      <Row id='totals'>
        <Col lg={4}>
          <div className='totals'>
            <p className='title'>
              Final Summary
            </p>

            <ul className='cart'>
              {cart.map(item =>
                <li key={item.name}>
                  {item.name}: <CurrencyDisplay value={item.price} />
                </li>
              )}

              {!!balance &&
                <li id='balance'>
                  Outstanding Balance: <CurrencyDisplay value={balance} />
                </li>
              }

              {discount > 0 &&
                <li>
                  Discount: <CurrencyDisplay value={-discount} />
                </li>
              }

              <li className='total'>
                Total: <CurrencyDisplay value={total} />
              </li>
            </ul>
          </div>

          {!!balance &&
            <UncontrolledTooltip target='#balance' autohide={false}>
              If you have questions about this charge please contact{' '}
              <a href='mailto:accounting@tzivoshashem.org?subject=Registration%20Balance' target='_blank' rel="noopener noreferrer">
                accounting@tzivoshashem.org
              </a>.
            </UncontrolledTooltip>
          }
        </Col>
        {total > 0 &&
          <Col lg={8}>
            <PaymentForm
              value={cc}
              onChange={onStateUpdate('cc')}
              payments={customerProfile && customerProfile.paymentProfiles} />
          </Col>
        }
      </Row>

      <NavigationRow back={back}>
        <Button color='primary'
          onClick={checkTerms} disabled={!props.terms}>
          {total > 0 ? 'Pay and' : ''} Register
        </Button>
      </NavigationRow>
    </TabPane>
  );
}
