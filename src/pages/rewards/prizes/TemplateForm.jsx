import React, { Component } from 'react';
// components
import { StorePrize } from 'components/ui';
import { Row, Col, Input } from 'reactstrap';
import { Toggle } from 'components/inputs';
// functions
import { eventToUpdate } from 'functions/events';

export class TemplateForm extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  // handle toggle's on the page
  onToggleChange = ({ target }) => {
    this.props.onUpdate({ [target.name]: target.checked ? 1 : 0 });
  }

  render () {
    let { onImageEdit, prize } = this.props;
    let { prize_name, prize_description, points, one_per_user, image } = prize;
    // props for all inputs
    const inputProps = { required: true, onChange: this.onChange };
    
    return (
      <Row >
          <Col xs={{ size: 12, order: 1 }} sm='8'>
            <Row>
              <Col xs={ 12 }>
                <label>Prize Name</label>
                <Input name='prize_name' value={ prize_name || '' } { ...inputProps } 
                  pattern='^.{3,50}$' title="3 to 50 letters" maxLength={ 50 } />
                <div className='invalid-message'>Must be between 3 - 50 characters</div>
              </Col>

              <Col xs={ 6 } >
                <label>Price (in miles)</label>
                <Input type='number' name='points' value={ points || '' } { ...inputProps } min="1" max="999999" />
                <div className='invalid-message'>Must be between 1 - 1,000,000 miles</div>
              </Col>

              <Col xs={ 6 } >
                <label htmlFor='one_per_user'>1 per Soldier</label><br/>
                <Toggle 
                  className='large' 
                  on='yes' off='no'
                  id='one_per_user'
                  name='one_per_user'
                  checked={ !!one_per_user }
                  onChange={ this.onToggleChange }/>
              </Col>

              <Col xs={ 12 }>
                <label>Description / Sponsor</label>
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
        </Col>
        <Col xs='12' sm={{ size: 4, order: 1 }} className='prize-picture'>
          <label>Prize Image</label>
          <StorePrize src={ image } onClick={ onImageEdit }/>
        </Col>

        
      </Row>
    );
  }
}
