import { combineReducers } from 'redux';

import soldiers from './soldiers/reducer';
import platoons from './platoons/reducer';

const reducer = combineReducers({
  platoons, soldiers,
});

export default reducer;
