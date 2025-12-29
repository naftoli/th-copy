import React, { useState, useEffect } from 'react';
// components
import { Grid } from 'components/missions/Grid';
import { Row, Col, TabPane, Button } from 'reactstrap';
import { InlineSync, Spinner } from 'components/ui';
import { SMSelect } from 'components/selects';

const TehillimTab = ({ tabId, soldiers, missions, loading, getGrid, markTask }) => {
  const [date, setDate] = useState(false);

  useEffect(() => {
    load();
  }, [date]);

  const updateDate = option => {
    setDate(option ? option.value : option);
  }

  const load = () => {
    if (date)
      getGrid('tehillim', date);
  }

  return (
    <TabPane tabId={tabId} id='TehillimTab'>

      <p className='title'>Grid Options</p>

      <Row id='options'>
        <Col sm={6}>
          <SMSelect
            value={date}
            onChange={updateDate} />
        </Col>
        <Col sm={6}>
          <Button color='primary'
            onClick={load}
            disabled={!date || loading}>
            <InlineSync loading={loading} /> Refresh Grid
          </Button>
        </Col>
      </Row>

      <hr />

      {loading && <Spinner />}

      {!loading && // not loading
        missions.length > 0 && // and we have missions
        soldiers.length > 0 && // and we have soldiers
        <Grid
          type='tehillim'
          soldiers={soldiers}
          missions={missions}
          markTask={markTask} />
      }
    </TabPane>
  );
}

export default TehillimTab;
