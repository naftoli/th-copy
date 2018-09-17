import React, { Component } from 'react';
// components
import { StorePrize } from 'components/ui';
import { Row, Col, Input, UncontrolledTooltip } from 'reactstrap';
import { PlatoonSelect, Toggle, Creatable } from 'components/inputs';
// functions
import { eventToUpdate } from 'functions/events';
import { isBC } from 'functions/login';

export class PrizeForm extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

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
    let { login, onImageEdit, editing, templates, prize } = this.props;
    let { 
      platoons = [], prize_name, prize_description, prize_count, points,
      one_per_user, is_active, teacher_edit, school, image
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

            <Col xs={ 6 } sm={ bc ? 4 : 6 }>
              <label htmlFor='one_per_user'>1 per Soldier</label><br/>
              <Toggle 
                className='large' 
                on='yes' off='no'
                id='one_per_user'
                name='one_per_user'
                checked={ !!one_per_user }
                onChange={ this.onToggleChange }/>
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
      </Row>
    );
  }
}
