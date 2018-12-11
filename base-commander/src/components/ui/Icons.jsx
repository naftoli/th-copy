import React, { Component } from 'react';
import classnames from 'classnames';

export class FontAwesome extends Component {

  static defaultProps = {
    icon: 'question',
    spin: false,
    solid: false,
    regular: false,
    light: false,
    brand: false,
  }

  render() {
    let { solid, regular, light, brand, spin, icon } = this.props;

    if ( !( regular || light || brand ) ) solid = true;

    const className = classnames({
      'fas': solid,
      'far': regular,
      'fal': light,
      'fab': brand,
      'fa-spin': spin
    }, `fa-${icon}`);
  
    return <i className={className}></i>;
  }
}
