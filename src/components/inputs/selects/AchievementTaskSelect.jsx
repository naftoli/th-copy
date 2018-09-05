import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from './Select';
// functions
import { findOption } from 'functions/selects';

class AchievementTaskSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    filter: PropTypes.func,
    value: PropTypes.any,
    showMiles: PropTypes.bool
  }

  static defaultProps = {
    showMiles: false
  }

  getOptions = () => {
    let { tasks, filter } = this.props;
    
    if ( filter )
      tasks = tasks.filter( filter );
    
    return tasks.map( task => ({
      value: task.achievement_task_id, 
      label: this.getLabel( task )
    }));
  }

  getLabel = ({ task, points }) => {
    const { showMiles } = this.props;
    if ( showMiles ) {
      return (
        <span>
          <span className='hebrew'>{ task }</span> ({ points } miles)
        </span>
      );
    }
    return task;
  }

  onChange = ( option ) => {
    return this.props.onChange && this.props.onChange( option );
  }
  
  render() {
    const { value, loading } = this.props;

    let options = this.getOptions();
    let selected = findOption( options, value ) || null;

    return (
      <Select
        {...this.props}
        value={ selected }
        options={ options }
        isLoading={ loading }
        onChange={ this.onChange } />
    );
  }
}

const mapStateToProps = ({ rewards }) => {
  return { ...rewards.achievement_tasks };
}

export default connect( mapStateToProps )( AchievementTaskSelect );
