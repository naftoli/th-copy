import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getSubjects } from 'store/rewards/subjects/operations';

class RewardSubjectSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    filter: PropTypes.func,
    value: PropTypes.any,
    showTasks: PropTypes.bool
  }

  static defaultProps = {
    showTasks: false
  }

  componentDidMount(){ 
    showError( this.props.getSubjects() )
  }

  getOptions = () => {
    let { subjects, filter } = this.props;
    
    if ( filter )
      subjects = subjects.filter( filter );
    
    return subjects.map( subject => ({
      value: subject.subject_id, 
      label: this.getLabel( subject )
    }));
  }

  getLabel = ({ subject_name, achievement_tasks }) => {
    const { showTasks } = this.props;
    if ( showTasks ) {
      return (
        <span>
          <span className='hebrew'>{subject_name}</span> ({ achievement_tasks.length } tasks)
        </span>
      );
    }
    return subject_name;
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
  return { ...rewards.subjects };
}

export default connect( mapStateToProps, { getSubjects } )( RewardSubjectSelect );
