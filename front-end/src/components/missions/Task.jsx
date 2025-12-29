import React from 'react';

import { Row, Col, Input } from 'reactstrap';
import { Checkbox } from 'components/inputs';
import { NumberDisplay, FontAwesome } from 'components/ui';

import { toast } from 'react-toastify';
import { LEGACY_URL } from 'components/constants';

export const Task = ({
  task = {}, user_id = 0, markMission, daily
}) => {

  const doMarkMission = (dates, mark) => {
    markMission(
      [user_id], task.grid_id, dates, mark
    )
      .catch(e => toast.error(e.message));
  }

  const toggleAll = e => {
    let marked = e.target.checked;
    let { date_task_marks } = task;
    let dates = date_task_marks
      .filter(mark => !!mark.marked !== marked)
      .map(mark => mark.mark_date);

    doMarkMission(dates, marked);
  }

  const onChange = mark_date => e => {
    let marked = e.target.checked;
    doMarkMission([mark_date], marked);
  }

  const onInputChange = (mark_date) => e => {
    let mark = e.target.value;
    doMarkMission([mark_date], mark);
  }

  let {
    short_name, task_name, mandatory_qty,
    quantity, points, date_task_mark,
    subject_id, needed, date_task_marks,
    mark_date, start_date
  } = task;

  // remove more then one underscore from task details
  task_name = task_name.replace(/[_]{2,}/g, '').trim();
  // define some variables
  let earned, marked = false, disabled = false;
  // set the variables based on the task type
  if (daily) {
    earned = needed <= date_task_marks.filter(mark => !!mark.marked).length;
    marked = date_task_marks.filter(mark => !mark.marked).length === 0;
  } else if (quantity) {
    earned = date_task_mark.done_qty >= quantity;
  } else {
    earned = marked = !!date_task_mark.marked;
    disabled = !!date_task_mark.disable;
    if (disabled) {
      marked = true;
    }
  }

  let img_url = `${LEGACY_URL}/images/stickers/campaigns/${subject_id}.gif`;
  if (subject_id === 136) {
    img_url = `${LEGACY_URL}/images/stickers/campaigns/pesukim.jpeg`;
  }

  return (
    <Row className='Task'>
      <Col lg={daily ? 6 : 10} md={daily ? 6 : 9} xs={daily ? 12 : 9}>
        <div className='cell info'>
          <img src={img_url}
            className={earned ? 'earned animated tada' : 'unearned'} alt='sticker' />
          <div>
            <p className='short-name'>
              {short_name} {mandatory_qty >= 1 && <FontAwesome icon='star' />}
            </p>
            <p className='task-name'>{task_name}</p>
            <p className='miles'>
              <NumberDisplay value={points} /> mile
              {points !== 1 ? 's' : ''}
              {daily ? '/day' : ''}
            </p>
          </div>
        </div>
      </Col>

      {daily &&
        <Col md={6} className='days'>
          {!quantity && (
            <div className="cell all">
              <Checkbox
                checked={marked}
                onChange={toggleAll} />
            </div>
          )}
          {date_task_marks.map(({ marked, mark_date, done_qty }, index) =>
            <div className="cell" key={index}>
              {quantity && quantity > 0 ? (
                <Input
                  type='text'
                  max={quantity}
                  placeholder={quantity}
                  defaultValue={done_qty || ''}
                  onChange={onInputChange(mark_date)} />
              ) : (
                <Checkbox
                  checked={marked}
                  onChange={onChange(mark_date)} />
              )}
            </div>
          )}
          {date_task_marks.length < 7 &&
            [...Array(7 - date_task_marks.length)].map((number, index) =>
              <div className="cell" key={index}>
                <Checkbox disabled />
              </div>
            )}
        </Col>
      }

      {!daily && !quantity &&
        <Col xs={3} lg={2}>
          <div className="cell">
            <Checkbox
              checked={marked}
              disabled={disabled}
              onChange={onChange(mark_date || start_date)} />
          </div>
        </Col>
      }
      {!daily && quantity &&
        <Col xs={3} lg={2}>
          <div className="cell">
            <Input
              type='number'
              placeholder={quantity}
              value={date_task_mark.done_qty || ''}
              onChange={onInputChange(mark_date || start_date)} />
          </div>
        </Col>
      }
    </Row>
  );
}
