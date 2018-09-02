import { combineReducers } from 'redux';

import achievement_tasks from './achievement_tasks/reducer';
import achievement_cards from './achievement_cards/reducer';
import subjects from './subjects/reducer';

const reducer = combineReducers({
  achievement_tasks, achievement_cards, subjects
});

export default reducer;
