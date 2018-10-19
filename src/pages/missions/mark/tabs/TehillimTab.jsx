import React, { Component } from 'react';
// components
import { TabPane } from 'reactstrap';

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
