import React, { Component } from 'react';
// components
import { Row, Col, Input, Label } from 'reactstrap';

export class PaymentsTab extends Component {
  render(){
    return (
      <div id='PaymentsTab'>
        <h4>Payment ID: { this.props.profileId }</h4>
      </div>
    )
  }
}
