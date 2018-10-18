import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, TabPane, Input } from 'reactstrap';

class TehillimTab extends Component {

  render(){
    const { tabId } = this.props;
    // render form
    return (
      <TabPane tabId={ tabId }>
        <h1>Tehillim Tab</h1>
      </TabPane>
    );
  }
}

export default TehillimTab;
