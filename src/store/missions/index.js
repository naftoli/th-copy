import { combineReducers } from 'redux';

import parshos from './parshos/reducer';

const reducer = combineReducers({
  parshos,
});

export default reducer;
