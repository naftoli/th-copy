import React, { Component } from 'react';
import Select from 'react-select';
import { getBases } from 'store/bases/operations';
import { connect } from 'react-redux';
import { loginChanged } from 'functions/login';
import { findOption } from 'functions/selects';

export class BaseSelect extends Component {

  state = { loading: false }

  componentDidMount(){
    if ( this.props.bases.length === 0 )
      this.loadBases();
  }

  // update if the login changed or the school_id prop changed
  componentDidUpdate( prevProps ) {
    if ( loginChanged( this.props.login, prevProps.login ) )
      this.loadBases();
  }
  // load the platoons
  loadBases = () => {
    this.setState({ loading: true });
    this.props.getBases().then( () => {
      this.setState({ loading: false });
    });
  }
  // render
  render() {
    const { bases, onChange, value } = this.props;
    const base_options = bases.map( 
      ({ school_id, school_name }) => ({ value: school_id, label: school_name })
    );

    let selected = findOption( base_options, value );

    return (
      <Select options={base_options} value={ selected } openMenuOnFocus
        isLoading={ this.state.loading } onChange={ onChange } />
    )
  }
}

const mapStateToProps = ( { bases, login } ) => ({
  bases: bases.bases, 
  login: login.current_login
})

export default connect( mapStateToProps, { getBases } )( BaseSelect );