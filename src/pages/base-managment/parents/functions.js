// formats the children into valid options for the dropdown
export const getChildOptions = ( children ) => {
  return children.map( 
    ({ user_id, user_serial, first, last }) => {
      return { value: user_id, label: `${user_serial}: ${first} ${last}`}
    }
  );
}