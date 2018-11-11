import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getSubjects } from 'store/missions/subjects/operations';

class SubjectSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    filter: PropTypes.func,
    value: PropTypes.any,
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
      label: subject.subject_name
    }));
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
        isLoading={ loading } />
    );
  }
}

const mapStateToProps = ({ missions }) => {
  const { subjects, loading } = missions.subjects;
  return { 
    subjects, 
    loading: !!loading.subjects 
  };
}

export default connect( mapStateToProps, { getSubjects } )( SubjectSelect );
