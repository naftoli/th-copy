import { combineReducers } from 'redux';

import mark from './mark/reducer';
import grid from './grid/reducer';
import tasks from './tasks/reducer';
import parshos from './parshos/reducer';
import subjects from './subjects/reducer';
import personalize from './personalize/reducer';
import shabbos_mevorchim from './shabbos_mevorchim/reducer';

const reducer = combineReducers({
  personalize,        parshos,    mark,
  shabbos_mevorchim,  subjects,   grid,
  tasks,
});

export default reducer;
