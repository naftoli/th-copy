import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, TabPane, Input } from 'reactstrap';

class DailyTab extends Component {

  render(){
    const { tabId } = this.props;
    // render form
    return (
      <TabPane tabId={ tabId }>
        <h1>Daily Tab</h1>
      </TabPane>
    );
  }
}

export default DailyTab;
