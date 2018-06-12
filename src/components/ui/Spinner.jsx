import React, { Component } from 'react';
import './styles/Spinner.scss';

class Spinner extends Component{
  static defaultProps = { size: 10 }

  render() {
    return <div className='spinner-1' style={{ fontSize: `${this.props.size}px` }}></div>;
  }
}

export default Spinner;