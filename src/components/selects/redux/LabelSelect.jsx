import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getLabels } from 'store/missions/subjects/operations';

class LabelSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    filter: PropTypes.func,
    value: PropTypes.any,
  }

  componentDidMount(){ 
    showError( this.props.getLabels() )
  }

  getOptions = () => {
    let { labels, filter } = this.props;
    
    if ( filter )
      labels = labels.filter( filter );
    
    return labels.map( label => ({
      value: label.label_id, 
      label: `${ label.label_name } - ${ label.frequency.frequency_name }`
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
  const { labels, loading } = missions.subjects;
  return { 
    labels, 
    loading: !!loading.labels 
  };
}

export default connect( mapStateToProps, { getLabels } )( LabelSelect );
