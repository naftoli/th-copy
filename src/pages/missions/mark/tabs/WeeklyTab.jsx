import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, TabPane, Input } from 'reactstrap';

class WeeklyTab extends Component {

  render(){
    const { tabId } = this.props;
    // render form
    return (
      <TabPane tabId={ tabId }>
        <h1>Weekly Tab</h1>
      </TabPane>
    );
  }
}

export default WeeklyTab;
