import React, { useState, useEffect } from 'react';
// components
import { Date } from 'components/inputs';
import { Grid } from 'components/missions/Grid';
import { Row, Col, TabPane, Button } from 'reactstrap';
import { FontAwesome, InlineSync, Spinner, ButtonBar } from 'components/ui';
// functions
import moment from 'moment';
import { julianToday, julianToMoment, toJulian } from 'functions/dates';

const DailyTab = ({ tabId, soldiers, missions, loading, getGrid, openModal, markTask }) => {
  const [date, setDate] = useState(julianToday());

  useEffect(() => {
    load();
  }, [date]);

  const updateDate = newDate => {
    newDate = newDate ? toJulian(newDate) : newDate;
    setDate(newDate);
  }

  const setToday = () => setDate(julianToday());
  const setYesterday = () => setDate(julianToday() - 1);

  const load = () => {
    getGrid('daily', date);
  }

  const momentDate = julianToMoment(date); // convert to moment

  return (
    <TabPane tabId={tabId} id='DailyTab'>

      <p className='title'>Grid Options</p>

      <Row id='options'>
        <Col sm={6}>
          <Date value={momentDate}
            maxDate={moment()}
            onChange={updateDate}
            minDate={moment().subtract(1, 'years')} />
        </Col>

        <Col sm={6}>
          <ButtonBar>
            <Button color='primary' onClick={setToday}>
              <FontAwesome icon='stopwatch' /> Today
            </Button>
            <Button color='primary' onClick={setYesterday}>
              <FontAwesome icon='clock' /> Yesterday
            </Button>
          </ButtonBar>
        </Col>

        <Col sm={6}>
          <Button color='primary'
            onClick={load}
            disabled={!date || loading}>
            <InlineSync loading={loading} /> Refresh Grid
          </Button>
        </Col>

        <Col sm={6}>
          <Button
            color='primary'
            disabled={loading}
            onClick={openModal('daily')} >
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
          type={'daily'}
          soldiers={soldiers}
          missions={missions}
          markTask={markTask} />
      }

    </TabPane>
  );
}

export default DailyTab;
