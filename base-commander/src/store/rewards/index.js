import { combineReducers } from 'redux';

import achievement_tasks from './achievement_tasks/reducer';
import miles from './miles/reducer';
import subjects from './subjects/reducer';
import prizes from './prizes/reducer';
import orders from './orders/reducer';

const reducer = combineReducers({
  achievement_tasks,  prizes,
  miles,  orders, subjects
});

export default reducer;
