import React, { Component } from 'react';
import classnames from 'classnames';

import Label from './Label';
import Mission from './Mission';
import { Collapse } from 'reactstrap';
import { Toggle } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';


class Task extends Component {

  state = {
    isOpen: false,
    isUpdating: false,
  };

  componentDidMount() {
    this.setState({ enrolled: this.props.enrolled });
  }

  toggle = () => {
    if ( !this.props.missions && !this.state.isOpen )
      this.props.getMissions( this.props.subject_id, this.props.task );
    // update the dropdown
    this.setState({ isOpen: !this.state.isOpen })
  };

  personalize = () => {
    this.setState({ isUpdating: true })
    this.props.personalize({
      level: 'task',
      task: this.props.task,
      enrolled: !this.props.enrolled,
      subject_id: this.props.subject_id,
    })
      .then(() => {
        this.setState({ isUpdating: false });
      })
      .catch(error => {
        this.setState({ isUpdating: false });
        throw error;
      });
  }

  render() {
    let {
      task, enrolled, disabled,
      labels, missions, subject_id,
      personalize, mandatory
    } = this.props;

    const classNames = classnames({
      'Task': true,
      'open': this.state.isOpen
    });

    const redStyle = { color: "red" }

    return (
      <div className={ classNames }>

        <Toggle
          className='small'
          disabled={ disabled || this.state.isUpdating }
          onChange={ this.personalize }
          checked={ disabled ? false : enrolled } />
          
        { this.state.isUpdating && <span style={{float: "right"}}>Saving...</span> }
        
        <p className='task' onClick={ this.toggle }>
          <FontAwesome icon='caret-right'/> { task }
          { mandatory === 1 && <span style={ redStyle }> * </span> }
        </p>

        { labels.map( ( label, index) =>
          <Label key={ index } { ...label } />
        )}

        <Collapse isOpen={ this.state.isOpen }>

          <hr />

          { !missions && <Spinner size={ 4 } /> }

          <div className='missions'>
            { task !== 'chidon limmud' && missions && missions.map( ( mission, index ) =>
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
