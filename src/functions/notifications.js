import { toast } from 'react-toastify';

export const createNotifcation = ( message ) => {
  return toast.info( message, { autoClose: false } );
}

export const updateNotifcation = ( toast_id, message, error = '', success = true ) => {
  const { SUCCESS, ERROR } = toast.TYPE;
  toast.update( toast_id, { 
    type: success ? SUCCESS : ERROR, 
    render: success ? message : error,
    autoClose: success ? null : false
  }); 
  return success;
}

export const showError = ( promise ) => {
  return promise.catch( e => toast.error( e.message ) );
}