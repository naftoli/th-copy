import { combineReducers } from 'redux';

import parshos from './parshos/reducer';
import mark from './mark/reducer';
import grid from './grid/reducer';

const reducer = combineReducers({
  parshos,  mark,   grid
});

export default reducer;
