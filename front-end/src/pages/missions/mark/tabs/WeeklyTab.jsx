import React, { useState, useEffect } from 'react';
// components
import { Grid } from 'components/missions/Grid';
import { ParshaSelect } from 'components/selects';
import { Row, Col, TabPane, Button } from 'reactstrap';
import { FontAwesome, InlineSync, Spinner } from 'components/ui';
// functions
import { julianToday } from 'functions/dates';

const WeeklyTab = ({ tabId, soldiers, missions, loading, getGrid, markTask, openModal }) => {
  const [date, setDate] = useState(false);

  useEffect(() => {
    load();
  }, [date]);

  const updateDate = option => {
    setDate(option ? option.value : option);
  }

  const load = () => {
    if (date)
      getGrid('weekly', date);
  }

  const today = julianToday();

  return (
    <TabPane tabId={tabId} id='WeeklyTab'>

      <p className='title'>Grid Options</p>

      <Row id='options'>
        <Col md={4}>
          <ParshaSelect
            isDescending
            value={date}
            endDate={today + 7} // show the current week
            onChange={updateDate} />
        </Col>
        <Col sm={6} md={4}>
          <Button color='primary'
            onClick={load}
            disabled={!date || loading}>
            <InlineSync loading={loading} /> Refresh Grid
          </Button>
        </Col>
        <Col sm={6} md={4}>
          <Button
            color='primary'
            disabled={loading}
            onClick={openModal('weekly')} >
            <FontAwesome icon='wrench' /> Customize Grid
          </Button>
        </Col>
      </Row>

      <hr />

      {loading && <Spinner />}

      {!loading && // not loading
        missions.length > 0 && // and we have missions
        soldiers.length > 0 && // and we have soldiers
        <Grid
          type='weekly'
          soldiers={soldiers}
          missions={missions}
          markTask={markTask} />
      }
    </TabPane>
  );
}

export default WeeklyTab;
