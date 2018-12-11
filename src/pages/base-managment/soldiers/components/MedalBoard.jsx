import React, { Component } from 'react';

import Slider from 'rc-slider';
import { Button } from 'reactstrap';
import { LEGACY_URL } from 'components/constants';
import { FontAwesome } from 'components/ui';

export class MedalBoard extends Component {

  static defaultProps = {
    updates: {},
    onMissionChange: () => {}
  }

  render() {
    const { board, onMissionChange, updates } = this.props;

    return (
      <div className='MedalBoard'>
        { Array.isArray( board ) &&
          board.map( ( medal, index ) => 
            <Medal 
              { ...medal }
              key={ index }
              onChange={ onMissionChange }
              value={ updates[ medal.subject_id ] } /> 
          )
        }
      </div>
    );
  }
}

class Medal extends Component {
  // pass all changes up
  onChange = value => {
    const { medals, earned, subject_id } = this.props;
    const max = medals[ medals.length - 1 ].missions;
    // reset unchanged inputs
    if ( value === earned )
      return this.reset();
    // prevent from going over the max
    if ( value > max )
      value = max;
    // and update accordingly
    return this.props.onChange( subject_id, value );
  }

  // handle slider changes
  onSliderChange = value =>
    this.onChange( value.toString() );
  // handle the input event
  onInputChange = ({ target }) =>
    this.onChange( target.value );

  // get all earned medals and pop off the last one
  getMedal = value =>
    this.props.medals.filter( medal => medal.missions <= value ).pop();

  // reset the updates
  reset = () =>
    this.props.onChange( this.props.subject_id, undefined );

  // go to the next medal
  next = () => {
    let { value, earned, medals } = this.props;

    earned = parseInt( earned, 10 );
    value = value || earned;
    
    const nextValue = medals.find( medal => medal.missions > value ).missions;
    this.onChange( nextValue.toString() );
  }

  // render the page
  render() {
    let { value, earned, subject_name, medals } = this.props;

    // * clean the set the value if it is undefined
    if ( value === undefined )
      value = earned;

    let intval = parseInt( value || 0, 10 );
    if ( intval < 0 )
      intval = 0;

    // * get the current medal and the max value
    let medal = this.getMedal( intval );
    const max = medals[ medals.length - 1 ].missions;
    // reduce the marks into dots on the slider
    const marks = medals.reduce( ( marks, medal ) => {
      return { ...marks, [medal.missions]: false }
    }, {});

    // make sure medal is never undefined
    if ( !medal )
      medal = medals[0];
    
    return (
      <div className='Medal'>
        <div className='actions'>

          <Button outline
              color='primary'
              onClick={ this.reset }
              disabled={ value === earned }>

            <FontAwesome icon='undo-alt' />
            <span>Reset Changes</span>
          </Button>

          <img 
            alt={ subject_name } 
            className='medal-img'
            src={ `${ LEGACY_URL }${ medal.picture }` } />

          <Button outline
              color='primary' 
              onClick={ this.next }
              disabled={ intval >= max }>

            <FontAwesome icon='arrow-right' />
            <span>Next Medal</span>
          </Button>

        </div>

        <strong>{ subject_name }</strong>

        <div className='rc-slider-box'>
          <Slider
            min={ 0 } 
            max={ max }
            value={ intval }
            marks={ marks }
            onChange={ this.onSliderChange } />
        </div>
        
        <span>
          <input
            min={ 0 }
            max={ max }
            type='number'
            value={ value }
            placeholder={ intval }
            onChange={ this.onInputChange } />
          / { max } Missions - { medal.color } Medal
        </span>
      </div>
    )
  }
}
