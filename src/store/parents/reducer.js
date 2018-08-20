import * as types from './types';

export const initialState = { 
  parents: [], 
  children: [],
  loading: false 
};

export default ( state = initialState, action ) => {
  // varialbes used in reducer
  let child, parents, children;

  switch ( action.type ) {

    case types.SET_LOADING:
      return {
        ...state,
        loading: action.payload
      };
    
    case types.SET_PARENTS:
      return { 
        ...state,
        parents: action.payload,
        loading: false,
      };

    case types.SET_CHILDREN:
      return { 
        ...state,
        children: action.payload,
        loading: false,
      };

    case types.REMOVE_CHILD:
      // get the kid and remove him from the parent non-destructively
      parents = state.parents.map( parent => {
        if ( parent.admin_id === action.payload.admin_id ) {
          // child defined on line 11
          child = parent.children.find( child => child.user_id === action.payload.user_id );
          return { ...parent, children: parent.children.filter( 
            child => child.user_id !== action.payload.user_id
          )};
        }
        return parent; // not them
      });
      // add the child to the list of kids without parents
      children = child ? state.children.concat( child ) : state.children;

      return { ...state, parents, children };

    case types.ADD_CHILD:
      // get the child
      child = state.children.find( 
        child => child.user_id === action.payload.user_id 
      );
      // remove it from children
      children = state.children.filter( 
        child => child.user_id !== action.payload.user_id
      );
      // add it to the parent
      parents = state.parents.map( 
        parent => {
          if ( parent.admin_id === action.payload.admin_id ) 
            return { ...parent, children: [ child, ...parent.children ] };
          return parent;
        }
      )
      return { ...state, parents, children };
    
    default:
      return state;
  }
}