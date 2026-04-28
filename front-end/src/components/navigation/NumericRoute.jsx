import React from 'react';
import { useParams } from 'react-router-dom';

export const NumericRoute = ({ children, fallback = null, param = 'id' }) => {
  const params = useParams();
  const value = params[param];

  if (!/^[0-9]+$/.test(value || '')) {
    return fallback;
  }

  return children;
};

export default NumericRoute;
