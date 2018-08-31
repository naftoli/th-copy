import { combineReducers } from 'redux';

import achievement_tasks from './achievement_tasks/reducer';
import subjects from './subjects/reducer';

const reducer = combineReducers({
  achievement_tasks, subjects
});

export default reducer;
