import React, { useState } from 'react';
// components
import { StorePrize } from 'components/ui';
import { Row, Col, Input, UncontrolledTooltip, Button, FormFeedback } from 'reactstrap';
import { PlatoonSelect, Toggle, Creatable } from 'components/inputs';
// functions
import { isBC } from 'functions/login';

export const PrizeForm = (props) => {
  const { login, onImageEdit, editing, templates, prize, onDelete, onUpdate } = props;
  let {
    platoons = [], prize_name, prize_description, prize_count, points,
    is_active, teacher_edit, school, image
  } = prize;

  const [touched, setTouched] = useState({});
  const onBlur = (field) => () => setTouched(prev => ({ ...prev, [field]: true }));

  // Event Handlers
  const onChange = e => {
    const { name, value } = e.target;
    let updateValue = value;
    if (name === 'num_per_user') {
      updateValue = value === '' ? 0 : (parseInt(value, 10) || 0);
    } else if (name === 'discount_amount') {
      updateValue = value === '' ? 0 : (parseFloat(value) || 0);
    } else if (name === 'discount_type' && value === '') {
      // Clear discount amount when discount type is cleared
      onUpdate({
        [name]: value,
        discount_amount: 0
      });
      return;
    }
    onUpdate({ [name]: updateValue });
  };

  const onTemplateChange = option => {
    let { value, label, prize_id, ...template } = option || {};
    if (!option) return;

    if (template.__isNew__)
      onUpdate({ prize_name: label });
    else
      onUpdate({ ...template });
  };

  const onPlatoonChange = (options) => {
    let updates = { platoons: options.map(option => option.value) };
    if (options.length !== 1)
      updates.teacher_edit = 0;
    onUpdate(updates);
  };

  const onToggleChange = ({ target }) => {
    onUpdate({ [target.name]: target.checked ? 1 : 0 });
  };

  // Logic Variables
  const inputProps = { required: true, onChange };
  const bc = isBC(login.code, true);

  if (!school && login.code === 'BC')
    school = { school_id: login.id }

  // Validation Patterns & Helpers
  const patterns = {
    prize_name: /^.{3,50}$/,
    prize_description: /^.{10,500}$/
  };
  const isValid = (val, pattern) => pattern.test(val || '');

  // Validation Checks
  const isInvalidName = touched.prize_name && (!prize_name || !isValid(prize_name, patterns.prize_name));
  const isInvalidPoints = touched.points && (!points || points < 1 || points > 999999);
  const isInvalidCount = touched.prize_count && (prize_count === '' || prize_count < 0 || prize_count > 99999999999);
  const isInvalidNumPerUser = touched.num_per_user && (prize.num_per_user < 0); // 0 is valid (unlimited)
  const isInvalidDiscount = touched.discount_amount && (prize.discount_type && (!prize.discount_amount || prize.discount_amount <= 0));

  const isInvalidDesc = touched.prize_description && prize_description && !isValid(prize_description, patterns.prize_description);

  return (
    <Row >
      <Col xs={{ size: 12, order: 1 }} sm='8'>
        <Row>

          <Col xs={12}>
            <label>Prize Name</label>
            {(editing || prize_name) &&
              <React.Fragment>
                <Input name='prize_name' value={prize_name || ''} {...inputProps}
                  onBlur={onBlur('prize_name')}
                  maxLength={50}
                  invalid={isInvalidName}
                />
                <FormFeedback>Must be between 3 - 50 characters</FormFeedback>
              </React.Fragment>
            }
            {!editing && !prize_name &&
              <Creatable
                placeholder={prize_name || 'Select Template / Type For New'}
                options={templates}
                openMenuOnFocus={false}
                onChange={onTemplateChange}
                isValidNewOption={val => val.length >= 3 && val.length <= 50} />
            }
            {/* Standard "Must be between 3 - 50 characters" shown by FormFeedback above or via Creatable logic? 
                Creatable handles its own validNewOption. Only shown if Input rendered. */}
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
            <label>In Stock</label>
            <Input type='number' name='prize_count' value={prize_count} {...inputProps} min="0" max="99999999999"
              onBlur={onBlur('prize_count')}
              invalid={isInvalidCount}
            />
            <FormFeedback>Must be between 0 - 100,000,000,000</FormFeedback>
          </Col>

          <Col xs={6} sm={bc ? 4 : 6}>
            <label htmlFor='is_active'>Active</label><br />
            <Toggle
              id='is_active'
              name='is_active'
              className='large'
              checked={!!is_active}
              onChange={onToggleChange} />
          </Col>

          {bc && school &&
            <Col xs={6} sm={4}>
              <label id='teacherEdit'>Teacher Editing</label><br />
              <UncontrolledTooltip placement="top" target="teacherEdit" autohide={false}>
                When limited to a single platoon, toggle this setting to allow teachers of that platoon to edit this prize.
              </UncontrolledTooltip>
              <Toggle
                name='teacher_edit'
                className='large'
                disabled={platoons.length !== 1}
                checked={!!teacher_edit}
                onChange={onToggleChange} />
            </Col>
          }

          <Col xs={6} sm={bc ? 4 : 6}>
            <label htmlFor='num_per_user'>Max Per Soldier</label>
            <small style={{ display: 'block', color: '#6c757d', marginBottom: '5px' }}>
              Leave blank or enter 0 for no limit
            </small>
            <Input
              type='number'
              name='num_per_user'
              id='num_per_user'
              value={prize.num_per_user || ''}
              min='0'
              onChange={onChange}
              onBlur={onBlur('num_per_user')}
              required={false}
              placeholder='0 for No Limit'
              invalid={isInvalidNumPerUser}
            />
            <FormFeedback>Enter 0 for unlimited, or a positive number.</FormFeedback>
          </Col>
        </Row>

        <Row>
          <Col xs={6} sm={6}>
            <label htmlFor='discount_type'>Discount Type</label>
            <Input
              type='select'
              name='discount_type'
              id='discount_type'
              value={prize.discount_type || ''}
              onChange={onChange}
              required={false}
            >
              <option value=''>No Discount</option>
              <option value='points'>Miles Discount</option>
              <option value='percent'>Percentage Off</option>
            </Input>
          </Col>

          <Col xs={6} sm={6}>
            <label htmlFor='discount_amount'>
              {prize.discount_type === 'percent' ? 'Percentage Off' : 'Miles Discount'}
            </label>
            <Input
              type='number'
              name='discount_amount'
              id='discount_amount'
              value={prize.discount_amount || ''}
              min='0'
              step={prize.discount_type === 'percent' ? '0.1' : '1'}
              onChange={onChange}
              onBlur={onBlur('discount_amount')}
              required={false}
              disabled={!prize.discount_type}
              placeholder={prize.discount_type === 'percent' ? 'e.g., 25' : 'e.g., 50'}
              invalid={isInvalidDiscount}
            />
            <FormFeedback>
              {prize.discount_type === 'percent'
                ? 'Enter percentage (e.g., 25 for 25% off)'
                : 'Enter miles to discount'}
            </FormFeedback>
          </Col>
        </Row>
      </Col>
      <Col xs='12' sm={{ size: 4, order: 1 }} className='prize-picture'>
        <label>Prize Image</label>
        <StorePrize src={image} onClick={onImageEdit} />
      </Col>

      {bc &&
        <Col xs={{ size: 12, order: 2 }}>
          <label>Limit To Platoon (leave blank for all)</label>
          <PlatoonSelect
            isMulti
            openMenuOnFocus={false}
            schoolId={school.school_id}
            values={platoons}
            onChange={onPlatoonChange} />
        </Col>
      }

      <Col xs={{ size: 12, order: 2 }}>
        <label>Description / Sponsor</label>
        <Input
          {...inputProps}
          required={false}
          name='prize_description'
          onBlur={onBlur('prize_description')}
          type="textarea" rows='2'
          value={prize_description || ''}
          maxLength={500}
          invalid={isInvalidDesc}
        />
        <FormFeedback>Please enter between 10 and 500 characters</FormFeedback>
      </Col>

      {prize.prize_id &&
        <Col xs={12} className='text-end' style={{ order: 3, marginTop: '10px' }}>
          <Button color='danger' onClick={onDelete}>Delete</Button>
        </Col>
      }
    </Row>
  );
}
