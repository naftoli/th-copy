import { combineReducers } from 'redux';

import staff from './staff/reducer';
import bases from './bases/reducer';
import soldiers from './soldiers/reducer';
import platoons from './platoons/reducer';


const reducer = combineReducers({
  platoons, bases,
  soldiers, staff,
});

export default reducer;
