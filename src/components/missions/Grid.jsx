import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { Table } from 'reactstrap';
import { Checkbox } from 'components/inputs';

import './styles/Grid.scss';

export class Grid extends Component {

  static propTypes = {
    date: PropTypes.number,
    soldiers: PropTypes.array,
    missions: PropTypes.array,
  }

  render(){
    const { soldiers, missions } = this.props;

    return (
      <Table className='Grid' bordered hover responsive size="sm">
        <thead>
          <tr className='Grid-row header'>
            <th>Soldier</th>
            { missions.map( ( mission, index ) =>
              <th key={ index }>{ mission.cat }</th>
            )}
          </tr>
        </thead>
        
        <tbody>
          <tr className='Grid-row'>
              <td>
                <strong>Check All</strong>
              </td>
              { missions.map( ( mission, index ) =>
                <td key={ index } >
                  <Checkbox />
                </td>
              )}
            </tr>

          { soldiers.map( ( soldier, index ) =>
            <tr className='Grid-row' key={ index }>
              <td>{ soldier.name }</td>
              { missions.map( ( mission, index ) =>
                <td key={ index } >
                  <Checkbox />
                </td>
              )}
            </tr>
          )}
        </tbody>
      </Table>
    )
  }
}
