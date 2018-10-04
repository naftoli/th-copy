import { combineReducers } from 'redux';

import platoons from './platoons/reducer';

const reducer = combineReducers({
  platoons,
});

export default reducer;
