import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import { SaveButton } from 'components/buttons';
import { Select, PhoneNumber } from 'components/inputs';
import {
  Modal, ModalHeader, ModalBody, ModalFooter,
  Row, Col, Input, Label, Alert, FormFeedback
} from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { getChildOptions } from './misc/functions';
import { onInputChange, onMultiSelectChange } from 'functions/events';
import { getParents, createParent } from 'store/base/parents/operations';
import { isHQ } from 'functions/login';

const initialState = {
  father: '', mother: '', last: '', email: '',
  father_cell: '', mother_cell: '', children: [],
  error: false, saving: false
};

const NewParentModal = (props) => {
  const {
    isOpen, toggle, login, availableChildren,
    createParent: createParentAction, getParents: getParentsAction
  } = props;

  const [state, setState] = useState({ ...initialState });
  // Destructure for easier usage
  const { father, mother, last, email, father_cell, mother_cell, children, error, saving } = state;

  const [touched, setTouched] = useState({});

  useEffect(() => {
    if (!isOpen) {
      // Reset state on close
      setState({ ...initialState });
      setTouched({});
    }
  }, [isOpen]);

  const updateState = (update) => setState(prev => ({ ...prev, ...update }));

  // Handlers
  const handleChange = onInputChange(updateState);
  const handleSelectChange = onMultiSelectChange(updateState);
  const onBlur = (field) => () => setTouched(prev => ({ ...prev, [field]: true }));

  const onChangeHQ = (e) => {
    const inputValue = e.target.value;
    // Filter out empty strings and validate serial number format (simple storage, validation happens on render/submit)
    const serialNumbers = inputValue.split(',')
      .map(serial => serial.trim())
      .filter(serial => serial !== '');

    // Actually, original code filtered *valid* serials only into state.
    // "filter(serial => serial !== '' && /^7\d{6}$/.test(serial))"
    // So if I type "777", it filters it out immediately? That seems annoying for typing.
    // But let's replicate exact behavior if it was working for them.
    // Wait, if I type "7", filtering "7" out means I can't type it!
    // The original code `onChangeHQ` filtered immediately!
    // Line 67: `filter(serial => serial !== '' && /^7\d{6}$/.test(serial))`
    // This logic basically prevents typing unless you paste a full valid list?
    // OR it was intended for *saving*?
    // Let's re-read original `onChangeHQ`.
    /*
      const serialNumbers = inputValue.split(',')
      .map(serial => serial.trim())
      .filter(serial => serial !== '' && /^7\d{6}$/.test(serial));
      this.setState({ children: serialNumbers });
    */
    // If I type "1", it's filtered. State becomes empty. Input value becomes empty.
    // This logic seems BROKEN for interactive typing if `value={children.join(', ')}`.
    // If user types "7", it vanishes.
    // Use `defaultValue` or separate `inputValue` state?
    // Original component had `value={children.join(', ')}`.
    // So user literally could not type purely? They probably copy-pasted lists.
    // OR the regex was looser? `^7\d{6}$`.
    // I will assume this logic was indeed "Paste Only" or flawed.
    // I will improve it: Store raw input string for typing, validate on blur/submit.
    // Actually, `children` is expected to be array of strings for `createParent`.
    // I'll stick to original logic but maybe relax it? 
    // No, I must be faithful OR fix obvious bugs. This looks like a bug.
    // But I'll fix it by storing raw input for HQ mode if possible?
    // Actually, let's just use `inputValue` state for HQ text field.
  };

  // Improving HQ Input:
  const [hqInput, setHqInput] = useState('');

  const onChangeHQImproved = (e) => {
    const val = e.target.value;
    setHqInput(val);
    // Parse valid children from it
    const validChildren = val.split(',')
      .map(s => s.trim())
      .filter(s => /^7\d{6}$/.test(s));
    updateState({ children: validChildren });
  };


  const handleSubmit = (e) => {
    e.preventDefault();
    if (father === '' && mother === '')
      return updateState({ error: `Please enter either the father or mother's name` });
    if (children.length === 0 && !isHQ(login.code)) // HQ logic might be different? Original code checked children.length === 0 unconditionally.
      // But for HQ input, if `hqInput` has invalid stuff, children might be empty.
      return updateState({ error: `Please select some soldiers.` });

    // For HQ, if they typed stuff but regex failed, children is empty.
    if (children.length === 0)
      return updateState({ error: `Please select some soldiers.` });

    updateState({ saving: true });

    createParentAction(state) // pass full state as before
      .then(() => {
        toggle();
        getParentsAction();
        setHqInput('');
      })
      .catch(error => {
        updateState({ error: error.message, saving: false });
        if (!isOpen) toast.error(error.message);
      });
  };

  // Validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const isEmailInvalid = touched.email && email && !emailRegex.test(email);

  const nameRegex = /^[a-zA-Z\s.]{2,}$/;
  const isFatherInvalid = touched.father && father && !nameRegex.test(father);
  const isMotherInvalid = touched.mother && mother && !nameRegex.test(mother);

  const lastRegex = /^[a-zA-Z\s.]{3,}$/;
  const isLastInvalid = touched.last && last && !lastRegex.test(last);

  const phoneRegex = /^(?:\(\d{3}\)|\d{3})[- ]?\d{3}[- ]?\d{4}$/;
  const isFatherCellInvalid = touched.father_cell && father_cell && !phoneRegex.test(father_cell);
  const isMotherCellInvalid = touched.mother_cell && mother_cell && !phoneRegex.test(mother_cell);

  const options = getChildOptions(availableChildren);

  return (
    <Modal isOpen={isOpen} toggle={toggle} centered id='NewParentModal'>
      <ModalHeader toggle={toggle}>Create New Parent</ModalHeader>
      <form onSubmit={handleSubmit}>
        <ModalBody>
          <Row>
            <Col xs={6}>
              <Label>E-Mail Address / Username</Label>
              <Input required name='email'
                value={email} type='email' onChange={handleChange}
                onBlur={onBlur('email')}
                invalid={isEmailInvalid}
              />
              <FormFeedback>Please enter a valid E-mail address</FormFeedback>
            </Col>

            <Col xs={6}>
              <Label>Password</Label>
              <Input name='password' value='p1234' disabled />
            </Col>

            <Col xs={6}>
              <Label>Father</Label>
              <Input name='father' value={father} onChange={handleChange}
                pattern='^[a-zA-Z\s.]{2,}$' title="Two or more letters"
                onBlur={onBlur('father')}
                invalid={isFatherInvalid}
              />
              <FormFeedback>Please enter 2 or more letters</FormFeedback>
            </Col>

            <Col xs={6}>
              <Label>Mother</Label>
              <Input name='mother' value={mother} onChange={handleChange}
                pattern='^[a-zA-Z\s.]{2,}$' title="Two or more letters"
                onBlur={onBlur('mother')}
                invalid={isMotherInvalid}
              />
              <FormFeedback>Please enter 2 or more letters</FormFeedback>
            </Col>

            <Col xs={12}>
              <Label>Last Name</Label>
              <Input required name='last'
                value={last} onChange={handleChange}
                pattern='^[a-zA-Z\s.]{3,}$' title="Three or more letters"
                onBlur={onBlur('last')}
                invalid={isLastInvalid}
              />
              <FormFeedback>Please enter 3 or more letters</FormFeedback>
            </Col>

            <Col xs={6}>
              <Label>Father Cell Phone</Label>
              <PhoneNumber required name='father_cell'  // Name was 'home' in original code props map? 
                // Original: <PhoneNumber required name='home' value={father_cell} ... />. 
                // Wait, state key is `father_cell`. `onInputChange` uses name.
                // If name='home', then `onUpdate` receives `{ home: val }`.
                // But state has `father_cell`!
                // Original code line 135: name='home'.
                // Original state line 19: father_cell.
                // This IMPLIES existing bug: typing in Father Cell updated 'home' in state, but render used 'father_cell'.
                // So Input would be controlled but never update? Or `onUpdate` merges `{home}` into state.
                // Render uses `father_cell`.
                // So user types, state gets `home`. `father_cell` remains empty. Input is stuck?
                // Unless `onInputChange` somehow mapped `home` to `father_cell`? No, it's generic.
                // I WILL FIX THIS. Name should be `father_cell`.
                value={father_cell} onChange={handleChange}
                onBlur={onBlur('father_cell')}
                invalid={isFatherCellInvalid}
              />
              <FormFeedback>Please enter a valid phone number</FormFeedback>
            </Col>

            <Col xs={6}>
              <Label>Mother Cell Phone</Label>
              <PhoneNumber required name='mother_cell' // Original name='cell'. State 'mother_cell'. Same bug?
                // Checks original state: `mother_cell`.
                // I will fix to `mother_cell`.
                value={mother_cell} onChange={handleChange}
                onBlur={onBlur('mother_cell')}
                invalid={isMotherCellInvalid}
              />
              <FormFeedback>Please enter a valid phone number</FormFeedback>
            </Col>

            {!isHQ(login.code) &&
              <Col xs={12}>
                <Label>Children</Label>
                <Select required isMulti selected={children}
                  options={options} tabSelectsValue={false}
                  onChange={handleSelectChange('children')} />
              </Col>
            }

            {isHQ(login.code) &&
              <Col xs={12}>
                <Label>Children: Serial numbers comma seperated.</Label>
                <Input required name='children_hq'
                  value={hqInput} // Use local input state
                  onChange={onChangeHQImproved}
                  onBlur={onBlur('children_hq')}
                  invalid={touched.children_hq && children.length === 0 && hqInput.length > 0}
                // Invalid if typed but no valid children parsed?
                />
                <FormFeedback>1 or more valid serial numbers</FormFeedback>
              </Col>
            }

            {error &&
              <div id='errors'>
                <Alert color='danger'>{error}</Alert>
              </div>
            }
          </Row>
        </ModalBody>
        <ModalFooter>
          <SaveButton text='Create' saving={saving} />
        </ModalFooter>
      </form>
    </Modal>
  );
};

const mapStateToProps = ({ base, login }) => ({
  availableChildren: base.parents.children,
  login: login.current_login
});

const mapDispatchToProps = { getParents, createParent };

export default connect(mapStateToProps, mapDispatchToProps)(NewParentModal);
