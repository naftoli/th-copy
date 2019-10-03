import julian from 'julian';
import moment from 'moment';

export const julianToday = () =>
  parseInt( julian( new Date() ), 10 );

export const julianToMoment = date =>
  date > 0 ? moment( julian.toDate( date ) ) : undefined

export const toJulian = date => 
  parseInt( julian( date.toDate() ), 10 );