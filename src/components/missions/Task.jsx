import React, { Component } from 'react';

import { Row, Col, Input } from 'reactstrap';
import { Checkbox } from 'components/inputs';
import { Number, FontAwesome } from 'components/ui';

import { LEGACY_URL } from 'components/constants';

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

    this.props.markMission( date_task_id, dates, marked );
  }

  onChange = ( mark_date ) => e => {
    let marked = e.target.checked;
    let { date_task_id } = this.props.task;

    this.props.markMission( date_task_id, [ mark_date ], marked );
  }

  onInputChange = ( mark_date ) => e => {
    let marked = e.target.value;
    let { date_task_id } = this.props.task;

    this.props.markMission( date_task_id, [ mark_date ], marked );
  }

  render() {
    const { daily } = this.props;

    let { 
      short_name, task_name,  mandatory_qty,
      quantity,   points,     date_task_mark,
      subject_id, needed,     date_task_marks, 
      mark_date,
    } = this.props.task;

    // remove more then one underscore from task details
    task_name = task_name.replace(/[_]{2,}/g, '').trim();
    // define some variables
    let earned, marked = false;
    // set the variables based on the task type
    if ( daily ) {
      earned = needed <= date_task_marks.filter( mark => !!mark.marked ).length;
      marked = date_task_marks.filter( mark => !mark.marked ).length === 0;
    } else if ( quantity ) {
      earned = date_task_mark.done_qty >= quantity;
    } else
      earned = marked = !!date_task_mark.marked;

    return (
      <Row className='Task'>
        <Col lg={ daily ? 6 : 10 } md={ daily ? 6 : 9 } xs={ daily ? 12 : 9 }>
          <div className='cell info'>
            <img src={ `${LEGACY_URL}/images/stickers/campaigns/${ subject_id }.gif`} 
              className={ earned ? 'earned animated tada' : 'unearned' } alt='sticker' />
            <div>
              <p className='short-name'>
                { short_name } { mandatory_qty >= 1 && <FontAwesome icon='star' /> }
              </p>
              <p className='task-name'>{ task_name }</p>
              <p className='miles'>
                <Number value={ points } /> mile{ points !== 1 ? 's' : '' }
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

        { !daily && !quantity &&
          <Col xs={ 3 } lg={ 2 }>
            <div className="cell">
              <Checkbox 
                checked={ marked } 
                onChange={ this.onChange( mark_date ) } />
            </div>
          </Col>
        }
        { !daily && quantity &&
          <Col xs={ 3 } lg={ 2 }>
            <div className="cell">
              <Input 
                type='number'
                placeholder={ quantity }
                value={ date_task_mark.done_qty || '' }
                onChange={ this.onInputChange( mark_date ) } />
            </div>
          </Col>
        }
      </Row>
    );
  }
}
