import { combineReducers } from 'redux';

import parshos from './parshos/reducer';
import mark from './mark/reducer';

const reducer = combineReducers({
  parshos,  mark
});

export default reducer;
