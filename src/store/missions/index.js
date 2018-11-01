import { combineReducers } from 'redux';

import parshos from './parshos/reducer';
import mark from './mark/reducer';
import grid from './grid/reducer';
import personalize from './personalize/reducer';
import shabbos_mevorchim from './shabbos_mevorchim/reducer';

const reducer = combineReducers({
  personalize, mark,  parshos,
  shabbos_mevorchim,  grid,
});

export default reducer;
