import React, { useState } from 'react';
// components
import { StorePrize } from 'components/ui';
import { Row, Col, Input, FormFeedback } from 'reactstrap';
import { Toggle } from 'components/inputs';

export const TemplateForm = (props) => {
  const { onImageEdit, prize, onUpdate } = props;
  const { prize_name, prize_description, points, one_per_user, image } = prize;

  const [touched, setTouched] = useState({});
  const onBlur = (field) => () => setTouched(prev => ({ ...prev, [field]: true }));

  // Event handlers
  const onChange = ({ target }) => onUpdate({ [target.name]: target.value });
  const onToggleChange = ({ target }) => onUpdate({ [target.name]: target.checked ? 1 : 0 });

  // Validation
  const patterns = {
    prize_name: /^.{3,50}$/,
    prize_description: /^.{10,500}$/
  };
  const isValid = (val, pattern) => pattern.test(val || '');

  // Custom checks
  const isInvalidName = touched.prize_name && (!prize_name || !isValid(prize_name, patterns.prize_name));
  const isInvalidPoints = touched.points && (!points || points < 1 || points > 999999);

  // Optional description: Invalid only if non-empty and fails pattern
  const isInvalidOptionalDesc = touched.prize_description && prize_description && !isValid(prize_description, patterns.prize_description);

  const inputProps = { required: true, onChange };

  return (
    <Row >
      <Col xs={{ size: 12, order: 1 }} sm='8'>
        <Row>
          <Col xs={12}>
            <label>Prize Name</label>
            <Input name='prize_name' value={prize_name || ''} {...inputProps}
              onBlur={onBlur('prize_name')}
              maxLength={50}
              invalid={isInvalidName}
            />
            <FormFeedback>Must be between 3 - 50 characters</FormFeedback>
          </Col>

          <Col xs={6} >
            <label>Price (in miles)</label>
            <Input type='number' name='points' value={points || ''} {...inputProps} min="1" max="999999"
              onBlur={onBlur('points')}
              invalid={isInvalidPoints}
            />
            <FormFeedback>Must be between 1 - 1,000,000 miles</FormFeedback>
          </Col>

          <Col xs={6} >
            <label htmlFor='one_per_user'>1 per Soldier</label><br />
            <Toggle
              className='large'
              on='yes' off='no'
              id='one_per_user'
              name='one_per_user'
              checked={!!one_per_user}
              onChange={onToggleChange} />
          </Col>

          <Col xs={12}>
            <label>Description / Sponsor</label>
            <Input
              {...inputProps}
              required={false}
              name='prize_description'
              onBlur={onBlur('prize_description')}
              type="textarea" rows='2'
              value={prize_description || ''}
              maxLength={500} // Pattern was 500
              invalid={isInvalidOptionalDesc}
            />
            <FormFeedback>Please enter between 10 and 500 characters</FormFeedback>
          </Col>
        </Row>
      </Col>
      <Col xs='12' sm={{ size: 4, order: 1 }} className='prize-picture'>
        <label>Prize Image</label>
        <StorePrize src={image} onClick={onImageEdit} />
      </Col>
    </Row>
  );
}
