import React, { Component } from 'react';
import Select from 'react-select';
import { getPlatoons } from 'store/platoons/operations';
import { connect } from 'react-redux';
import { loginChanged } from 'functions/login';
import { findOption } from 'functions/selects';

export class PlatoonSelect extends Component {

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
    const { school_id, login, value } = this.props;
    const haveSchool = school_id || login.code === 'BC';
    if ( haveSchool ) {
      this.setState({ loading: true });
      this.props.getPlatoons( school_id ).then( () => {
        this.setState({ loading: false });
      })
    }
  }

  render() {
    const { platoons, onChange, value } = this.props;
    let platoon_options = platoons.map( 
      ({ class_id, name, school_id }) => ({ value: class_id, label: name, school_id })
    );

    let selected = findOption( platoon_options, value );
    platoon_options = this.state.loading ? [] : platoon_options;

    return (
      <Select options={platoon_options} value={ selected } openMenuOnFocus
        isLoading={ this.state.loading } onChange={ onChange }/>
    )
  }
}

const mapStateToProps = ( { platoons, login } ) => ({
  platoons: platoons.platoons, 
  login: login.current_login
})

export default connect( mapStateToProps, { getPlatoons } )( PlatoonSelect );