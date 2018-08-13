import React from 'react';

export const InlineSync = ({ loading }) => {
  return <i className={`fas fa-sync-alt ${ loading ? 'fa-spin' : '' }`}></i>;
}