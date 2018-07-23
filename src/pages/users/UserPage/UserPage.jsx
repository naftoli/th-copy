import React, { Component } from 'react';
import { Redirect } from 'react-router-dom'
import { connect } from 'react-redux';

class UserPage extends Component {

  state = {
    soldier: false
  }

  componentDidMount() {
    const { soldiers, match } = this.props;
    const current_soldier = soldiers.find( 
      soldier => soldier.user_id === match.params.id
    );
    this.setState({
      soldier:  current_soldier
    });
  }

  render(){
    const { soldier } = this.state;

    if ( soldier === undefined ) {
      return <Redirect to='/users' />;
    }

    console.log( this.state );
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