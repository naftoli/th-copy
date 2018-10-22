import { combineReducers } from 'redux';

import parshos from './parshos/reducer';
import mark from './mark/reducer';
import grid from './grid/reducer';
import shabbos_mevorchim from './shabbos_mevorchim/reducer';

const reducer = combineReducers({
  parshos,  mark,   grid, 
  shabbos_mevorchim
});

export default reducer;
