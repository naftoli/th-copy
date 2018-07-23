import React, { Component } from 'react';
import { Redirect } from 'react-router-dom'
import { connect } from 'react-redux';
import { setTitle } from 'functions/utils';
import './UserPage.scss';

class UserPage extends Component {

  state = {
    soldier: false
  }

  componentDidMount() {
    const { soldiers, match } = this.props;
    const current_soldier = soldiers.find( 
      soldier => soldier.user_id === match.params.id
    );
    // update the page title
    if ( current_soldier ) {
      setTitle(`View / Edit ${current_soldier.user_serial}`)
    }
    // update the state
    this.setState({
      soldier:  current_soldier
    });
  }

  render(){
    const { soldier } = this.state;
    // if we do not have the soldier...
    if ( soldier === undefined ) {
      return <Redirect to='/users' />;
    }
    // render the page and it's sub-pages ( tabs )
    return (
      <pre>{ JSON.stringify( this.state.soldier, null, 2 ) }</pre>
    )
  }
}

const mapStateToProps = ( state ) => {
  return {
    ...state.soldiers,
    current_login: state.login.current_login
  };
}

export default connect( mapStateToProps )( UserPage );