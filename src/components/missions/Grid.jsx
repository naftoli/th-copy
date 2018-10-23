import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { Link } from 'react-router-dom';
import { FontAwesome } from 'components/ui';
import { Checkbox } from 'components/inputs';
import { Table, Input, Alert } from 'reactstrap';

import './styles/Grid.scss';

class GridCell extends Component {

  onChange = e => {
    let value = e.target.checked ? '1' : '0';
    let { mission, soldier } = this.props;

    if ( e.target.type === 'number' )
      value = e.target.value;

    this.props.onChange( value, mission, soldier );
  }

  render() {
    let { mission, soldier, value } = this.props;
    // if it is a quantity based mission
    if ( mission.quantity ) {
      // set the value of the input
      if ( !value && soldier && !!soldier.marks[ mission.grid_id ] )
        value = soldier.marks[ mission.grid_id ].done_qty;
      // hide error
      if ( !value && soldier )
        value = value || '';
      // return a input component
      return <Input 
        min='0'
        type='number'
        value={ value }
        onChange={ this.onChange } />;
    }

    if ( !value && soldier )
      value = !!soldier.marks[ mission.grid_id ];
    // return a checkbox for on/off based tasks
    return <Checkbox 
      checked={ value }
      onChange={ this.onChange } />
  }
}

export class Grid extends Component {

  static propTypes = {
    date: PropTypes.number,
    type: PropTypes.string,
    soldiers: PropTypes.array,
    missions: PropTypes.array,
  }

  checkAll = ( value, mission ) => {
    const user_ids = this.props.soldiers
      .filter( soldier => // filter to soldiers that either
        !soldier.marks[ mission.grid_id ] || // 1. do not have a mark for this grid_id
        soldier.marks[ mission.grid_id ].done_qty !== value // 2. do have a mark that does not match the one entered at the top
      ).map( soldier => soldier.user_id );
    // mark the task
    this.props.markTask(
      this.props.type, user_ids, mission.grid_id, 
      mission.mark_date || this.props.date, value
    );
  }

  toggleTask = ( value, mission, soldier ) => {
    const user_ids = [ soldier.user_id ];
    this.props.markTask(
      this.props.type, user_ids,  mission.grid_id, 
      mission.mark_date || this.props.date, value
    );
  }

  render(){
    let { soldiers, missions } = this.props;
    // hide disabled missions from the GUI
    missions = missions.filter( mission => !mission.disabled );

    if ( missions.length === 0 )
      return <Alert color="danger">No Available Tasks</Alert>
    
    return (
      <Table className='Grid' bordered striped hover responsive size="sm">
        <thead>
          <tr className='Grid-row header'>
            <th>
              <span>Missions <FontAwesome icon='arrow-right' /></span>
              <hr/>
              <span>Soldiers <FontAwesome icon='arrow-down' /></span>
            </th>
            { missions.map( ( mission, index ) =>
              <th key={ index }>
                <span>{ mission.cat } </span>
                { mission.mandatory_qty >= 1 && <FontAwesome icon='star' /> }
              </th>
            )}
          </tr>
        </thead>
        
        <tbody>
          <tr className='Grid-row'>
              <td>
                <strong>All Soldiers</strong>
              </td>
              { missions.map( ( mission, index ) =>
                <td key={ index } >
                  <GridCell 
                    mission={ mission }
                    onChange={ this.checkAll } />
                </td>
              )}
            </tr>

          { soldiers.map( ( soldier, index ) =>
            <tr className='Grid-row' key={ index }>
              <td>
                <small>{ soldier.rank.name }</small><br/>
                <Link to={`/bm/users/${soldier.user_id}`}>{ soldier.name }</Link>
              </td>
              { missions.map( ( mission, index ) =>
                <td key={ index } >
                  <GridCell 
                    soldier={ soldier }
                    mission={ mission }
                    onChange={ this.toggleTask } />
                </td>
              )}
            </tr>
          )}
        </tbody>
      </Table>
    )
  }
}
