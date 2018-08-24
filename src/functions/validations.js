import Payment from 'payment';

export const validateCC = ({ number, expiry, cvc }) => {
  const type = Payment.fns.cardType( number );

  return new Promise( ( resolve, reject ) => {
    if ( !Payment.fns.validateCardNumber( number ) ) {
      return reject( new Error('Invalid Credit Card Number') );
    } else if ( !Payment.fns.validateCardExpiry( expiry ) ) {
      return reject( new Error('Invalid Exparation Date') );
    } else if ( !Payment.fns.validateCardCVC( cvc, type ) ) {
      return reject( new Error('Invalid CVC (Code)') );
    } 

    resolve({
      'cc-number': number.replace(/ /g, ''),
      'cc-exp': expiry.replace(/ |\//g, ''),
      'x_card_code': cvc
    });
  });
}