import React, { Component } from 'react';
// components
import { StorePrize } from 'components/ui';
import { Row, Col, Input, UncontrolledTooltip, Button } from 'reactstrap';
import { PlatoonSelect, Toggle, Creatable } from 'components/inputs';
// functions
import { isBC } from 'functions/login';

export class PrizeForm extends Component {

  onChange = e => {
    const { name, value } = e.target;
    let updateValue = value;
    if (name === 'num_per_user') {
      updateValue = value === '' ? 0 : (parseInt(value, 10) || 0);
    } else if (name === 'discount_amount') {
      updateValue = value === '' ? 0 : (parseFloat(value) || 0);
    } else if (name === 'discount_type' && value === '') {
      // Clear discount amount when discount type is cleared
      this.props.onUpdate({ 
        [name]: value,
        discount_amount: 0
      });
      return;
    }
    this.props.onUpdate({ [name]: updateValue });
  };

  onTemplateChange = option => {
    let { value, label, prize_id, ...template } = option;

    if ( template.__isNew__ )
      this.props.onUpdate({ prize_name: label });
    else
      this.props.onUpdate({ ...template });
  }

  // handle react-select dropdown change events
  onPlatoonChange = ( options ) => {
    let updates = { platoons: options.map( option => option.value ) };
    if ( options.length !== 1 )
      updates.teacher_edit = 0;
    this.props.onUpdate( updates );
  };
  // handle toggle's on the page
  onToggleChange = ({ target }) => {
    this.props.onUpdate({ [target.name]: target.checked ? 1 : 0 });
  }

  render () {
    let { login, onImageEdit, editing, templates, prize, onDelete } = this.props;
    let { 
      platoons = [], prize_name, prize_description, prize_count, points,
      is_active, teacher_edit, school, image
    } = prize;
    // props for all inputs
    const inputProps = { required: true, onChange: this.onChange };
    const bc = isBC( login.code, true );

    if ( !school && login.code === 'BC' )
      school = { school_id: login.id }
    
    return (
      <Row >
          <Col xs={{ size: 12, order: 1 }} sm='8'>
          <Row>

            <Col xs={ 12 }>
              <label>Prize Name</label>
              { ( editing || prize_name ) && 
                <Input name='prize_name' value={ prize_name || '' } { ...inputProps } 
                  pattern='^.{3,50}$' title="3 to 50 letters" maxLength={ 50 } />
              }
              { !editing && !prize_name &&
                <Creatable 
                  placeholder={ prize_name || 'Select Template / Type For New' }
                  options={ templates } 
                  openMenuOnFocus={ false }
                  onChange={ this.onTemplateChange } 
                  isValidNewOption={ val => val.length >= 3 && val.length <= 50 } />
              }
              <div className='invalid-message'>Must be between 3 - 50 characters</div>
            </Col>

            <Col xs={ 6 } >
              <label>Price (in miles)</label>
              <Input type='number' name='points' value={ points || '' } { ...inputProps } min="1" max="999999" />
              <div className='invalid-message'>Must be between 1 - 1,000,000 miles</div>
            </Col>

            <Col xs={ 6 } >
              <label>In Stock</label>
              <Input type='number' name='prize_count' value={ prize_count } { ...inputProps } min="0" max="99999999999" />
              <div className='invalid-message'>Must be between 0 - 100,000,000,000</div>
            </Col>

            <Col xs={ 6 } sm={ bc ? 4 : 6 }>
              <label htmlFor='is_active'>Active</label><br/>
              <Toggle 
                id='is_active'
                name='is_active'
                className='large'
                checked={ !!is_active }
                onChange={ this.onToggleChange } />
            </Col>

            { bc && school && 
              <Col xs={ 6 } sm={ 4 }>
                <label id='teacherEdit'>Teacher Editing</label><br/>
                <UncontrolledTooltip placement="top" target="teacherEdit" autohide={ false }>
                  When limited to a single platoon, toggle this setting to allow teachers of that platoon to edit this prize.
                </UncontrolledTooltip>
                <Toggle 
                  name='teacher_edit'
                  className='large'
                  disabled={ platoons.length !== 1 }
                  checked={ !!teacher_edit }
                  onChange={ this.onToggleChange } />
              </Col>
            }

            <Col xs={ 6 } sm={ bc ? 4 : 6 }>
              <label htmlFor='num_per_user'>Max Per Soldier</label>
              <small style={{ display: 'block', color: '#6c757d', marginBottom: '5px' }}>
                Leave blank or enter 0 for no limit
              </small>
              <Input
                type='number'
                name='num_per_user'
                id='num_per_user'
                value={ prize.num_per_user || '' }
                min='0'
                onChange={ this.onChange }
                required={ false }
                placeholder='0 for No Limit'
              />
              <div className='invalid-message'>Enter 0 for unlimited, or a positive number.</div>
            </Col>
          </Row>

          <Row>
            <Col xs={ 6 } sm={ 6 }>
              <label htmlFor='discount_type'>Discount Type</label>
              <Input
                type='select'
                name='discount_type'
                id='discount_type'
                value={ prize.discount_type || '' }
                onChange={ this.onChange }
                required={ false }
              >
                <option value=''>No Discount</option>
                <option value='points'>Miles Discount</option>
                <option value='percent'>Percentage Off</option>
              </Input>
            </Col>

            <Col xs={ 6 } sm={ 6 }>
              <label htmlFor='discount_amount'>
                {prize.discount_type === 'percent' ? 'Percentage Off' : 'Miles Discount'}
              </label>
              <Input
                type='number'
                name='discount_amount'
                id='discount_amount'
                value={ prize.discount_amount || '' }
                min='0'
                step={prize.discount_type === 'percent' ? '0.1' : '1'}
                onChange={ this.onChange }
                required={ false }
                disabled={!prize.discount_type}
                placeholder={prize.discount_type === 'percent' ? 'e.g., 25' : 'e.g., 50'}
              />
              <div className='invalid-message'>
                {prize.discount_type === 'percent' 
                  ? 'Enter percentage (e.g., 25 for 25% off)' 
                  : 'Enter miles to discount'}
              </div>
            </Col>
          </Row>
        </Col>
        <Col xs='12' sm={{ size: 4, order: 1 }} className='prize-picture'>
          <label>Prize Image</label>
          <StorePrize src={ image } onClick={ onImageEdit }/>
        </Col>

        { bc && 
          <Col xs={{ size: 12, order: 2 }}>
            <label>Limit To Platoon (leave blank for all)</label>
            <PlatoonSelect 
              isMulti
              openMenuOnFocus={ false }
              schoolId={ school.school_id }
              values={ platoons }
              onChange={ this.onPlatoonChange } />
          </Col>
        }

        <Col xs={{ size: 12, order: 2 }}>
          <label>Description / Sponsor</label>
          <Input
            { ...inputProps }
            required={ false }
            name='prize_description'
            type="textarea" rows='2'
            value={ prize_description || '' }
            pattern='^.{10,500}$' title="10 to 500 characters" maxLength={ 500 } />
          <div className='invalid-message'>Please enter between 10 and 500 characters</div>
        </Col>

        { prize.prize_id &&
          <Col xs={ 12 } className='text-right' style={{ order: 3, marginTop: '10px' }}>
            <Button color='danger' onClick={ onDelete }>Delete</Button>
          </Col>
        }
      </Row>
    );
  }
}
