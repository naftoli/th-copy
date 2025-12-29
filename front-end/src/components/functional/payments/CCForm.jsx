import React, { useState, useRef, useEffect } from 'react';
import PropTypes from 'prop-types';
// Components
import Cards from 'react-credit-cards';
import { FontAwesome, InlineSync } from 'components/ui';
import { Input, Row, Col, Label, Button, Collapse } from 'reactstrap';
// Functions
import Payment from 'payment';
import classnames from 'classnames';
// styles
import './CCForm.scss';

const CCForm = ({
  onChange, value = {}, show = true, onSubmit
}) => {

  const [focused, setFocused] = useState('');
  const [loading, setLoading] = useState(false);
  const formRef = useRef(null);

  useEffect(() => {
    const form = formRef.current;
    if (form) {
      Payment.formatCardNumber(form.querySelector('input[name="number"]'), 16);
      Payment.formatCardExpiry(form.querySelector('input[name="expiry"]'));
      Payment.formatCardCVC(form.querySelector('input[name="cvc"]'));
    }
  }, []);

  const handleInputFocus = ({ target }) => {
    setFocused(target.name);
  };

  // clean up the input as it is entered
  const handleInputChange = ({ target }) => {
    // update the correct section
    const newValue = { ...value, [target.name]: target.value };

    onChange(newValue);
  };

  const submit = (e) => {
    e && e.preventDefault();
    // only submit if we are loading
    if (!loading) {
      setLoading(true);
      Promise.resolve(onSubmit()) // support promises and non-promises
        // if we do not remount the component on adding a card ( remove the loading screen ) then add this back.
        // .then(() => this.setState({ loading: false }))
        .catch(() => setLoading(false));
    }
  }

  const { name, number, expiry, cvc } = value;

  const inputProps = {
    onChange: handleInputChange,
    onFocus: handleInputFocus,
    required: !!onSubmit
  }
  const classNames = classnames('CCForm', { 'has-add': !!onSubmit });
  // get the text for the button
  let buttonText = <span><FontAwesome icon='plus' /> Add</span>;
  if (loading) buttonText = <InlineSync loading />;

  return (
    <Collapse className={classNames} isOpen={show}>
      <form onSubmit={submit} autoComplete='on' ref={formRef}>
        <Row>
          <Col xs='12'>
            <Label>Card Number</Label>
            <Input type='tel' name='number' value={number || ''} {...inputProps}
              placeholder='4725 9182 9976 7854' autocompletetype="cc-number" />
          </Col>
          <Col xs={7}>
            <Label>Expiration</Label>
            <Input type='tel' name='expiry' value={expiry || ''} {...inputProps}
              placeholder='MM / YY' autocompletetype="cc-exp" />
          </Col>
          <Col xs={5}>
            <Label>CVC</Label>
            <Input type='tel' name='cvc' value={cvc || ''} placeholder='CVC' {...inputProps}
              autocompletetype="cc-csc" autoComplete="off" />
          </Col>
          {onSubmit &&
            <Col xs={12} className='save'>
              <Button color='primary' onFocus={handleInputFocus} name='number'>
                {buttonText}
              </Button>
            </Col>
          }
        </Row>
      </form>
      <div id='preview'>
        <Cards
          cvc={cvc || ''}
          name={name || ''}
          focused={focused}
          number={number ? number.replace(/ /g, '') : ''}
          expiry={expiry ? expiry.replace(/ |\//g, '') : ''} />
      </div>
    </Collapse>
  );
}

CCForm.propTypes = {
  onChange: PropTypes.func.isRequired,
  value: PropTypes.object.isRequired,
  show: PropTypes.bool,
  onSubmit: PropTypes.func
}

export default CCForm;
