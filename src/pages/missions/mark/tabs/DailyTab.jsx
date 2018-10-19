import React, { Component } from 'react';
// components
import { Date } from 'components/inputs';
import { Row, Col, TabPane, Button } from 'reactstrap';
import { FontAwesome, InlineSync } from 'components/ui';

import { Grid } from 'components/missions/Grid';
// functions
import moment from 'moment';
import { julianToday, julianToMoment, toJulian } from 'functions/dates';

class DailyTab extends Component {

  state = { 
    // default the date to today
    date: julianToday()
  }

  updateDate = date => {
    date = date ? toJulian( date ) : date;
    this.setState({ date });
  }

  setToday = () => this.setState({ date: julianToday() });
  setYesterday = () => this.setState({ date: julianToday() - 1 });

  render(){
    const { tabId } = this.props;
    const date = julianToMoment( this.state.date );
    console.log( date, this.state.date );
    // render form
    return (
      <TabPane tabId={ tabId } id='DailyTab'>

        <p className='title'>Grid Options</p>

        <Row id='options'>
          <Col sm={6}>
            <Date value={ date } 
              maxDate={ moment() } 
              onChange={ this.updateDate }
              minDate={ moment().subtract( 1, 'years' ) } />
          </Col>
          <Col xs={6} sm={3}>
              <Button color='primary' onClick={ this.setToday }>
                <FontAwesome icon='arrow-circle-down' /> Today
              </Button>
          </Col>
          <Col xs={6} sm={3}>
              <Button color='primary' onClick={ this.setYesterday }>
                <FontAwesome icon='arrow-circle-left' /> Yesterday
              </Button>
          </Col>
          <Col xs={6}>
              <Button color='primary'>
                <InlineSync /> Refresh Grid
              </Button>
          </Col>
          <Col xs={6}>
              <Button color='primary'>
                <FontAwesome icon='wrench' /> Customize Grid
              </Button>
          </Col>
        </Row>

        <hr/>

        <Grid 
          soldiers={ [ { name: 'Chaim Berliner', user_id: 500 }, { name: 'Moshe Chaimson', user_id: 502 } ] } 
          missions={ [ 
            { cat: 'צדקה - שחרית', grid_id: 101 }, { cat: 'Quota - שחרית', grid_id: 102 }, 
            { cat: 'פירוש המילות', grid_id: 103 }, { cat: 'Rebbe\'s Kapital', grid_id: 103 },
            { cat: 'ספר המצות Daily', grid_id: 104 }, { cat: 'Tanya - New Lines', grid_id: 105 }, 
            { cat: 'Missions - Homework', grid_id: 106 }, { cat: 'Uniform - Tzizis', grid_id: 107 } 
          ]} />
      </TabPane>
    );
  }
}

export default DailyTab;
