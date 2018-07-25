// detect login changes
export const loginChanged = ( new_login, old_login ) => {
  const { type, id } = new_login;
  const { type: prevType, id: prevId } = old_login;
  // return if true if there are any differences between the objects
  return type !== prevType || prevId !== id;
}