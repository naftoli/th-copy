import React, { Component } from 'react';
import { connect } from 'react-redux';

class UserPage extends Component {

  render(){
    const { soldiers, match } = this.props;
    const user = soldiers.find( soldier => soldier.user_id == match.params.id )
    return <pre>{ JSON.stringify( user, null, 2 ) }</pre>
  }
}

const mapStateToProps = ( state ) => {
  return {
    ...state.soldiers,
    current_login: state.login.current_login
  };
}

export default connect( mapStateToProps )( UserPage );