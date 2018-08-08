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

  state = { loading: false }

  componentDidMount(){
    if ( this.props.platoons.length === 0 )
      this.loadPlatoons();
  }

  // update if the login changed or the school_id prop changed
  componentDidUpdate( prevProps ) {
    if (
      loginChanged( this.props.login, prevProps.login ) ||
      prevProps.school_id !== this.props.school_id
    ) {
      this.loadPlatoons();
    }
  }
  // load the platoons
  loadPlatoons = () => {
    const { school_id, login } = this.props;
    const haveSchool = school_id || login.code === 'BC';
    if ( haveSchool ) {
      this.setState({ loading: true });
      this.props.getPlatoons( school_id ).then( () => {
        this.setState({ loading: false });
      })
    } else {
      this.props.setPlatoons([]);
    }
  }

  render() {
    const { platoons, value, onChange, showAllOption } = this.props;
    let platoon_options = platoons.map( 
      ({ class_id, name, school_id }) => ({ value: class_id, label: name, school_id, class_id })
    );
    if ( showAllOption ) platoon_options.unshift({ value: false, label: 'All Platoons' });

    let selected = findOption( platoon_options, value );
    if ( !selected && platoon_options.length > 0 ) selected = platoon_options[0];
    platoon_options = this.state.loading ? [] : platoon_options;

    return (
      <Select options={platoon_options} value={ selected }
        isLoading={ this.state.loading } onChange={ onChange }/>
    )
  }
}

const mapStateToProps = ( { platoons, login } ) => ({
  platoons: platoons.platoons, 
  login: login.current_login
})

export default connect( mapStateToProps, { getPlatoons, setPlatoons } )( PlatoonSelect );