import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import Select from './Select';
// functions
import { getPlatoons } from 'store/platoons/operations';
import { setPlatoons } from 'store/platoons/actions';
import { findOption } from 'functions/selects';

export class PlatoonSelect extends Component {

  static propTypes = {
    showAllOption: PropTypes.bool,
    fetchAll: PropTypes.bool,
    onChange: PropTypes.func,
    value: PropTypes.any,
    school_id: PropTypes.any.isRequired
  }

  static defaultProps = {
    showAllOption: false,
    fetchAll: false
  }

  componentDidMount(){
    if ( this.props.platoons.length === 0 )
      this.loadPlatoons();
  }

  // update if the login changed or the school_id prop changed
  componentDidUpdate( { school_id: prevId } ) {
    // if the school ID changed, get the new platoons into redux
    const { school_id, onChange, value, isClearable, fetchAll } = this.props;
    if ( prevId !== school_id && !fetchAll ) { this.loadPlatoons(); }

    // if we have a value and it is not selected, select it
    const options = this.getOptions();
    const selected = findOption( options, value );
    if ( !selected && options.length > 0 && value ) {
      onChange( isClearable ? false : options[0] ); 
    }
  }
  // load the platoons
  loadPlatoons = () => {
    const { school_id, fetchAll, loading } = this.props;
    
    if ( fetchAll ) { 
      return this.props.getPlatoons( false, true ); // fetch all platoons
    } else if ( school_id ) {
      return this.props.getPlatoons( school_id );
    } else {
      return this.props.setPlatoons( [] );
    }
  }

  getOptions = () => {
    const { platoons, showAllOption, school_id } = this.props;
    
    const options = platoons
    // only platoons in the school_id from props
    .filter( platoon => platoon.school_id === school_id )
    // map them to what react-select expects
    .map( ({ class_id, name, school_id }) => ({ value: class_id, label: name }) );
    // add an all platoons option
    if ( showAllOption ) options.unshift({ value: false, label: 'All Platoons' });
    // and return the options
    return options;
  }

  render() {
    const { value, onChange, loading } = this.props;

    let options = this.getOptions();
    const selected = findOption( options, value ) || null;
    options = loading ? [] : options;

    return (
      <Select {...this.props} options={ options } value={ selected }
        isLoading={ loading } onChange={ onChange }/>
    )
  }
}

const mapStateToProps = ( { platoons } ) => ({
  ...platoons
})

export default connect( mapStateToProps, { getPlatoons, setPlatoons } )( PlatoonSelect );
