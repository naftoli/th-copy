import React, { Component } from 'react';
import { Row, Col } from 'reactstrap';
import { Task } from './Task';

export class Label extends Component {

  static defaultProps = {
    label: '',
    missions: [],
  }

  render() {
    const { label, missions, user_id } = this.props;

    const daily = missions[0].frequency_name === "Daily";

    const tasks = missions.map( ( mission, index) => 
      <Task 
        key={ index } 
        task={ mission } 
        user_id={ user_id } />
    );

    return (
      <Col md={ daily ? 12 : 6 } className='Label'>
        <Row>
          <Col md={ daily ? 6 : 12 }>
            <div className='cell'>{ label }</div>
          </Col>

          { daily && 
            <Col md={ 6 } className='days'>
              <div className="cell all">A</div>
              <div className="cell">F</div>
              <div className="cell">ש</div>
              <div className="cell">S</div>
              <div className="cell">M</div>
              <div className="cell">T</div>
              <div className="cell">W</div>
              <div className="cell">T</div>
            </Col>
          }
          
          { tasks }
        </Row>
      </Col>
    );
  }
}
