import React, { Fragment } from 'react';
// components
import { TabPane } from 'reactstrap';
import { Callout } from 'components/ui';
import { Checkbox } from 'components/inputs/Checkbox';
import { CurrencyDisplay } from 'components/ui/Formats';
import { NavigationRow } from '../../rows/registration/NavigationRow';
// functions
import { getTotal, moduleSelected, availableModules } from './functions';
import axios from 'axios';

export class ModulesTab extends React.Component {
  
  state = { submitted: false }

  componentDidUpdate( prevprops ) {
    const valid = this.isValid( this.props.base )
    if ( prevprops.valid !== valid && this.state.submitted )
      this.props.onValidChange( valid );
  }

  isValid = base => {
    return moduleSelected( base );
  }
  
  onSubmit = e => {
    e.preventDefault();

    this.setState({
      submitted: true
    });

    if ( this.isValid( this.props.base ) ) {
      this.props.onSubmit( e );
    }
  }

  checkCoupon = e => {
    e.preventDefault()
    const coupon = document.getElementById('coupon').value
    const url = "https://mashpia.com/ajax/checkCoupon.php"
    axios.post(url, {
        coupon: coupon,
        school_id: this.props.base.school_id
    })
      .then(result => {
        console.log(result)
        const data = result.data
        if (data.success) this.props.onCoupon(data.discount)
        else alert(data.error)
      })
      .catch(error => console.log(error))
  }

  render(){
    let { tabId, base, back, onChange, coupon } = this.props;

    let {
      chayolei, chayolei_fee, tanya,  tanya_fee,
      rewards,  rewards_fee,  chidon, chidon_fee,
    } = base;
    // let {
    //   chayolei_fee, tanya_fee,
    //   rewards_fee,  chidon_fee,
    // } = base;
    // check if we have one item checked
    const total = getTotal( base, false, coupon );
    const valid = this.isValid( base );
    // get the modules we can register
    const modules = availableModules( base.registration, true );

    return (
      <TabPane tabId={ tabId } id='ModulesTab'>

        <Callout>
          Please choose which modules you would like to enroll your base in.
        </Callout>

        <div className='modules'>

          <div className={`module ${ !modules.chayolei ? 'paid' : '' }`}>
            <Checkbox name='chayolei'
                onChange={ onChange }
                checked={ !!chayolei }
                disabled={ !modules.chayolei }>
              Basic Tzivos Hashem Programming
            </Checkbox>
            {/* <Checkbox name='chayolei'
                onChange={ onChange }
                checked={ true }
                disabled={ true }>
              Basic Tzivos Hashem Programming
            </Checkbox>*/}

            <div className='details'>
              <p>
                Join us in our core mission of bringing Kabolas Ol to the next generation of Jews.
              </p>
              <ul className='checkboxes'>
                <li>An online dashboard for managing all your Soldiers, Platoons and much more.</li>
                <li>Promotional Materials throughout the year for your base and it’s teachers</li>
                <li>Monthly Rallies and associated resources.</li>
                <li>Seperate logins for teachers and parents to control their soldiers.</li>
                <li>Training for teachers via email and whatsapp support</li>
                <li>Ongoing training and support from Headquarters for base commanders.</li>
              </ul>
            </div>

            <div className='price'>
              <span>
                Total Value: <CurrencyDisplay value={ 2000 } />
              </span>
              <span>
                Your Price: <CurrencyDisplay value={ chayolei_fee } />
              </span>
            </div>
          </div>

          { this.props.base.inst_id !== 10 && 
          <Fragment>
            <div className={`module ${ !modules.chidon ? 'paid' : '' }`}>
              <Checkbox name='chidon'
                  onChange={ onChange }
                  checked={ !!chidon }
                  disabled={ !modules.chidon}>
                Chidon
              </Checkbox>
              {/* <Checkbox name='chidon'
                  onChange={ onChange }
                  checked={ true }
                  disabled={ true }>
                Chidon
              </Checkbox> */}

              <div className='details'>
                <p>
                  Join us in teaching Safer Hamitzvos to every Jewish boy and girl.
                </p>
                <ul className='checkboxes'>
                  <li>Discount on Yahadus Books when ordering during soldier registration.</li>
                  <li>Promotional Materials and Test Resources.</li>
                  <li>Invitations to join the world famous final test in NYC.</li>
                </ul>
              </div>

              <div className='price'>
                <span>
                  Total Value: <CurrencyDisplay value={ 600 } />
                </span>
                <span>
                  Your Price: <CurrencyDisplay value={ chidon_fee } />
                </span>
              </div>
            </div>

            <div className={`module ${ !modules.tanya ? 'paid' : '' }`}>
              <Checkbox name='tanya'
                  onChange={ onChange }
                  checked={ !!tanya }
                  disabled={ !modules.tanya}>
                Tanya Program
              </Checkbox>
              {/* <Checkbox name='tanya'
                  onChange={ onChange }
                  checked={ true }
                  disabled={ true }>
                Tanya Program
              </Checkbox> */}

              <div className='details'>
                <p>
                  Put your base on the world stage learning Tanya for the Rebbe's birthday.
                </p>
                <ul className='checkboxes'>
                  <li>Your School featured on OurBirthdayGift.com</li>
                  <li>Compete with other schools all over the world for the top spot on the public leaderboard</li>
                </ul>
              </div>

              <div className='price'>
                <span>
                  Total Value: <CurrencyDisplay value={ 150 } />
                </span>
                <span>
                  Your Price: <CurrencyDisplay value={ tanya_fee } />
                </span>
              </div>
            </div>
          </Fragment>
          }

          <div className={`module ${ !modules.rewards ? 'paid' : '' }`}>
            <Checkbox name='rewards'
                onChange={ onChange }
                checked={ !!rewards }
                disabled={ !modules.rewards}>
              Rewards Program
            </Checkbox>
            {/* <Checkbox name='rewards'
                onChange={ onChange }
                checked={ true }
                disabled={ true }>
              Rewards Program
            </Checkbox> */}

            <div className='details'>
              <p>
                Bring Tzivos Hashem to life with a prize store integrated directly into your Tzivos Hashem experiance.
              </p>
              <ul className='checkboxes'>
                <li>Prize inventory, price and order managment.</li>
                <li>Achievment cards to give Miles to your soldiers.</li>
                <li>Online store with shopping cart for soldiers to spend their miles from the Tzivos Hashem Parent Portal</li>
              </ul>
            </div>

            <div className='price'>
              <span>
                Total Value: <CurrencyDisplay value={ 500 } />
              </span>
              <span>
                Your Price: <CurrencyDisplay value={ rewards_fee } />
              </span>
            </div>
          </div>
        </div>

        <div style={{float: "left", marginTop: "0.5rem", marginBottom: "0.5rem"}}>
          Coupon Code: &nbsp;
          {
            coupon
                ? <input type='text' name='coupon' id='coupon' value={ coupon } disabled />
                : <span><input type='text' name='coupon' id='coupon' />&nbsp;<button onClick={ this.checkCoupon }>Apply</button></span>
          }
        </div>

        <div className='total'>
          Your Final Price: <CurrencyDisplay value={ total } />
        </div>

        <NavigationRow
          back={ back }
          next={ this.onSubmit }
          nextDisabled={ !valid } />

      </TabPane>
    );
  }
}
