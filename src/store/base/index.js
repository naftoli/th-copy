import { combineReducers } from 'redux';

import soldiers from './soldiers/reducer';
import platoons from './platoons/reducer';
import staff from './staff/reducer';

const reducer = combineReducers({
  platoons, soldiers, staff
});

export default reducer;
