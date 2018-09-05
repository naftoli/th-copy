import React, { Component } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
import { PlatoonSelect, Toggle, Creatable } from 'components/inputs';
import { StorePrize } from 'components/ui';
// functions
import { eventToUpdate } from 'functions/events';
import { isBC, isAdmin } from 'functions/login';

export class PrizeForm extends Component {

  static defaultProps = {
    platoons: []
  }

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  onTemplateChange = option => {
    let { value, label, ...template } = option;

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
    let { 
      platoons, prize_name, prize_description, prize_count, points,
      one_per_user, is_active, teacher_edit, school, image, login,
      onImageEdit, editing
    } = this.props;
    // props for all inputs
    const inputProps = { required: true, onChange: this.onChange };
    const bc = isBC( login.code, true );

    if ( !school && login.code === 'BC' )
      school = { school_id: login.id }
    
    const templates = [
      { 
        value: 'Say Posuk at Line Up', label: 'Say Posuk at Line Up', prize_name: 'Say Posuk at Line Up',
        points: 8000, is_active: 1, one_per_user: 2, image_id: '163933.20180904172354.png',
        image: '/v2/images/imgsrepo/163933.20180904172354.png'
      }
    ]

    return (
      <Row >
          <Col xs={{ size: 12, order: 1 }} sm='8'>
          <Row>

            <Col xs={ 12 }>
              <label>Prize Name</label>
              { editing && 
                <Input name='prize_name' value={ prize_name || '' } { ...inputProps } 
                  pattern='^.{3,80}$' title="3 to 80 letters" maxLength={ 80 } />
              }
              { !editing && !isAdmin( login.code ) && 
                <Creatable onChange={ this.onTemplateChange } options={ templates } />
              }
              <div className='invalid-message'>Please enter 3 to 80 characters</div>
            </Col>

            <Col xs={ 12 } sm={ 6 }>
              <label>Price (in miles)</label>
              <Input type='number' name='points' value={ points || '' } { ...inputProps } min="1" max="999999" />
              <div className='invalid-message'>Prizes must cost between 1 and 1,000,000 miles</div>
            </Col>

            <Col xs={ 12 } sm={ 6 }>
              <label>In Stock</label>
              <Input type='number' name='prize_count' value={ prize_count || '' } { ...inputProps } min="0" max="99999999999" />
              <div className='invalid-message'>Stock must between 0 and 100,000,000,000.</div>
            </Col>

            <Col xs={ 6 } sm={ bc ? 4 : 6 }>
              <label>Active</label><br/>
              <Toggle 
                name='is_active'
                className='large'
                checked={ !!is_active }
                onChange={ this.onToggleChange } />
            </Col>

            <Col xs={ 6 } sm={ bc ? 4 : 6 }>
              <label>1 per Soldier</label><br/>
              <Toggle 
                className='large' 
                on='yes' off='no' 
                name='one_per_user'
                checked={ !!one_per_user }
                onChange={ this.onToggleChange }/>
            </Col>
            { bc && school && 
              <Col xs={ 6 } sm={ 4 }>
                <label>Teacher Editing</label><br/>
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
          <label>Description</label>
          <Input
            { ...inputProps }
            required={ false }
            name='prize_description'
            type="textarea" rows='2'
            value={ prize_description || '' }
            pattern='^.{20,2000}$' title="20 to 2000 characters" maxLength={ 2000 } />
          <div className='invalid-message'>Please enter between 20 and 2,000 characters</div>
        </Col>
      </Row>
    );
  }
}
