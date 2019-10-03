import Payment from 'payment';

export const validateCC = ({ payment_profile_id, number, expiry, cvc }) => {
  const type = Payment.fns.cardType( number );

  return new Promise( ( resolve, reject ) => {
    // if payment profile id is preset, return the profile id as valid
    if ( payment_profile_id ) {
      return resolve({ payment_profile_id })
    }
    // if there is no card number or payment profile, then nothing was provided.
    if ( !number && !expiry && !cvc ) {
      return reject( new Error('Please Select a Payment Method') );
    }
    // validate the card number
    if ( !Payment.fns.validateCardNumber( number ) ) {
      return reject( new Error('Invalid Credit Card Number') );
    } else if ( !Payment.fns.validateCardExpiry( expiry ) ) {
      return reject( new Error('Invalid Exparation Date') );
    } else if ( !Payment.fns.validateCardCVC( cvc, type ) ) {
      return reject( new Error('Invalid CVC (Code)') );
    }
    // resolve with the data that the API wants
    resolve({
      'cc-number': number.replace(/ /g, ''),
      'cc-exp': expiry.replace(/ |\//g, ''),
      'x_card_code': cvc
    });
  });
}