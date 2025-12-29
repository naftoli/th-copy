import React from 'react';
import { FontAwesome } from '../';
import logo from 'img/logos/th.svg';
import './Spinner.scss';
import './LoadingScreen.scss';

export const InlineSync = ({ loading, icon }) => {
  return <FontAwesome icon={!loading && icon ? icon : 'sync-alt'} spin={loading} />;
}

export const Spinner = ({ size = 10 }) => {
  return <div className='spinner-1' style={{ fontSize: `${size}px` }}></div>;
}

export const LoadingScreen = ({ size = 10, hideLogo }) => {
  return (
    <div id='LoadingScreen'>
      {!hideLogo && <img src={logo} id='logo' alt='logo' />}
      <Spinner />
    </div>
  )
}
