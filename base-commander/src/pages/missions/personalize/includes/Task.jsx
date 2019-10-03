import React, { Component } from 'react';
import classnames from 'classnames';

import Label from './Label';
import Mission from './Mission';
import { Collapse } from 'reactstrap';
import { Toggle } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';


class Task extends Component {

  state = { isOpen: false };

  componentDidMount() {
    this.setState({ enrolled: this.props.enrolled });
  }

  toggle = () => {
    if ( !this.props.missions && !this.state.isOpen )
      this.props.getMissions( this.props.subject_id, this.props.task );
    // update the dropdown
    this.setState({ isOpen: !this.state.isOpen })
  };

  personalize = () => this.props.personalize({
    level: 'task',
    task: this.props.task,
    enrolled: !this.props.enrolled,
    subject_id: this.props.subject_id,
  });

  render() {
    let {
      task,   enrolled, disabled, 
      labels, missions, subject_id,
      personalize
    } = this.props;

    const classNames = classnames({
      'Task': true,
      'open': this.state.isOpen
    });

    return (
      <div className={ classNames }>

        <Toggle
          className='small'
          disabled={ disabled }
          onChange={ this.personalize }
          checked={ disabled ? false : enrolled } />
        
        <p className='task' onClick={ this.toggle }>
          <FontAwesome icon='caret-right'/> { task }
        </p>

        { labels.map( ( label, index) =>
          <Label key={ index } { ...label } />
        )}

        <Collapse isOpen={ this.state.isOpen }>

          <hr />

          { !missions && <Spinner size={ 4 } /> }

          <div className='missions'>
            { missions && missions.map( ( mission, index ) => 
              <Mission
                key={ index }
                task={ task }
                { ...mission }
                subject_id={ subject_id }
                personalize={ personalize }
                disabled={ !enrolled || disabled } />
            )}
          </div>
          
        </Collapse>
      </div>
    );
  }
}

export default Task;
