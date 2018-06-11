import React from 'react';
import './styles/Spinner.scss';

const Spinner = ( props = { size: 10 } ) => (
  <div className='spinner-1' style={{ fontSize: `${props.size}px` }}></div>
);

export default Spinner;