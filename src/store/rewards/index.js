import { combineReducers } from 'redux';

import achievement_tasks from './achievement_tasks/reducer';
import achievement_cards from './achievement_cards/reducer';
import subjects from './subjects/reducer';
import prizes from './prizes/reducer';
import orders from './orders/reducer';

const reducer = combineReducers({
  achievement_tasks,  prizes,
  achievement_cards,  orders, subjects
});

export default reducer;
