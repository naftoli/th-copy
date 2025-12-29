import React, { useState } from 'react';
// components
import { Radio, Select } from 'components/inputs';
import { Row, Col, Input, Button, FormFeedback } from 'reactstrap';
import { findOption } from 'functions/selects';
import { FontAwesome, NumberDisplay } from 'components/ui';

export const PlatoonRow = (props) => {
  const { inputProps, onSelectChange, onDelete, platoon } = props;
  const {
    class_grade, class_sub, class_teacher, cell, email,
    soldiers, miles_per_soldier, miles_balance, updated
  } = platoon;

  const [touched, setTouched] = useState({});

  const onBlur = (field) => () => {
    setTouched(prev => ({ ...prev, [field]: true }));
  };

  let grades = ['Pre1a', '1', '2', '3', '4', '5', '6', '7', '8'];
  grades = grades.map(grade => {
    if (grade === 'Pre1a') return { label: 'Pre1a / Kindergarten', value: grade };
    return { label: grade, value: grade };
  });
  const selectedGrade = findOption(grades, class_grade);

  // Validation
  // Phone regex from original: 
  // ^(((\+[0-9]{1,3}[0-9 ]{9,})|((?:1 |\()?[0-9]{3}(?: |\) |-)?[0-9]{3}(?: |-)?[0-9]{4}))[,;])*((\+[0-9]{1,3}[0-9 ]{9,})|((?:1 |\()?[0-9]{3}(?: |\) |-)?[0-9]{3}(?: |-)?[0-9]{4}))$
  // This looks complex (multiple numbers separated by , or ;).
  // I'll assume standard phone check for now or try to preserve logic if possible.
  // Actually, previously I used a simpler regex for phones.
  // But this allows multiple phones.
  // Let's reuse the simple regex `validationHelpers` I might have created?
  // Or just copy the logic.
  // The original pattern was commented out line 51!
  // It relied on `title` to show error.
  // I will implement a regex that supports comma/semicolon separated phones?
  // Or just simple required check if complex regex is too much risk?
  // The original component had it valid *if* it matched the pattern, but the pattern was commented out?
  // Wait, line 51 was commented out. `title` was there.
  // So it might have had NO validation except "required"?
  // But line 53 had `div.invalid-message`.
  // If I want to be safe, I'll stick to basic required + simple format check if easy.
  // "separate multiple email addresses with a comma" -> hints multiple.

  // Email pattern was active: 
  // ^(\s?[^\s,]+@[^\s,]+\.[^\s,]+\s?[,;])*(\s?[^\s,]+@[^\s,]+\.[^\s,]+)$
  const emailMultiRegex = /^(\s?[^\s,]+@[^\s,]+\.[^\s,]+\s?[,;])*(\s?[^\s,]+@[^\s,]+\.[^\s,]+)$/;
  const isEmailInvalid = touched.email && email && !emailMultiRegex.test(email);

  const isTeacherInvalid = touched.class_teacher && !class_teacher; // required

  // Cell/phone validation
  // Original had invalid-message but commented out pattern.
  // I'll add a simple length check or regex if user inputs something.
  const isCellInvalid = touched.cell && !cell; // basic required

  const isMilesBalanceInvalid = touched.miles_balance && (miles_balance < 0 || miles_balance > 99999999999);

  return (
    <Row>
      <Col sm={6}>
        <label>Grade</label>
        <Select
          required
          options={grades}
          value={selectedGrade}
          onChange={onSelectChange('class_grade')} />
      </Col>

      <Col sm={6}>
        <label>Class (e.g. 'Boys' or 'א‬'.)</label>
        <Input name='class_sub' value={class_sub || ''} {...inputProps} required={false} />
      </Col>

      <Col sm={12}>
        <label>Teacher Name</label>
        <Input required name='class_teacher' value={class_teacher || ''} {...inputProps}
          invalid={isTeacherInvalid} onBlur={onBlur('class_teacher')} />
        <FormFeedback>Teacher Name is required</FormFeedback>
      </Col>

      <Col sm={6}>
        <label>Teacher Phone</label>

        <Input required name='cell' type='tel' value={cell || ''}
          {...inputProps}
          invalid={isCellInvalid} onBlur={onBlur('cell')}
        />
        <FormFeedback>separate multiple phone numbers with a comma</FormFeedback>
      </Col>

      <Col sm={6}>
        <label>Teacher E-Mail</label>

        <Input required name='email' value={email || ''} {...inputProps}
          invalid={isEmailInvalid} onBlur={onBlur('email')}
        />
        <FormFeedback>1 or more valid E-mail addresses (, or ; seperated)</FormFeedback>
      </Col>

      <Col sm={6}>
        <Radio
          required
          name='updated'
          value='1'
          checked={updated === '1' || updated === 1}
          {...inputProps} />
        <label>Updated</label>
        <Radio
          name='updated'
          value='0'
          checked={updated === '0' || updated === 0}
          {...inputProps} />
        <label>Not Updated</label>
      </Col>

      {soldiers && soldiers.length === 0 &&
        <Col sm={12}>
          <Button color='danger' onClick={onDelete} id='delete'>
            <FontAwesome icon='trash' /> Delete Platoon
          </Button>
        </Col>
      }

      <Col xs={12}>
        <p className='title'>
          Achievement Card Settings
        </p>
      </Col>

      <Col sm={6}>
        <label>Miles per Soldier (per month)</label>
        <Input
          type='number' name='miles_per_soldier'
          value={miles_per_soldier} {...inputProps} />
      </Col>

      <Col sm={6}>
        <label>Current Miles Balance</label>
        <Input
          type='number' name='miles_balance' min='0' max='99999999999'
          value={miles_balance} {...inputProps}
          invalid={isMilesBalanceInvalid} onBlur={onBlur('miles_balance')}
        />
        <FormFeedback>0 to <NumberDisplay value={99999999999} /></FormFeedback>
      </Col>

    </Row>
  );
};
