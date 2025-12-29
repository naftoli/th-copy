import { combineReducers } from 'redux';

import staff from './staff/reducer';
import bases from './bases/reducer';
import parents from './parents/reducer';
import soldiers from './soldiers/reducer';
import platoons from './platoons/reducer';
import institutions from './institutions/reducer';
import modules from './modules/reducer';

const reducer = combineReducers({
  platoons, bases,
  soldiers, staff,
  parents,  institutions,
  modules
});

export default reducer;
