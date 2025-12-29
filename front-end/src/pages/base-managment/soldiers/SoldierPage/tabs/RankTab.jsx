import React from 'react';

import RankBoard from '../../components/RankBoard';
import { NumberDisplay } from 'components/ui';
import { Row, Col, TabPane } from 'reactstrap';

const RankTab = ({ board, miles, rank, tabId }) => {
  return (
    <TabPane id='RankTab' tabId={tabId}>

      <Row>
        <Col sm={6}>
          <label>Rank:</label>
          <h4>{rank.name || 'N/A'}</h4>
        </Col>
        <Col sm={6}>
          <label>Miles: </label>
          <h4><NumberDisplay value={miles} /></h4>
        </Col>
      </Row>

      <RankBoard board={board} />
    </TabPane>
  )
}

export default RankTab
