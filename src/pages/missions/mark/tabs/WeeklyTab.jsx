import React, { Component } from 'react';
// components
import { Grid } from 'components/missions/Grid';
import { Row, Col, TabPane, Button } from 'reactstrap';
import { FontAwesome, InlineSync, Spinner } from 'components/ui';
import ParshaSelect from 'components/inputs/selects/ParshaSelect';
// functions
import { julianToday } from 'functions/dates';

class WeeklyTab extends Component {

  state = { date: false }

  updateDate = option => {
    this.setState({ date: option ? option.value : option }, this.load );
  }

  load = () => {
    if ( this.state.date )
      this.props.getGrid( 'weekly', this.state.date );
  }

  render(){
    const { date } = this.state;
    const today = julianToday();
    const { tabId, soldiers, missions, loading } = this.props;
    // render form
    return (
      <TabPane tabId={ tabId } id='WeeklyTab'>

        <p className='title'>Grid Options</p>

        <Row id='options'>
          <Col md={4}>
            <ParshaSelect
              isDescending
              value={ date }
              endDate={ today + 7 } // show the current week
              onChange={ this.updateDate } />
          </Col>
          <Col sm={6} md={4}>
              <Button color='primary'
                  onClick={ this.load } 
                  disabled={ !date || loading }>
                <InlineSync loading={ loading } /> Refresh Grid
              </Button>
          </Col>
          <Col sm={6} md={4}>
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
            type='weekly'
            soldiers={ soldiers } 
            missions={ missions } 
            markTask={ this.props.markTask } />
        }
      </TabPane>
    );
  }
}

export default WeeklyTab;
