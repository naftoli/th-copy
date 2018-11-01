import React, { Component } from 'react';
import classnames from 'classnames';
import { Checkbox } from 'components/inputs';
import { DateDisplay } from 'components/ui/Formats';

class Mission extends Component {

  personalize = () => this.props.personalize({
    level: 'mission',
    task: this.props.task,
    mission: this.props.name,
    enrolled: !this.props.enrolled,
    subject_id: this.props.subject_id,
  });

  render() {
    const {
      name,   enrolled,   disabled, end,
      start,  start_date, end_date
    } = this.props;

    const classNames = classnames({
      'Mission': true,
    });

    return (
      <div className={ classNames }>
        <Checkbox 
            disabled={ disabled }
            onChange={ this.personalize }
            checked={ disabled ? false : enrolled }>
          { name }
          <small>{ start } - { end }</small>
          <small><DateDisplay value={ start_date }/> - <DateDisplay value={ end_date }/></small>
        </Checkbox>
      </div>
    );
  }
}

export default Mission;
