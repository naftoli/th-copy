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
    showAllOption: PropTypes.bool
  }

  static defaultProps = {
    showAllOption: false
  }

  componentDidMount(){
    if ( this.props.platoons.length === 0 )
      this.loadPlatoons();
  }

  // update if the login changed or the school_id prop changed
  componentDidUpdate( { school_id: prevId } ) {
    // if the school ID changed, get the new platoons into redux
    const { school_id, onChange, value, isClearable } = this.props;
    if ( prevId !== school_id ) { this.loadPlatoons(); }

    // if we have a value and it is not selected, select it
    const platoon_options = this.getPlatoonOptions();
    const selected = findOption( platoon_options, value );
    if ( !selected && platoon_options.length > 0 && !isClearable ) { 
      onChange( platoon_options[0] ); 
    }
  }
  // load the platoons
  loadPlatoons = () => {
    const { school_id } = this.props;
    if ( school_id ) this.props.getPlatoons( school_id );
    else this.props.setPlatoons( [] );
  }

  getPlatoonOptions = () => {
    const { platoons, showAllOption } = this.props;
    const platoon_options = platoons.map( 
      ({ class_id, name, school_id }) => ({ value: class_id, label: name, school_id, class_id })
    );
    if ( showAllOption ) platoon_options.unshift({ value: false, label: 'All Platoons' });
    return platoon_options;
  }

  render() {
    const { value, onChange, loading } = this.props;

    let platoon_options = this.getPlatoonOptions();
    const selected = findOption( platoon_options, value );
    platoon_options = loading ? [] : platoon_options;

    return (
      <Select {...this.props} options={ platoon_options } value={ selected }
        isLoading={ loading } onChange={ onChange }/>
    )
  }
}

const mapStateToProps = ( { platoons } ) => ({
  ...platoons
})

export default connect( mapStateToProps, { getPlatoons, setPlatoons } )( PlatoonSelect );
