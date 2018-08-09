import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import Select from './Select';
// functions
import { getPlatoons } from 'store/platoons/operations';
import { setPlatoons } from 'store/platoons/actions';
import { loginChanged } from 'functions/login';
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
  componentDidUpdate( { school_id: prevId, login: prevLogin } ) {
    const { login, school_id } = this.props;
    if ( loginChanged( login, prevLogin ) || prevId !== school_id )
      this.loadPlatoons();
  }
  // load the platoons
  loadPlatoons = () => {
    const { school_id, login } = this.props;
    const haveSchool = school_id || login.code === 'BC';
    if ( haveSchool ) this.props.getPlatoons( school_id );
    else this.props.setPlatoons( [] );
  }

  render() {
    const { platoons, value, onChange, showAllOption, loading } = this.props;
    let platoon_options = platoons.map( 
      ({ class_id, name, school_id }) => ({ value: class_id, label: name, school_id, class_id })
    );
    if ( showAllOption ) platoon_options.unshift({ value: false, label: 'All Platoons' });

    let selected = findOption( platoon_options, value );
    if ( !selected && platoon_options.length > 0 ) onChange( platoon_options[0] );
    platoon_options = loading ? [] : platoon_options;

    return (
      <Select options={platoon_options} value={ selected }
        isLoading={ loading } onChange={ onChange }/>
    )
  }
}

const mapStateToProps = ( { platoons, login } ) => ({
  ...platoons,
  login: login.current_login
})

export default connect( mapStateToProps, { getPlatoons, setPlatoons } )( PlatoonSelect );
