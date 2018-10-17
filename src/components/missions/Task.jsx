import React, { Component } from 'react';
import { Row, Col } from 'reactstrap';
import { LEGACY_URL } from 'components/constants';
import { Number } from 'components/ui';
import { Checkbox } from 'components/inputs';

export class Task extends Component {

  static defaultProps = {
    task: {},
    user_id: 0
  }

  toggleAll = e => {
    let marked = e.target.checked;
    let { date_task_id, date_task_marks } = this.props.task;
    let dates = date_task_marks
      .filter( mark => !!mark.marked !== marked )
      .map( mark => mark.mark_date );
    console.log( marked, date_task_id, dates );
  }

  onChange = ( mark_date ) => e => {
    let marked = e.target.checked;
    let { date_task_id } = this.props.task;
    console.log( this.props.user_id, date_task_id, mark_date, marked );
  }

  render() {
    let { 
      short_name, task_name,  frequency_name, 
      points,     mark_date,  needed, subject_id,
      date_task_marks,        date_task_mark,
    } = this.props.task;

    // remove more then one underscore from task details
    task_name = task_name.replace(/[_]{2,}/g, '').trim();
    // check if this is a daily task or a weekly one
    const daily = frequency_name === "Daily";
    // define some variables
    let earned, marked = false;
    // set the variables based on the task type
    if ( daily ) {
      earned = needed <= date_task_marks.filter( mark => !!mark.marked ).length;
      marked = date_task_marks.filter( mark => !mark.marked ).length === 0;
    } else
      earned = marked = !!date_task_mark.marked;

    return (
      <Row className='Task'>
        <Col md={ daily ? 6 : 10 } xs={ daily ? 12 : 10 }>
          <div className='cell info'>
            <img src={ `${LEGACY_URL}/images/stickers/campaigns/${ subject_id }.gif`} 
              className={ earned ? 'earned' : 'unearned' } alt='sticker' />
            <div>
              <p className='short-name'>{ short_name }</p>
              <p className='task-name'>{ task_name }</p>
              <p className='miles'>
                <Number value={ points } /> miles
              </p>
            </div>
          </div>
        </Col>

        { daily && 
          <Col md={ 6 } className='days'>
            <div className="cell all">
              <Checkbox
                checked={ marked }
                onChange={ this.toggleAll } />
            </div>
            { date_task_marks.map( ({ marked, mark_date }, index) => 
              <div className="cell" key={ index }>
                <Checkbox 
                  checked={ marked }
                  onChange={ this.onChange( mark_date ) } />
              </div>
            )}
          </Col>
        }

        { !daily && 
          <Col xs={ 2 } className='days'>
            <div className="cell">
              <Checkbox 
                checked={ marked } 
                onChange={ this.onChange( mark_date ) } />
            </div>
          </Col>
        }
      </Row>
    );
  }
}
