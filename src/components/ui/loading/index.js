import React, { Component } from 'react';
import { FontAwesome } from '../';
import './Spinner.scss';

export const InlineSync = ({ loading }) => {
  return <FontAwesome icon='sync-alt' spin={ loading } />;
}

export class Spinner extends Component{
  static defaultProps = { size: 10 }

  render() {
    return <div className='spinner-1' style={{ fontSize: `${this.props.size}px` }}></div>;
  }
}
