import React from 'react';
import classnames from 'classnames';

export const FontAwesome = ({
  icon = 'question',
  spin = false,
  solid = false,
  regular = false,
  light = false,
  brand = false,
}) => {

  const isStyleDefined = regular || light || brand;
  const isSolid = !isStyleDefined || solid;

  const className = classnames({
    'fas': isSolid,
    'far': regular,
    'fal': light,
    'fab': brand,
    'fa-spin': spin
  }, `fa-${icon}`);

  return <i className={className}></i>;
}
