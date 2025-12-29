import React, { useEffect } from 'react';
import { useDispatch } from 'react-redux';
import { Navigate } from 'react-router-dom';
import { logout } from 'store/login/actions';

const Logout = () => {
  const dispatch = useDispatch();

  // log the user out
  useEffect(() => {
    dispatch(logout());
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  // redirect to the homepage
  return <Navigate to="/" replace />;
}

export default Logout;