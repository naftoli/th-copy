import React, { Component } from 'react';
import { Row, Col } from 'reactstrap';
import { Task } from './Task';

import './styles/Label.scss';

export class Label extends Component {

  static defaultProps = {
    label: '',
    missions: [],
  }

  render() {
    const { label, missions, user_id } = this.props;

    const daily = missions[0].frequency_name === "Daily";

    // const tasks = missions.map( ( mission, index) => 
    //   <Task 
    //     key={ index } 
    //     task={ mission } 
    //     user_id={ user_id } />
    // );

    return (
      <div className='Label'>
        <Row>
          <Col md={ daily ? 6 : 12 }>
            <div className='cell'>{ label }</div>
          </Col>

          { daily && 
            <Col md={ 6 } className='days'>
              <div className="cell all">All</div>
              <div className="cell">F</div>
              <div className="cell">ש</div>
              <div className="cell">S</div>
              <div className="cell">M</div>
              <div className="cell">T</div>
              <div className="cell">W</div>
              <div className="cell">T</div>
            </Col>
          }
        </Row>

        { missions.map( ( mission, index) => 
          <Task 
            daily={ daily }
            task={ mission } 
            user_id={ user_id } 
            // use the updated key if present to force a re-render
            key={ index } 
            markMission={ this.props.markMission } />
        ) }

      </div>
    );
  }
}
