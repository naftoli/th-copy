import React, { useState, useEffect, Fragment } from 'react';
import API from "../../../../../api/api";
// components
import { Input, Row, Col, TabPane, FormFeedback } from 'reactstrap';
import { Callout } from 'components/ui';
import { NavigationRow } from '../rows/registration/NavigationRow';
import { Form, Radio } from "components/inputs";

export const PlatoonsTab = (props) => {
  const { tabId, back, onValidChange, onSubmit } = props;
  const [platoons, setPlatoons] = useState([]);
  const [touched, setTouched] = useState({}); // { [platoonId]: { field: true } }

  useEffect(() => {
    API.get(`/core/platoons`)
      .then(data => {
        setPlatoons(data);
      });
  }, []);

  const setValue = (id, key, value) => {
    const updatedPlatoons = platoons.map(platoon => {
      if (platoon.class_id !== id) return platoon;
      else {
        const pl = {};
        if (key.includes('updated')) pl['updated'] = value;
        else pl[key] = value;
        return { ...platoon, ...pl };
      }
    });
    setPlatoons(updatedPlatoons);
  };

  const handleBlur = (id, key) => () => {
    setTouched(prev => ({
      ...prev,
      [id]: { ...(prev[id] || {}), [key]: true }
    }));
  };

  const save = (elem) => {
    elem.preventDefault();
    const promises = platoons.map(platoon =>
      API.patch('/core/platoons?id=' + platoon.class_id, platoon)
    );
    // Wait for all? Original code didn't wait either.
    onSubmit(elem);
  };

  const gradeTitleCss = {
    borderBottom: '1px solid #3e6dc4',
    fontSize: '1.2em',
    paddingLeft: '0.5em',
    marginTop: '0.5em',
    marginBottom: '0.5rem',
    fontFamily: 'Arial,Helvetica,sans-serif'
  };

  // Validators
  const emailRegex = /^(\s?[^\s,]+@[^\s,]+\.[^\s,]+\s?[,;])*(\s?[^\s,]+@[^\s,]+\.[^\s,]+)$/;
  // Phone regex from original commented out pattern, or generic
  // Original used a very complex pattern. Let's use generic or rely on backend? 
  // Code had commented out pattern. I will use a simple one for now or just trust Input type='tel' if no pattern specified?
  // I'll stick to basic length checks or re-use the generic one I used elsewhere if needed.
  // Actually, let's look at the original code's INTENT. It seemed to rely on native validation (pattern).
  // I'll implement JS validation.

  return (
    <TabPane tabId={tabId}>

      <Form
        validateAfterSubmit
        onSubmit={save}
        onValidChange={onValidChange}>
        <p className='title'>
          Platoons
        </p>

        <Callout color="warning">
          Please review the platoons that you have and update the teachers information so that they can stay in the loop throughout the year.
        </Callout>

        {platoons.map(platoon => {
          const isTouched = touched[platoon.class_id] || {};
          const isEmailInvalid = isTouched.email && platoon.email && !emailRegex.test(platoon.email);
          // Phone validation: Basic check for now as original regex was commented out.
          const isPhoneInvalid = isTouched.cell && platoon.cell && platoon.cell.length < 7;

          return (
            <Fragment key={platoon.class_id}>
              <p style={gradeTitleCss}>
                {platoon.class_grade}
                {platoon.class_sub ? '-' + platoon.class_sub : ''}
              </p>
              <Row>
                <Col sm={6}>
                  <label>Teacher Name</label>
                  <Input required name='class_teacher' defaultValue={platoon.teacher} onChange={e => setValue(platoon.class_id, e.target.name, e.target.value)} />
                </Col>

                <Col sm={6}>
                  <label>Teacher Phone</label>
                  <Input required name='cell' type='tel' defaultValue={platoon.cell}
                    onChange={e => setValue(platoon.class_id, 'cell', e.target.value)}
                    title='one or more valid phone numbers'
                    onBlur={handleBlur(platoon.class_id, 'cell')}
                    invalid={isPhoneInvalid}
                  />
                  <FormFeedback>Please enter a valid phone number</FormFeedback>
                  {/* Fixed copy-paste error from 'email addresses' */}
                </Col>

                <Col sm={6}>
                  <label>Teacher E-Mail</label>
                  <Input required name='email' defaultValue={platoon.email}
                    onChange={e => setValue(platoon.class_id, e.target.name, e.target.value)}
                    title='1 or more valid E-mail addresses (, or ; seperated)'
                    onBlur={handleBlur(platoon.class_id, 'email')}
                    invalid={isEmailInvalid}
                  />
                  <FormFeedback>1 or more valid E-mail addresses (, or ; seperated)</FormFeedback>
                </Col>

                <Col sm={6}>
                  <label>Updated for this yr</label><br />
                  <Radio
                    required
                    name={platoon.class_id + '_updated'}
                    value='1'
                    className='form-check-input'
                    checked={parseInt(platoon.updated, 10) === 1}
                    onChange={e => setValue(platoon.class_id, e.target.name, e.target.value)}> This Teacher IS UPDATED for the coming yr </Radio><br />
                  <Radio
                    required
                    name={platoon.class_id + '_updated'}
                    value='0'
                    className='form-check-input'
                    checked={parseInt(platoon.updated, 10) === 0}
                    onChange={e => setValue(platoon.class_id, e.target.name, e.target.value)}> This Teacher IS NOT YET UPDATED for the coming yr </Radio>
                </Col>
              </Row>
            </Fragment>
          );
        })}

        <NavigationRow back={back} next />
      </Form>
    </TabPane>
  )
};

