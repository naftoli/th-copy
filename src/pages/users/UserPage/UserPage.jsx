import React, { Component } from 'react';

class UserPage extends Component {
  render(){
    return <p>{ this.props.match.params.id }</p>
  }
}

export default UserPage;