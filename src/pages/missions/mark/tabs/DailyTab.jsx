import React, { Component } from 'react';
// components
import { Date } from 'components/inputs';
import { Grid } from 'components/missions/Grid';
import { Row, Col, TabPane, Button } from 'reactstrap';
import { FontAwesome, InlineSync, Spinner } from 'components/ui';
// functions
import moment from 'moment';
import { julianToday, julianToMoment, toJulian } from 'functions/dates';

class DailyTab extends Component {

  state = {
    date: julianToday() // default the date to today
  }

  componentDidMount = () => {
    this.load();
  }

  updateDate = date => {
    date = date ? toJulian( date ) : date;
    this.setState({ date });
  }

  setToday = () => this.setState({ date: julianToday() }, this.load );
  setYesterday = () => this.setState({ date: julianToday() - 1 }, this.load );

  load = () => {
    this.props.getGrid( 'daily', this.state.date );
  }

  render(){
    const { tabId, soldiers, missions, loading } = this.props;
    const date = julianToMoment( this.state.date );
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
                <FontAwesome icon='stopwatch' /> Today
              </Button>
          </Col>
          <Col xs={6} sm={3}>
              <Button color='primary' onClick={ this.setYesterday }>
                <FontAwesome icon='clock' /> Yesterday
              </Button>
          </Col>
          <Col sm={6}>
              <Button color='primary' onClick={ this.load }>
                <InlineSync /> Refresh Grid
              </Button>
          </Col>
          <Col sm={6}>
              <Button color='primary'>
                <FontAwesome icon='wrench' /> Customize Grid
              </Button>
          </Col>
        </Row>

        <hr/>

        { loading && <Spinner /> }
        { !loading && // not loading
          missions.length > 0 && // and we have missions
          soldiers.length > 0 && // and we have soldiers
          <Grid 
            type={ 'daily' }
            soldiers={ soldiers } 
            missions={ missions } 
            markTask={ this.props.markTask } />
        }
      </TabPane>
    );
  }
}

export default DailyTab;
