import React, { Component } from 'react';

import RankBoard from '../../components/RankBoard';
import { NumberDisplay } from 'components/ui';
import { Row, Col, TabPane } from 'reactstrap';

class RankTab extends Component {
  render() {
    let { board, miles, rank } = this.props;

    return (
      <TabPane id='RankTab' tabId= { this.props.tabId }>

        <Row>
          <Col sm={6}>
            <label>Rank:</label>
            <h4>{ rank.name || 'N/A' }</h4>
          </Col>
          <Col sm={6}>
            <label>Miles: </label>
            <h4><NumberDisplay value={ miles }/></h4>
          </Col>
        </Row>

        <RankBoard board={ board } />
      </TabPane>
    )
  }
}

export default RankTab
